<?php
namespace Rexmac\Zend\Session\Service;

/**
 *  Service layer to ease the use and management of Session entities.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class SessionService extends \Rexmac\Doctrine\Service {

  /**
   * Garbage Collection - remove session data older than $maxlifetime
   *
   * @param int $maxlifetime
   * @return bool TRUE
   */
  public static function collectGarbage($maxlifetime) {
    $dql = 'DELETE ' . self::getEntityClass() . ' s WHERE (s.modified + ' . ($maxlifetime ?: 's.lifetime') . ') < ?1';
    self::getEntityManager()->createQuery($dql)
      ->setParameter(1, time())
      ->execute();
    return true;
  }
}
