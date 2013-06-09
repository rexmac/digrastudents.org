<?php
namespace Rexmac\Zend\Controller\Request;

/**
 * HTTP request object for use with Zend_Controller family.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class HttpRequest extends \Zend_Controller_Request_Http {

  /**
   * Return full URL of this request.
   *
   * @return string
   */
  public function getRequestUrl() {
    $requestUri = $this->getRequestUri();
    $scheme = $this->getScheme();
    $host = $this->getHttpHost();

    $schemeAndHost = $scheme . '://' . $host;

    if(strpos($requestUri, $schemeAndHost) === 0) return $requestUri;

    return $schemeAndHost . $requestUri;
  }
}
