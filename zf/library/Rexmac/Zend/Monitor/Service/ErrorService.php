<?php
namespace Rexmac\Zend\Monitor\Service;

use \DateTime,
    \DateTimeZone;

/**
 * Service layer to ease the use and management of Error entities
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class ErrorService extends \Rexmac\Doctrine\Service {

  /**
   * Get error report
   *
   * @param DateTime|string $startDate Beginning of date range
   * @param DateTime|string $stopDate End of date range
   * @param DateTimeZone $timeZone Desired timezone for results
   * @param string Type of errors to include in report
   * @return array Report data
   * @todo Use class constants (in Error entity perhaps) for $logType
   */
  public static function getReport($startDate, $stopDate, DateTimeZone $timeZone, $logType = null) {
    $gmt = new DateTimeZone('GMT');
    if($startDate instanceof DateTime) {
      $startDate = clone $startDate;
      $startDate->setTimeZone($gmt);
      $startDate = $startDate->format('Y-m-d H:i:s');
    }
    if($stopDate instanceof DateTime) {
      $stopDate = clone $stopDate;
      $stopDate->setTimeZone($gmt);
      $stopDate = $stopDate->format('Y-m-d H:i:s');
    }

    // Create query
    $dql = sprintf('SELECT e FROM %s e WHERE e.date >= :startDate AND e.date <= :stopDate',
      self::getEntityClass()
    );
    if(null !== $logType && 0 != $logType) {
      $dql .= ' AND e.logType = :logType';
    }
    $query = self::getEntityManager()->createQuery($dql);

    // Set query parameters
    $query->setParameters(array(
      'startDate' => $startDate,
      'stopDate'  => $stopDate,
    ));
    if(null !== $logType && 0 != $logType) {
      $query->setParameter('logType', $logType);
    }

    // Execute query
    $errors = $query->getArrayResult();

    // @todo Consider a custom hydration class instead; see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html#custom-hydration-modes
    $results = array();
    foreach($errors as $error) {
      $results[$error['id']] = $error;
    }

    // Return results
    return $results;
  }
}
