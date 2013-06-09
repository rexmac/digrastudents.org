<?php
/**
 * Application classmap for autoloader
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
$dirname = dirname(__FILE__);
return array (
  'Bootstrap' => $dirname . DIRECTORY_SEPARATOR . 'Bootstrap.php',
  'ErrorController' => $dirname . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'ErrorController.php',
  'GamesResearchMapController' => $dirname . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'GamesResearchMapController.php'
);
