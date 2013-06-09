<?php

use Rexmac\Digra\Digra;
use Rexmac\Zend\Log\Logger;

/**
 * GamesResearchMap controller
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class GamesResearchMapController extends \Zend_Controller_Action {

  /**
   * Initialization
   *
   * @return void
   */
  public function init() {
    parent::init();
    $this->_helper->getHelper('AjaxContext')
      ->addActionContext('data', 'json')
      ->initContext();
  }

  /**
   * Index action
   *
   * @return void
   */
  public function indexAction() {
    $this->_forward('data');
  }

  /**
   * Data action
   *
   * @return void
   */
  public function dataAction() {
    // Force JSON context and disable auto-serialization
    $this->_helper->getHelper('AjaxContext')->setAutoJsonSerialization(false)->initcontext('json');

    // Disable layout and view
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    $digra = new Digra();
    $data = $digra->fetchResearchPositions();
    echo Zend_Json::encode($data);
  }
}
