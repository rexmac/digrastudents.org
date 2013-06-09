<?php
namespace Rexmac\Zend\Log;

use \Zend_Log,
    \Zend_Log_Writer_Stream,
    \Zend_Registry;

/**
 * Convenience class for easy file logging.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class Logger {

  /**
   * Logger object
   *
   * @var Zend_Log
   */
  private $_log;

  /**
   * Singleton isntance
   *
   * @var Rexmac\Zend\Log\Logger
   */
  private static $_instance = null;

  /**
   * Optional PHP stream to log to
   *
   * @var null|stream
   */
  public static $logStream = null;

  /**
   * Returns static Rexmac\Zend\Log\Logger instance
   *
   * @return Rexmac\Zend\Log\Logger
   */
  public static function getInstance() {
    if(null === self::$_instance) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Resets static Rexmac\Zend\Log\Logger instance.
   *
   * Useful for functional tests.
   *
   * @return void
   */
  public static function resetInstance() {
    self::$_instance = null;
  }

  /**
   * Logs a message at priorty EMERG
   *
   * @todo Look into replacing priority methods with __callstatic
   * @param string $message
   * @return void
   */
  public static function emerg($message) {
    self::getInstance()->getLog()->emerg($message);
  }

  /**
   * Logs a message at priorty ALERT
   *
   * @param  string $message
   * @return void
   */
  public static function alert($message) {
    self::getInstance()->getLog()->alert($message);
  }

  /**
   * Logs a message at priorty CRIT
   *
   * @param  string $message
   * @return void
   */
  public static function crit($message) {
    self::getInstance()->getLog()->crit($message);
  }

  /**
   * Logs a message at priorty ERR
   *
   * @param  string $message
   * @return void
   */
  public static function err($message) {
    self::getInstance()->getLog()->err($message);
  }

  /**
   * Logs a message at priorty WARN
   *
   * @param  string $message
   * @return void
   */
  public static function warn($message) {
    self::getInstance()->getLog()->warn($message);
  }

  /**
   * Logs a message at priorty NOTICE
   *
   * @param  string $message
   * @return void
   */
  public static function notice($message) {
    self::getInstance()->getLog()->notice($message);
  }

  /**
   * Logs a message at priorty INFO
   *
   * @param  string $message
   * @return void
   */
  public static function info($message) {
    self::getInstance()->getLog()->info($message);
  }

  /**
   * Logs a message at priorty DEBUG
   *
   * @param  string $message
   * @return void
   */
  public static function debug($message) {
    self::getInstance()->getLog()->debug($message);
  }

  /**
   * Constructor
   *
   * @return Rexmac\Zend\Log\Logger
   */
  private function __construct() {
    if(null !== self::$logStream) {
      $this->_log = new Zend_Log(new Zend_Log_Writer_Stream(self::$logStream));
    } elseif(Zend_Registry::isRegistered('log')) {
      // @codeCoverageIgnoreStart
      $this->_log = Zend_Registry::get('log');
    } else { // @codeCoverageIgnoreEnd
      $this->_log = new Zend_Log(new Zend_Log_Writer_Stream('/tmp/log'), 'a');
    }

    $this->_log->setEventItem('pid', getmypid());
  }

  /**
   * Return Zend_Log object
   *
   * @return Zend_Log
   */
  public function getLog() {
    return $this->_log;
  }
}
