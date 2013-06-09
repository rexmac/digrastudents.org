<?php
namespace Rexmac\Zend\View\Helper\Jquery;

/**
 * Jquery helper interface
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
interface Helper {

  /**
   * Magic overload: Should proxy to {@link render()}.
   *
   * @return string
   */
  public function __toString();

  /**
   * Renders helper
   *
   * @return string Helper output
   * @throws Zend_View_Exception if unable to render
   */
  public function render();
}
