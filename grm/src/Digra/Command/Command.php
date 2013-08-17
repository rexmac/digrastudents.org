<?php

namespace Digra\Command;

use Digra\Console\Application;
use Digra\IO\IOInterface;
use Digra\IO\NullIO;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Base class for Digra commands
 *
 */
abstract class Command extends BaseCommand {
  /**
   * @ var Digra
   */
  private $digra;

  /**
   * @var IOInterface
   */
  private $io;

  /**
   * @return Digra
   */
  public function getDigra($required = true) {
    if(null === $this->digra) {
      $application = $this->getApplication();
      if($application instanceof Application) {
        $this->digra = $application->getDigra($required);
      } elseif($required) {
        throw new \RuntimeException(
          'Could not create a Digra\Digra instance, you must inject '.
          'one if this command is not used with a Digra\Console\Application instance'
        );
      }
    }

    return $this->digra;
  }

  /**
   * @return IOInterface
   */
  public function getIO() {
    if(null === $this->io) {
      $application = $this->getApplication();
      #if($application instanceof Application) {
        $this->io = $application->getIO();
      #} else {
      #  $this->io = new NullIO();
      #}
    }

    return $this->io;
  }

  /**
   * @param IOInterface $io
   */
  public function setIO(IOInterface $io) {
    $this->io = $io;
  }
}
