<?php

/**
 * This file is part of Digra.
 *
 * (c) Rex McConnell (http://rexmac.com/) <rex@rexmac.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function includeIfExists($file) {
  if(file_exists($file)) {
    return include $file;
  }
}

#if((!$loader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__ . '/../../../autoload.php'))) {
if(!($loader = includeIfExists(__DIR__ . '/../vendor/autoload.php'))) {
  echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL .
       'bin/composer install' . PHP_EOL;
  exit(1);
}

return $loader;
