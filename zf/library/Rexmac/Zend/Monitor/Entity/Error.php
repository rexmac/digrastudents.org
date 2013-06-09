<?php
namespace Rexmac\Zend\Monitor\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents an error log entry
 *
 * @author Rex McConnell <rex@rexmac.com>
 * @ORM\Entity
 */
class Error extends \Rexmac\Doctrine\Entity {
  /**
   * Unique identifier
   *
   * @var int
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id = null;

  /**
   * Log type
   *
   * @var string
   * @ORM\Column(length=50)
   */
  protected $logType = 'default';

  /**
   * Priority
   *
   * @var int
   * @ORM\Column(type="integer")
   */
  protected $priority = null;

  /**
   * Error number (see http://www.php.net/manual/en/errorfunc.constants.php)
   *
   * @var int
   * @ORM\Column(type="integer",nullable=true)
   */
  protected $errno = null;

  /**
   * Message
   *
   * @var string
   * @ORM\Column(type="text")
   */
  protected $message = null;

  /**
   * File
   *
   * @var string
   * @ORM\Column(length=255,nullable=true)
   */
  protected $file = null;

  /**
   * Line number
   *
   * @var int
   * @ORM\Column(type="integer",nullable=true)
   */
  protected $line = null;

  /**
   * Context
   *
   * @var string
   * @ORM\Column(type="text",nullable=true)
   */
  protected $context = null;

  /**
   * Stack trace
   *
   * @var string
   * @ORM\Column(type="text",nullable=true)
   */
  protected $stackTrace = null;

  /**
   * Date
   *
   * @var DateTime
   * @ORM\Column(type="datetime")
   */
  protected $date = null;

  /**
   * Priority name
   *
   * @var string
   * @ORM\Column(length=15)
   */
  protected $priorityName = null;
}
