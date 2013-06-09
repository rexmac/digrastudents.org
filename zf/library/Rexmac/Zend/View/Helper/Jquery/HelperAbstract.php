<?php
namespace Rexmac\Zend\View\Helper\Jquery;

use Zend_Controller_Front as FrontController,
    Zend_View;

/**
 * Abstract helper class for Jquery view helpers
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
abstract class HelperAbstract extends \Zend_View_Helper_Abstract implements Helper {

  /**
   * Accumulated jQuery files
   *
   * @var array
   */
  private static $_files = array();

  /**
   * Accumulated jQuery script
   *
   * @var string
   */
  private static $_script = '';

  /**
   * JS view
   *
   * @var Zend_View
   */
  private static $_jsView = null;

  /**
   * Returns JS view script for the current request's controller
   *
   * @return Zend_View
   */
  private static function getView() {
    if(null === self::$_jsView) {
      self::$_jsView = new Zend_View();
      $request = FrontController::getInstance()->getRequest();
      $module = strtolower($request->getModuleName());
      $path = APPLICATION_PATH;
      if($module != 'default') {
        $path .= '/modules/'.$module;
      }
      $path .= '/views/scripts/' . strtolower($request->getControllerName());
      self::$_jsView->setScriptPath($path);
    }
    return self::$_jsView;
  }

  /**
   * Assign view variables
   *
   * @param array $data Associative array of view variable names and their values
   */
  public static function assignData($data) {
    self::getView()->assign($data);
  }

  /**
   * Prepends the given javascript file to the accumulated jQuery javascript
   *
   * @param string $file jQuery javascript file
   */
  public static function prependFile($file) {
    if(!in_array($file, self::$_files)) {
      array_unshift(self::$_files, $file);
    }
  }

  /**
   * Appends the given javascript file to the accumulated jQuery javascript
   *
   * @param string $file jQuery javascript file
   */
  public static function appendFile($file) {
    if(!in_array($file, self::$_files)) {
      self::$_files[] = $file;
    }
  }

  /**
   * Appends the given javascript to the accumulated jQuery javascript
   *
   * @param string $script jQuery javascript
   */
  public static function appendScript($script) {
    self::$_script .= $script . "\n";
  }

  /**
   * Clear script
   *
   * @return void
   */
  public static function clearScript() {
    self::$_script = '';
  }

  /**
   * Returns accumulated jQuery files
   *
   * @return array
   */
  public static function getFiles() {
    return self::$_files;
  }

  /**
   * Returns accumulated jQuery javascript
   *
   * Also appends .js viewscript file for current action if one exists
   *
   * @return string
   */
  public static function getScript() {
    $script = self::$_script;

    $request = FrontController::getInstance()->getRequest();
    if(isset($request)) {
      $filename = strtolower($request->getActionName()) . '.js';
      $paths = self::getView()->getScriptPaths();
      foreach($paths as $dir) {
        if(is_readable($dir . $filename)) {
          $script .= "\n" . self::$_jsView->render($filename);
          break;
        }
      }
    }

    return $script . "\n";
  }

  /**
   * Magic overload: Proxy to {@link render()}.
   *
   * This method will trigger an E_USER_ERROR if rendering the helper causes
   * an exception to be thrown.
   *
   * Implements {@link Rexmac\Zend\View\Helper\Jquery\Helper::__toString()}.
   *
   * @return string
   */
  public function __toString() {
    try {
      return $this->render();
    } catch(Exception $e) {
      $msg = get_class($e) . ': ' . $e->getMessage();
      trigger_error($msg, E_USER_ERROR);
      return '';
    }
  }
}
