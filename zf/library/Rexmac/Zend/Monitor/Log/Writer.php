<?php
namespace Rexmac\Zend\Monitor\Log;

use \DateTime,
    Rexmac\Zend\Monitor\Service\ErrorService;

/**
 * Custom DB log writer for Monitor

 * @author Rex McConnell <rex@rexmac.com>
 * @codeCoverageIgnore
 */
class Writer extends \Zend_Log_Writer_Abstract {
  /**
   * Class constructor
   *
   * @return void
   */
  public function __construct() {
  }

  /**
   * Create a new instance of Writer
   *
   * @param array|Zend_Config $config
   * @return Writer
   */
  static public function factory($config) {
    return new self();
  }

  /**
   * Write a message to the log
   *
   * @param array $event event data
   * @return void
   */
  protected function _write($event) {
    $dataToInsert = $event;

    // Replace timestamp string
    $dataToInsert['date'] = new DateTime($dataToInsert['timestamp']);
    unset($dataToInsert['timestamp']);

    // Insert entry into DB
    ErrorService::create($dataToInsert);
  }
}
