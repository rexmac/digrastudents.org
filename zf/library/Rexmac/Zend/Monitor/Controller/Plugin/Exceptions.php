<?php
namespace Rexmac\Zend\Monitor\Controller\Plugin;

use \Zend_Registry;

/**
 * Zend controller plugin that intercepts exception responses for logging
 * purposes.
 *
 * This class was inspired by and contains code from the monitorix project by
 * Markus Hausammann (?) (https://github.com/markushausammann/monitorix) and
 * released under the New BSD License.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class Exceptions extends \Zend_Controller_Plugin_Abstract {

  /**
   * Called before Zend_Controller_Front exits its dispatch loop.
   *
   * @return void
   */
  public function dispatchLoopShutdown() {
    $response = $this->getResponse();
    if($response->isException()) {
      Zend_Registry::get('monitor')->writeLog($response);
    }
  }
}
