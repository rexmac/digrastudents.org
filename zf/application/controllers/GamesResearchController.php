<?php

use Rexmac\Digra\Digra;
use Rexmac\Zend\Log\Logger;

/**
 * GamesResearch controller
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class GamesResearchController extends \Zend_Controller_Action {

  /**
   * Initialization
   *
   * @return void
   */
  public function init() {
    parent::init();
    $this->_helper->getHelper('AjaxContext')
      ->addActionContext('journals', 'json')
      ->addActionContext('positions', 'json')
      ->initContext();
  }

  /**
   * Index action
   *
   * @return void
   */
  public function indexAction() {
    $this->_forward('positions');
  }

  /**
   * Journals action
   *
   * @return void
   */
  public function journalsAction() {
    // Force JSON context and disable auto-serialization
    $this->_helper->getHelper('AjaxContext')->setAutoJsonSerialization(false)->initcontext('json');

    // Disable layout and view
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    $digra = new Digra();
    $data = $digra->fetchJournals();
    echo Zend_Json::encode($data);
  }

  /**
   * Positions action
   *
   * @return void
   */
  public function positionsAction() {
    // Force JSON context and disable auto-serialization
    $this->_helper->getHelper('AjaxContext')->setAutoJsonSerialization(false)->initcontext('json');

    // Disable layout and view
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    $digra = new Digra();
    $data = $digra->fetchPositions();
    echo Zend_Json::encode($data);

    #$cacheDir = (defined('APPLICATION_PATH') ? APPLICATION_PATH . '/../' : '') . './cache';
    #$data = file_get_contents($cacheDir . '/grm.json');
    #echo $data;
  }
}
