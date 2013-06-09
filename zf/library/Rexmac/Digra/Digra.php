<?php

namespace Rexmac\Digra;

/**
 * @author Rex McConnell <rex@rexmac.com>
 */
class Digra {

  #const URL = 'http://www.digra.org:8080/Plone/shared/game-research-map/';
  const URL = 'http://www.digarec.org/gamesresearchmap/doku.php?id=start:gamesresearchmap';
  const VERSION = '1.0.0-DEV';

  public function fetchResearchPositions() {
    #$response = file_get_contents(self::URL);
    $cacheDir = APPLICATION_PATH . '/../cache';
    $filename = $cacheDir . '/positions.html';
    $response = file_get_contents($filename);

    $researchPositions = $response;

    return $this->parseResearchPositionsHtml($researchPositions);

    return $researchPositions;
  }

  public function parseResearchPositionsHtml($html = false) {
    if(!$html) {
      throw new \InvalidArgumentException('$html argument must contain a value');
    }

    // I fucking hate DOMDocument
    $temp = libxml_use_internal_errors(true);
    $dom = new \DOMDocument;
    $dom->loadHTML((string)$html);
    libxml_clear_errors();
    libxml_use_internal_errors($temp);

    /*
     * Parent node
     *      /../
     *
     * Class is exactly 'foo' and nothing else
     *     /div[@class='foo']/
     *
     * Class contains 'foo'
     *     /div[contains(concat(' ',normalize-space(@class),' '),' foo ')]/
     */

    $xpath = new \DOMXPath($dom);
    #$nodes = $xpath->query('/html/body/div[1]/div[2]/div/table');
    $nodes = $xpath->query("///div[contains(concat(' ',normalize-space(@class),' '),' page ')]/div/table");

    if($nodes->length !== 1) {
      throw new \RuntimeException('Failed to parse HTML. Raw HTML response follows:' . PHP_EOL . PHP_EOL . $html);
    }

    $table = $nodes->item(0);

    // Did I mention that I hate DOMDocument et al.?
    $trs = $table->getElementsByTagName('tr');

    $headers = array();
    $data = array();
    $i = 0;
    foreach($trs as $tr) {
      #echo $tr->nodeValue . PHP_EOL;
      if('row0' === $tr->getAttribute('class')) {
        $ths = $tr->getElementsByTagName('th');
        foreach($ths as $th) {
          $headers[] = trim($th->textContent);
        }
      } else {
        $tds = $tr->getElementsByTagName('td');
        $data[$i] = array();
        foreach($tds as $td) {
          $data[$i][] = trim($td->textContent);
        }
        $i++;
      }
    }

    #echo var_export($headers, true) . PHP_EOL . PHP_EOL;
    #echo var_export($data, true) . PHP_EOL;
    #echo "Found " . count($data) . " entries." . PHP_EOL;

    // Write raw CSV
    $cacheDir = APPLICATION_PATH . '/../cache';
    $filename = $cacheDir . '/positions.raw.csv';
    if(!($fh = fopen($filename, 'w'))) {
      throw new \RuntimeException('Failed to open output file for writing raw CSV data');
    }
    fputcsv($fh, $headers);
    foreach($data as $fields) {
      fputcsv($fh, $fields);
    }
    fclose($fh);

    // Load CSV file as string
    $csv = file_get_contents($filename);
    // Split string into lines
    $rows = explode("\n", trim($csv));
    // Parse first line (column headers) for use as keys
    $colHeaders = str_getcsv(array_shift($rows));
    $headers = array(
      'continent'  => 'Continent',
      'country'    => 'Country',
      'university' => 'University/Research Center/Network',
      'department' => 'Department/Faculty/School',
      'group'      => 'Research Group/Lab',
      'program'    => 'Degree Program(s)',
      'contact'    => 'Contact Person',
      'link'       => 'Link',
      'focus'      => 'Focus/Specialization'
    );
    $keys = array_keys($headers);

    $data = array_map(function ($row) use ($keys) {
      return array_combine($keys, str_getcsv($row));
    }, $rows);
    #$json = json_encode(array('headers' => $headers, 'data' => $data));

    // Filter data to be more consistent
    for($i = 0; $i < count($data); ++$i) {
      $data[$i]['university'] = preg_replace('/^-(?:University-)?$/i', '', $data[$i]['university']);
      $data[$i]['department'] = preg_replace('/^-(?:(Faculty\/)?Department-)?$/i', '', $data[$i]['department']);
      $data[$i]['group']      = preg_replace('/^-(?:Lab-)?$/i'       , '', $data[$i]['group']);
      $data[$i]['contact']    = preg_replace('/^-(?:Contact-)?$/i',    '', $data[$i]['contact']);
      $data[$i]['focus']      = preg_replace('/^-(?:Focus-)?$/i',      '', $data[$i]['focus']);
      $data[$i]['link']       = preg_replace('/^-(?:www-)?$/i',        '', $data[$i]['link']);
      $data[$i]['program']    = preg_replace('/^-(?:PhD-)?$/i',     'PhD', $data[$i]['program']);
      $data[$i]['program']    = preg_replace('/Ph\.?D\.?/i',        'PhD', $data[$i]['program']);
      $data[$i]['program']    = preg_replace('/(B|M)\.?(A|S)\.?/', '$1$2', $data[$i]['program']);
      foreach($data[$i] as $key => $value) {
        $data[$i][$key] = preg_replace('/^- *| *-$/', '', $value);
      }

      // Relocate details fields
      $data[$i]['details'] = array(
        'group'   => $data[$i]['group'],
        'contact' => $data[$i]['contact'],
        'link'    => $data[$i]['link'],
        'focus'   => $data[$i]['focus']
      );
      unset($data[$i]['group']);
      unset($data[$i]['contact']);
      unset($data[$i]['link']);
      unset($data[$i]['focus']);
    }
    unset($headers['group']);
    unset($headers['contact']);
    unset($headers['link']);
    unset($headers['focus']);

    /*
    // Rename/camelCase keys
    $jsonKeys = array();
    $humans = array(
      '/University.+/',
    );
    $replicants = array(
      'University'
    );
    foreach($colHeaders as $header) {
      $header = preg_replace('/^University.+/', 'University', $header);
      $jsonKeys[] = strtolower(preg_replace('/[a-z]/i', '', ucwords($header)));
    }

    $headers = array_combine($jsonKeys, $colHeaders);
    */

    #$json = $this->csvToJson($csv);

    #var_export($json);
    return array('headers' => $headers, 'data' => $data);
  }

  private function csvToJson($csv, $keys = array()) {
    $rows = explode("\n", trim($csv));
    if(!is_array($keys) || empty($keys)) {
      $keys = str_getcsv(array_shift($rows));
    }
    $csvArray = array_map(function ($row) use ($keys) {
      return array_combine($keys, str_getcsv($row));
    }, $rows);
    $json = json_encode($csvArray);

    return $json;
  }
}
