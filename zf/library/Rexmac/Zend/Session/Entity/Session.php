<?php
namespace Rexmac\Zend\Session\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a PHP session
 *
 * @author Rex McConnell <rex@rexmac.com>
 * @ORM\Entity
 */
class Session extends \Rexmac\Doctrine\Entity {
  /**
   * Unique identifier
   *
   * @var string
   * @ORM\Id
   * @ORM\Column(type="string",length=128)
   */
  protected $id = null;

  /**
   * Timestamp of last modification
   *
   * @var int
   * @ORM\Column(type="integer")
   */
  protected $modified = null;

  /**
   * Timestamp of expiration
   *
   * @var int
   * @ORM\Column(type="integer")
   */
  protected $lifetime = null;

  /**
   * Session data
   *
   * @var string
   * @ORM\Column(type="text")
   */
  protected $data = null;
}
