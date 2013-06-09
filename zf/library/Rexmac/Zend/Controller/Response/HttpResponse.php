<?php
namespace Rexmac\Zend\Controller\Response;

/**
 * HTTP response object for use with Zend_Controller family.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class HttpResponse extends \Zend_Controller_Response_Http {
  /**
   * Get response header
   *
   * @param string $name
   * @param mixed $default
   *
   * @return mixed
   */
  public function getHeader($name, $default = null) {
    foreach($this->_headers as $header) {
      if($header['name'] === $name) {
        return $header;
      }
    }
    return $default;
  }
}
