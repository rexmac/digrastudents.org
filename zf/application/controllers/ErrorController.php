<?php
/**
 * Niche (http://rexmac.com/)
 *
 * @category   Niche
 * @package    Rexmac_Niche
 * @subpackage Application_Controller
 * @copyright  Copyright (c) 2013 Rex Mcconnell (http://rexmac.com/)
 * @license    http://rexmac.com/license/bsd3c.txt BSD 3-Clause License
 */
use Rexmac\Zend\Log\Logger,
    Zend_Controller_Plugin_ErrorHandler as ErrorHandler,
    Zend_Registry;

/**
 * Error controller
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class ErrorController extends \Zend_Controller_Action {

  /**
   * Initialization
   *
   * @return void
   */
  public function init() {
    $this->_helper->contextSwitch()
      ->addActionContext('error', array('json', 'xml'))
      ->initContext();
  }

  /**
   * Pre-dispatch
   *
   * @return void
   */
  public function preDispatch() {
    if('xml' === $this->getRequest()->getParam('format')) {
      $this->view->xmlWrapperTag = mb_strtolower(sanitize_string_for_xml_tag(Zend_Registry::get('siteName'))) . 'Response';
    }
  }

  /**
   * Post-dispatch
   *
   * @return void
   */
  public function postDispatch() {
    unset($this->view->now);
    if('xml' === $this->getRequest()->getParam('format')) {
      $this->render();
      $response = $this->getResponse();
      $response->setBody(rtrim(preg_replace('/>\s+</', '><', $response->getBody())));
    }
  }

  /**
   * Index action
   *
   * @return void
   */
  public function indexAction() {
    $this->_forward('error');
  }

  /**
   * Error action
   *
   * @return void
   */
  public function errorAction() {
    $errors = $this->_getParam('error_handler');
    switch ($errors->type) {
      case ErrorHandler::EXCEPTION_NO_ROUTE:
      case ErrorHandler::EXCEPTION_NO_CONTROLLER:
      case ErrorHandler::EXCEPTION_NO_ACTION:
        // 404 error -- controller or action not found
        $this->getResponse()->setHttpResponseCode(404);
        $this->view->message = 404;
        break;
      default:
        // Application error
        $this->getResponse()->setHttpResponseCode(500);
        if($errors->exception) {
          $this->view->message = 'Application error: '.$errors->exception->getMessage();
        } else {
          $this->view->message = 'Application error: Unknown error';
        }
    }

    // Log exception, if logger available
    Logger::crit(__METHOD__.':: '.$this->view->message.' - '.$errors->exception);

    // Conditionally display exceptions
    if($this->getInvokeArg('displayExceptions') == true) {
      $this->view->exception = $errors->exception;
    }

    if(null === $this->getRequest()->getParam('format')) {
      $this->view->request = $errors->request;
    }

    if($this->view->message === 404) {
      $this->view->pageTitle(' - Page not found');
    } else {
      $this->view->pageTitle(' - An error occurred');
    }

    $this->getRequest()->setParams(array(
      'controller' => 'error',
      'action'     => 'error'
    ));
  }

  /**
   * Forbidden action
   *
   * @return void
   */
  public function forbiddenAction() {
  }
}
