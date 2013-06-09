<?php
namespace Rexmac\Zend\Monitor\Controller\Plugin;

use \DateTime,
    \Zend_Exception,
    \Zend_Log,
    \Zend_Registry;

/**
 * Zend controller plugin that logs slow DB queries.
 *
 * This class was inspired by and contains code from the monitorix project by
 * Markus Hausammann (?) (https://github.com/markushausammann/monitorix) and
 * released under the New BSD License.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class SlowQueries extends \Zend_Controller_Plugin_Abstract {

  /**
   * Called before Zend_Controller_Front exits its dispatch loop.
   *
   * @return void
   */
  public function dispatchLoopShutdown() {
    $slowQueryLimit = Zend_Registry::get('monitor')->getSlowQueryLimit();
    $slowQueryLimitSeconds = $slowQueryLimit / 1000;

    // Log slow queries using Doctrine SQL logger
    try {
      $sqlLogger = Zend_Registry::get('sqlLogger');
      $queries = $sqlLogger->getQueries();
      foreach($queries as $query) {
        if($query['executionMs'] > $slowQueryLimit) {
          $this->_logMessage($query['executionMs'], $query['query'], $query['params']);
        }
      }
    } catch(Zend_Exception $e) {
    }

    // Log slow queries using Zend_Db profilers
    try{
      $profilers = Zend_Registry::get('profilers');
      foreach($profilers as $profiler) {
        if($profiler->getTotalNumQueries()) {
          $queryProfiles = $profiler->getQueryProfiles();
          foreach($queryProfiles as $queryProfile) {
            if($queryProfile->getElapsedSecs() > $slowQueryLimitSeconds) {
              $this->_logMessage($queryProfile->getElapsedSecs() * 1000, $queryProfile->getQuery(), $queryProfile->getQueryParams());
            }
          }
          $profiler->clear();
        }
      }
    } catch(Zend_Exception $e) {
    }
  }

  /**
   * Log slow query message via monitor
   *
   * @param int $executionTime Execution time in ms
   * @param string $query SQL query
   * @param array SQL query prarameters
   * @return void
   */
  private function _logMessage($executionTime, $query, array $params) {
    $parameters = array();
    foreach($params as $param) {
      if($param instanceof DateTime) $parameters[] = $param->format('Y-m-d H:i:s T');
      else $parameters[] = $param;
    }
    $message = 'A slow database query was detected.' . "\n"
      . '===================================' . "\n"
      . 'Execution time: ' . $executionTime . " ms\n"
      . 'Query:          ' . $query . "\n"
      . 'Parameters:     ' . implode(', ', $parameters);

    try {
      Zend_Registry::get('monitor')->writeLog($message, Zend_Log::WARN, 'slow-query');
    } catch(Zend_Exception $e) {
    }
  }
}
