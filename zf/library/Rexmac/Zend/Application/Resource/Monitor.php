<?php
namespace Rexmac\Zend\Application\Resource;

use Rexmac\Zend\Monitor\Monitor as MonitorObject,
    \Zend_Db,
    \Zend_Log_Writer_Db,
    \Zend_Registry;

/**
 * Zend application resource for configuring monitor which logs errors to DB.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class Monitor extends \Zend_Application_Resource_ResourceAbstract {

  /**
   * Associative array of config options
   *
   * @var array
   */
  protected $_options = array(
    /*'db' => array(
      'adapter' => 'pdo_sqlite',
      'params'  => array(
        'host'     => 'localhost',
        'dbname'   => 'db',
        'username' => null,
        'password' => null
      )
    ),*/
    'logExceptions'       => false,
    'logFatalErrors'      => false,
    'logJavaScriptErrors' => false,
    'logSlowQueriess'     => false,
    'slowQueryLimit'      => null
  );

  /**
   * Monitor object
   *
   * @var MonitorObject
   */
  protected $_monitor;

  /**
   * Initializes resource
   *
   * @return Monitor
   */
  public function init() {
    if(!$this->_monitor) {
      $options = $this->getOptions();

      $this->_monitor = new MonitorObject();
      if($options['logExceptions']) $this->_monitor->logExceptions();
      if($options['logFatalErrors']) $this->_monitor->logFatalErrors();
      if($options['logJavaScriptErrors']) $this->_monitor->logJavaScriptErrors();
      if($options['logSlowQueries']) $this->_monitor->logSlowQueries(array(), isset($options['slowQueryLimit']) ? $options['slowQueryLimit'] : null);
      Zend_Registry::set('monitor', $this->_monitor);
    }

    return $this->_monitor;
  }

  /**
   * Retrieve monitor instance
   *
   * @return MonitorObject
   */
  public function getMonitor() {
    return $this->_monitor;
  }
}
