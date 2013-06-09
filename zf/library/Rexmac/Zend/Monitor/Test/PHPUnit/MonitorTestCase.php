<?php
namespace Rexmac\Zend\Monitor\Test\PHPUnit;

use Doctrine\ORM\Tools\SchemaTool,
    \Zend_Monitor;

/**
 * Functional testing scaffold for Monitor tests
 *
 * @author Rex McConnell <rex@rexmac.com>
 * @codeCoverageIgnore
 */
class MonitorTestCase extends \Rexmac\Zend\Test\PHPUnit\DoctrineTestCase {

  /**
   * Set up before class
   *
   * @return void
   */
  public static function setUpBeforeClass() {
    #Zend_Session::$_unitTestEnabled = true;
    parent::setUpBeforeClass();
    $tool = new SchemaTool(self::$entityManager);
    $metadata = array(self::$entityManager->getClassMetadata('Rexmac\Zend\Monitor\Entity\Error'));
    self::$metadata = array_merge(self::$metadata, $metadata);
    $tool->createSchema($metadata);
  }
}

