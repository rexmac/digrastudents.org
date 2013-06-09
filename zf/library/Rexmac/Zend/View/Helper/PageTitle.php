<?php

namespace Rexmac\Zend\View\Helper;

/**
 * Helper class for displaying page titles
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class PageTitle extends \Zend_View_Helper_Placeholder_Container_Standalone {

  /**
   * Suffix to be appended to output
   *
   * @var string
   */
  private $_suffix = '';

  /**
   * Constructor
   *
   * @return void
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Helper method to display page title
   *
   * @param string $suffix Suffix to be appended to output
   * @return string
   */
  public function pageTitle($suffix = '') {
    $this->_suffix .= $suffix;
    return $this;
  }

  /**
   * Return page title
   *
   * @return string
   */
  public function toString() {
    if($this->view) {
      if($page = $this->view->navigation()->findActive($this->view->navigation()->getContainer())) {
        return $this->view->escape($page['page']->getLabel()) . $this->_suffix;
      }
      return ''.$this->_suffix;
    }
  }
}
