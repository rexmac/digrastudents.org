<?php
namespace Rexmac\Zend\Monitor;

use \Exception,
    Rexmac\Zend\Monitor\Exception as MonitorException,
    Rexmac\Zend\Monitor\Log\Writer as DoctrineLogWriter,
    Rexmac\Zend\View\Helper\Jquery as JqueryViewHelper,
    \Zend_Controller_Action_HelperBroker as HelperBroker,
    \Zend_Controller_Front as FrontController,
    \Zend_Controller_Response_Http as HttpResponse,
    \Zend_Db_Adapter_Abstract as AbstractDbAdapter,
    \Zend_Exception,
    \Zend_Log_Writer_Abstract as AbstractLogWriter,
    \Zend_Registry,
    \Zend_Session;

/**
 * Class for logging of errors from various sources.
 *
 * This class was inspired by and contains code from the monitorix project by
 * Markus Hausammann (?) (https://github.com/markushausammann/monitorix) and
 * released under the New BSD License.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class Monitor extends \Zend_Log {
  /**
   * Has the shutdown function been registered?
   *
   * @var bool
   */
  private static $_shutdownRegistered = false;

  /**
   * Should exceptions be logged?
   *
   * @var bool
   */
  private $_logExceptions = false;

  /**
   * Should fatal errors be logged?
   *
   * @var bool
   */
  private $_logFatalErrors = false;

  /**
   * Should Javascript errors be logged?
   *
   * @var bool
   */
  private $_logJavascriptErrors = false;

  /**
   * Should slow SQL queries be logged?
   *
   * @var bool
   */
  private $_logSlowQueries = false;

  /**
   * Number of milliseconds after which a SQL query is considered a slow query.
   *
   * @var int
   */
  private $_slowQueryLimit = 1000;

  /**
   * Constructor
   *
   * @param  AbstractLogWriter $writer Log writer
   * @return void
   */
  public function __construct(AbstractLogWriter $writer = null) {
    if(null === $writer) {
      parent::__construct(new DoctrineLogWriter());
    } else {
      parent::__construct($writer);
    }
  }

  /**
   * Are exceptions being logged?
   *
   * @return bool
   */
  public function isLoggingExceptions() {
    return $this->_logExceptions;
  }

  /**
   * Are fatal errors being logged?
   *
   * @return bool
   */
  public function isLoggingFatalErrors() {
    return $this->_logFatalErrors && self::$_shutdownRegistered;
  }

  /**
   * Are Javascript errors being logged?
   *
   * @return bool
   */
  public function isLoggingJavascriptErrors() {
    return $this->_logJavascriptErrors;
  }

  /**
   * Are slow queries being logged?
   *
   * @return bool
   */
  public function isLoggingSlowQueries() {
    return $this->_logSlowQueries;
  }

  /**
   * Return slow SQL query liit
   *
   * @return int
   */
  public function getSlowQueryLimit() {
    return $this->_slowQueryLimit;
  }

  /**
   * Toggle logging of exceptions
   *
   * @param bool $toggle Should exceptions be logged? Default is TRUE.
   * @return Monitor
   */
  public function logExceptions($toggle = true) {
    $this->_logExceptions = $toggle;
    $this->_registerControllerPlugin('Rexmac\Zend\Monitor\Controller\Plugin\Exceptions', $toggle);
  }

  /**
   * Toggle logging of fatal errors
   *
   * @param bool $toggle Should fatal errors be logged? Default is TRUE.
   * @return Monitor
   * @todo unregister_shutdown_function when $toggler is false;
   * http://stackoverflow.com/questions/2726524/can-you-unregister-a-shutdown-function
   * http://www.serverphorums.com/read.php?7,337759 [PHP-DEV] [PATCH] unregister_shutdown_function()
   */
  public function logFatalErrors($toggle = true) {
    $this->_logFatalErrors = $toggle;
    if(!self::$_shutdownRegistered) {
      if(!Zend_Session::$_unitTestEnabled) register_shutdown_function(array($this, 'shutdownFunction'));
      self::$_shutdownRegistered = true;
    }
    return $this;
  }

  /**
   * Toggle logging of Javascript errors
   *
   * @todo Toggle implies ability to undo, but there is currently no way of unappending script to Jquery view helper
   * @param bool $toggle Should Javascript errors be logged? Default is TRUE.
   * @return Monitor
   */
  public function logJavascriptErrors($toggle = true) {
    $this->_logJavascriptErrors = $toggle;

    if($toggle) {
      $viewRenderer = HelperBroker::getStaticHelper('viewRenderer');
      if(null === $viewRenderer->view) {
        try { $viewRenderer->init(); }
        catch(Zend_Exception $e) {
          throw new MonitorException('Could not init() viewRenderer.');
        }
      }

      $view = $viewRenderer->view;
      if(false === $view->getPluginLoader('helper')->getPaths('Rexmac\Zend\View\Helper')) {
        $view->addHelperPath('Rexmac/Zend/View/Helper', 'Rexmac\Zend\View\Helper');
      }

      JqueryViewHelper::appendScript(
        'window.onerror=function(message,errorUrl,errorLine){'
        . '$.ajax({type:\'post\',url:\'?monitor=x\',dataType:\'html\','
        . 'data:{\'message\':message,\'errorUrl\':errorUrl,\'errorLine\':errorLine}})};'
      );
    }
    $this->_registerControllerPlugin('\Rexmac\Zend\Monitor\Controller\Plugin\JavascriptErrors', $toggle);

    return $this;
  }

  /**
   * Toggle logging of slow DB queries.
   *
   * @param array $adapters Array of Zend_Db adapters
   * @param int $limit (ms) Anything slower is considered a slow query.
   * @param bool $toggle Whether or not to log slow DB queries. Default is true.
   * @return Monitor
   */
  public function logSlowQueries(array $adapters, $limit = null, $toggle = true) {
    $this->_logSlowQueries = $toggle;
    if(null !== $limit) $this->_slowQueryLimit = (int) $limit;
    $profilers = array();
    foreach($adapters as $adapter) {
      $profiler = $adapter->getProfiler()->setEnabled($toggle);
      if($toggle) {
        $profilers[] = $profiler;
      }
    }

    if(count($profilers) > 0) {
      Zend_Registry::set('monitorProfilers', $profilers);
    }

    $this->_registerControllerPlugin('\Rexmac\Zend\Monitor\Controller\Plugin\SlowQueries', $toggle);

    return $this;
  }

  /**
   * Write log entry to DB
   *
   * @param string|Zend_Controller_Response_Http $input
   * @param int $priority
   * @param string $logType
   */
  public function writeLog($input, $priority = self::DEBUG, $logType = null) {
    if($input instanceof HttpResponse) {
      $exceptions = $input->getException();
      foreach($exceptions as $exception) {
        $message = $exception->getMessage();
        $extraFields = $this->_getExtraFieldsArray($exception);
        parent::log($message, self::CRIT, $extraFields);
      }
    } else {
      if($input === null) throw new Exception('unknown');
      parent::log($input, $priority, $this->_getExtraFieldsArray($logType));
    }
  }

  /**
   * Callback function to log fatal errors.
   *
   * Not intended to be caled directly. For use with
   * register_shutdown_function(), which is done by the logFatalErrors method.
   * Would be private if possible.
   *
   * @return void
   */
  public function shutdownFunction() {
    if(!$this->_logFatalErrors) return;
    $error = error_get_last();
    #$this->handleError($error['type'], $error['message'], $error['file'], $error['line'], 'Last error before shutdown. Fatal or syntax.');
    #if(!(error_reporting() & $error['type'])) return;
    if(null === $error) return;
    parent::log(
      $error['message'],
      self::CRIT,
      array(
        'logType'     => 'php-error',
        #'applicationName' => $this->_applicationName,
        #'environment' => APPLICATION_ENV,
        'errno'       => $error['type'],
        'file'        => $error['file'],
        'line'        => $error['line'],
        'context'     => json_encode('Last error before shutdown. Fatal or syntax'),
        'stackTrace'  => json_encode(debug_backtrace(false))
      )
    );
  }

  /**
   * Maps given information to array of extra fields.
   *
   * @param string|Zend_Exception $input
   * @return array
   */
  private function _getExtraFieldsArray($input = null) {
    if(null === $input) return array();

    $extraFields = array(
      'logType'         => is_string($input) ? $input : 'log',
      #'applicationName' => $this->_applicationName,
      #'environment'     => APPLICATION_ENV
    );

    if($input instanceof Zend_Exception) {
      $extraFields['logType'] = 'exception';
      $extraFields['errno'] = $input->getCode();
      $extraFields['file']  = $input->getFile();
      $extraFields['line']  = $input->getLine();
      #$extraFields['context']  = 'unknown';
      $extraFields['stackTrace'] = json_encode($input->getTrace());
    }

    return $extraFields;
  }

  /**
   * Retrieve the lowest free index of the front controller plugin stack that
   * is above the minimum given index.
   *
   * @param int $minIndex Lowest stack index to use
   */
  private function _getLowestFreeStackIndex($minIndex) {
    $plugins = array_keys(FrontController::getInstance()->getPlugins());
    sort($plugins);
    $highestIndex = array_pop($plugins);
    if($highestIndex < $minIndex) return $minIndex;
    return $highestIndex + 1;
  }

  /**
   * Register front controller plugin
   *
   * @param string $name Name of plugin
   * @param bool $toggle
   * @return void
   */
  private function _registerControllerPlugin($name, $toggle) {
    $fc = FrontController::getInstance();
    if($toggle) {
      $fc->registerPlugin(new $name, $this->_getLowestFreeStackIndex(101));
    } else {
      $fc->unregisterPlugin($name);
    }
  }
}
