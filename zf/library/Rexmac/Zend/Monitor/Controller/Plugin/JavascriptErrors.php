<?php
namespace Rexmac\Zend\Monitor\Controller\Plugin;

use \Zend_Controller_Request_Abstract as AbstractRequest,
    \Zend_Controller_Request_Http as HttpRequest,
    \Zend_Log,
    \Zend_Registry;

/**
 * Zend controller plugin that intercepts XmlHttpRequests for logging
 * purposes.
 *
 * This class was inspired by and contains code from the monitorix project by
 * Markus Hausammann (?) (https://github.com/markushausammann/monitorix) and
 * released under the New BSD License.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class JavascriptErrors extends \Zend_Controller_Plugin_Abstract {

  /**
   * Called before Zend_Controller_Front begins evaluating the
   * request against its routes.
   *
   * @SuppressWarnings(PHPMD.ExitExpression)
   * @param AbstractRequest $request
   * @return void
   */
  public function routeStartup(AbstractRequest $request) {
    if(!($request instanceof HttpRequest)) return;

    if($request->getQuery('monitor') === 'x' && $request->isXmlHttpRequest()) {
      $message = "A javascript error was detected.\n"
        . "================================\n"
        . 'Message: ' . $request->getPost('message', '') . "\n"
        . 'URI: ' . $request->getPost('errorUrl', 'unknown') . "\n"
        . 'Line: ' . $request->getPost('errorLine', 'unknown') . "\n";
      Zend_Registry::get('monitor')->writeLog($message, Zend_Log::WARN, 'javascript-error');

      // Immediately return empty response
      $this->getResponse()->setBody('')->sendResponse();
      exit();
    }
  }
}
