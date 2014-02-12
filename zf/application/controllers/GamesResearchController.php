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
      ->addActionContext('articles', 'json')
      ->addActionContext('journals', 'json')
      ->addActionContext('positions', 'json')
      ->addActionContext('twitter', 'json')
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
   * Articles action
   *
   * @return void
   */
  public function articlesAction() {
    // Force JSON context and disable auto-serialization
    $this->_helper->getHelper('AjaxContext')->setAutoJsonSerialization(false)->initcontext('json');

    // Disable layout and view
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    $digra = new Digra();
    $data = $digra->fetchContent('articles');
    echo Zend_Json::encode($data);
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
    $data = $digra->fetchContent('journals');
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
    $data = $digra->fetchContent('positions');
    echo Zend_Json::encode($data);

    #$cacheDir = (defined('APPLICATION_PATH') ? APPLICATION_PATH . '/../' : '') . './cache';
    #$data = file_get_contents($cacheDir . '/grm.json');
    #echo $data;
  }

  /**
   * Twitter action
   *
   * @return void
   */
  public function twitterAction() {
    // Force JSON context and disable auto-serialization
    $this->_helper->getHelper('AjaxContext')->setAutoJsonSerialization(false)->initcontext('json');

    // Disable layout and view
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    $digra = new Digra();
    $data = $digra->fetchContent('academicsOnTwitter');
    echo Zend_Json::encode($data);
  }

}
