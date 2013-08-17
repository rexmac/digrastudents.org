<?php

namespace Digra;

use Digra\IO\IOInterface;
use Digra\Json\Jsonfile;

/**
 * Creates a configured instance of Digra
 *
 */
class Factory {

  /**
   * Create a Digra instance
   *
   * @param IOInterface       $io          IO instance
   * @param array_string_null $localConfig Either a configuration array or a filename to read from, if null it will
   *                                       read from the default filename
   * @throws \InvalidArgumentException
   * @return Digra
   */
  public function createDigra(IOInterface $io, $localConfig = null) {
/*
    // Load Digra configuration
    if(null === $localConfig) {
      $localConfig = static::getConfigurationFile();
    }

    if(is_string($localConfig)) {
      $configFile = $localConfig;
      $file = new JsonFile($localConfig, new RemoteFilesystem($io));

      if(!$file->exists()) {
        if($localConfig === 'digra.json') {
          $message = 'Digra could not find a digra.json file in ' . getcwd();
        } else {
          $message = 'Composer could not find the config file: ' . $localConfig;
        }
        $instructions = 'Please create a digra.json file.';
        throw new \InvalidArgumentException($message . PHP_EOL . $instructions);
      }

      $file->validateSchema(JsonFile::LAX_SCHEMA);
      $localConfig = $file->read();
    }

    // Configuration defaults
    $config = static::createConfig();
    $config->merge($localConfig);
 */

    // Initialize Digra
    $digra = new Digra();
    #$digra->setConfig($config);

    return $digra;
  }

  public static function createConfig() {
  }

  public static function getConfigurationFile() {
    return trim(getenv('DIGRA')) ?: 'digra.json';
  }

  /**
   * Create a Digra instance
   *
   * @param IOInterface       $io     IO instance
   * @param array_string_null $config Either a configuration array or a filename to read from, if null it will
   *                                  read from the default filename
   * @return Digra
   */
  public static function create(IOInterface $io, $config = null) {
    $factory = new static();

    return $factory->createDigra($io, $config);
  }
}
