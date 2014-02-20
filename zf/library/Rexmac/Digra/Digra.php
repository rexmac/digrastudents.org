<?php

namespace Rexmac\Digra;

/**
 * @author Rex McConnell <rex@rexmac.com>
 */
class Digra {

  #const URL = 'http://www.digra.org:8080/Plone/shared/game-research-map/';
  const ACADEMICS_ON_TWITTER_URL = 'https://docs.google.com/spreadsheet/pub?key=0Am04dkUpi5bOdHFiWDU2MmNuTjFTRUowazNIY2FGX3c&single=true&gid=0&output=csv';
  #const ARTICLES_URL = 'https://docs.google.com/spreadsheet/pub?key=0AqjgW5ZOmo0kdE8xLUI1RHVyYnQtRmhkZW5HWDh0MXc&single=true&gid=0&output=csv';
  const ARTICLES_URL = 'https://docs.google.com/spreadsheet/pub?key=0Avnyt6dhhUZgdFZOZ0Q0elUweVprTWpjQ1o0dTNDalE&single=true&gid=0&output=csv';
  const JOURNALS_URL = 'https://docs.google.com/spreadsheet/pub?key=0Avnyt6dhhUZgdDkzdkZkMm9BUkNkV291TlZwOWFBT0E&single=true&gid=0&output=csv';
  const POSITIONS_URL = 'https://docs.google.com/spreadsheet/pub?key=0Avnyt6dhhUZgdHp3WVhLZTRNb1ZqOUZGZDZ6N0d0akE&single=true&gid=0&output=csv';
  #const URL = 'http://www.digarec.org/gamesresearchmap/doku.php?id=start:gamesresearchmap';
  const VERSION = '1.1.0-DEV';

  private static $contentMap = array(
    'academicsOnTwitter' => array(
      'url'       => self::ACADEMICS_ON_TWITTER_URL,
      'cacheFile' => 'academicsOnTwitter.json'
    ),
    'articles'  => array(
      'url' => self::ARTICLES_URL,
      'cacheFile' => 'atricles.json'
    ),
    'journals'  => array(
      'url' => self::JOURNALS_URL,
      'cacheFile' => 'journals.json'
    ),
    'positions' => array(
      'url' => self::POSITIONS_URL,
      'cacheFile' => 'positions.json'
    )
  );

  /**
   * Get path to cache directory
   *
   * @return string
   */
  private function getCacheDir() {
    $cacheDir = (defined('APPLICATION_PATH') ? APPLICATION_PATH . '/../' : '') . './cache';
    $realCacheDir = realpath($cacheDir);
    if(false === $realCacheDir) {
      #mkdir($cacheDir, 0775);
      throw new \RuntimeException("Cache directory does not exist ($cacheDir).");
    }
    return $realCacheDir;
  }

  /**
   * Fetch content
   *
   * @param string $type Type of content (@see self::$contentMap)
   * @return 
   * @throws RuntimeException If $type is not a valid content type
   */
  public function fetchContent($type = false) {
    if(array_key_exists($type, self::$contentMap) && is_array(self::$contentMap[$type]) && !empty(self::$contentMap[$type])) {
      // Is there a cached file?
      $cacheDir = $this->getCacheDir();
      $cachedJson = $cacheDir . '/' . $type . '.json';
      if(file_exists($cachedJson)) {
        // How old is the cached file?
        $cachedResults = json_decode(file_get_contents($cachedJson));
        if(($cachedResults->date + 60*60) >= time()) {
          return $cachedResults;
        }
      }

      // Get (read: scrape) HTML from source and write to file
      $response = file_get_contents(self::$contentMap[$type]['url']);
      if($response === false) {
        throw new \RuntimeException('Failed to retrieve CSV response from server.');
      }
      $filename = $cacheDir . '/' . $type . '.csv';
      if(false === file_put_contents($filename, $response)) {
        throw new \RuntimeException('Failed to write CSV response to file.');
      }

      return $this->parseContentCsv($type, $response);
    }

    throw new \RuntimeException("Content type, $type, is not a valid content type.");
  }

  /**
   * Parse content CSV, cache JSON results, and return array of results ready for JSON encoding
   *
   * @param string $type Type of content (@see self::$contentMap)
   * @param string $response CSV response from self::fetchContent
   * @return array Parsed content as array, ready to be JSON encoded; e.g., array('date' => time(), 'headers' => $headers, 'data' => $data);
   * @throws RuntimeException If $type is not a valid content type
   */
  private function parseContentCsv($type = false, $response = '') {
    if(array_key_exists($type, self::$contentMap) && is_array(self::$contentMap[$type]) && !empty(self::$contentMap[$type])) {
      $parseMethodName = 'parse' . ucfirst($type) . 'Csv';
      if(method_exists($this, $parseMethodName)) {
        $results = call_user_func(array($this, $parseMethodName), $response);

        // Write JSON-encoded results to file
        $cacheDir = $this->getCacheDir();
        $filename = $cacheDir . '/' . $type . '.json';
        if(false === file_put_contents($filename, json_encode($results))) {
          throw new \RuntimeException('Failed to write JSON-encoded results to file.');
        }

        return $results;
      }
    }

    throw new \RuntimeException("Content type, $type, is not a valid content type.");
  }

  /**
   * Parse academics-on-twitter CSV and return array of results ready for JSON encoding
   *
   * @param string $csv CSV data to be parsed
   * @return array Parsed content as array, ready to be JSON encoded; e.g., array('date' => time(), 'headers' => $headers, 'data' => $data);
   * @throws InvalidArgumentException If $csv does not contain a value
   * @throws RuntimeException Upon failure to write JSON-encoded results to file.
   */
  private function parseAcademicsOnTwitterCsv($csv = false) {
    if(!$csv) {
      throw new \InvalidArgumentException('$csv argument must contain a value.');
    }

    // Split string into lines
    $rows = explode("\n", trim($csv));
    array_shift($rows);
    array_shift($rows);
    array_shift($rows);
    array_shift($rows);

    // Map column headers to JSON keys
    $headers = array(
      'name'         => 'Name',
      'twitter'      => 'Twitter',
      'affiliations' => 'Affiliation(s)',
      'website'      => 'Website',
      'notes'        => 'Notes, Specializations'
    );
    $keys = array_keys($headers);

    $data = array_map(function ($row) use ($keys) {
      return array_combine($keys, str_getcsv($row));
    }, $rows);

    // Remove column headers (i.e., JSON keys) for detail fields
    unset($headers['notes']);

    // Return result as array, ready to be JSON encoded
    return array('date' => time(), 'headers' => $headers, 'data' => $data);
  }

  /**
   * Parse journal articles CSV and return array of results ready for JSON encoding
   *
   * @param string $csv CSV data to be parsed
   * @return array Parsed content as array, ready to be JSON encoded; e.g., array('date' => time(), 'headers' => $headers, 'data' => $data);
   * @throws InvalidArgumentException If $csv does not contain a value
   * @throws RuntimeException Upon failure to write JSON-encoded results to file.
   */
  private function parseArticlesCsv($csv = false) {
    if(!$csv) {
      throw new \InvalidArgumentException('$csv argument must contain a value.');
    }

    // Split string into lines
    $rows = explode("\n", trim($csv));
    array_shift($rows);
    array_shift($rows);
    array_shift($rows);

    // Map column headers to JSON keys
    $headers = array(
      'category'     => 'Main Category',
      'sub-category' => 'Sub-Category',
      'type'         => 'Type',
      'year'         => 'Year',
      'authors'      => 'Author(s)',
      'title'        => 'Title',
      'publisher'    => 'Publisher',
      'link'         => 'Link to publication or abstract'
    );
    $keys = array_keys($headers);

    $data = array_map(function ($row) use ($keys) {
      return array_combine($keys, str_getcsv($row));
    }, $rows);

    // Remove column headers (i.e., JSON keys) for detail fields
    unset($headers['link']);

    // Return result as array, ready to be JSON encoded
    return array('date' => time(), 'headers' => $headers, 'data' => $data);
  }

  /**
   * Parse journals CSV and return array of results ready for JSON encoding
   *
   * @param string $csv CSV data to be parsed
   * @return array Parsed content as array, ready to be JSON encoded; e.g., array('date' => time(), 'headers' => $headers, 'data' => $data);
   * @throws InvalidArgumentException If $csv does not contain a value
   * @throws RuntimeException Upon failure to write JSON-encoded results to file.
   */
  private function parseJournalsCsv($csv = false) {
    if(!$csv) {
      throw new \InvalidArgumentException('$csv argument must contain a value.');
    }

    // Split string into lines
    $rows = explode("\n", trim($csv));
    array_shift($rows);

    // Map column headers to JSON keys
    $headers = array(
      'journal'                 => 'Journal',
      'homepage'                => 'Homepage',
      'discipline'              => 'Discipline',
      'h5Index'                 => 'H5-Index',
      'h5Median'                => 'H5-Median',
      'impactFactor'            => 'Impact Factor',
      'publisher'               => 'Publisher',
      'publisherHomepage'       => 'Publisher Homepage',
      'frequency'               => 'Frequency (pubs/yr)',
      'journalReviewerUrl'      => 'Journal Reviewer Profile',
      'wordLimit'               => 'Word Limit',
      'briefWordLimit'          => 'Brief Word Limit',
      'issn'                    => 'ISSN',
      'eissn'                   => 'eISSN',
      'submissionGuidelinesUrl' => 'Submission Guidelines URL'
    );
    $keys = array_keys($headers);

    $data = array_map(function ($row) use ($keys) {
      return array_combine($keys, str_getcsv($row));
    }, $rows);

    // Filter data
    for($i = 0; $i < count($data); ++$i) {
      foreach($data[$i] as $key => $value) {
        $data[$i][$key] = trim($value);
      }

      // Relocate detail fields
      $data[$i]['details'] = array(
        'issn'                    => $data[$i]['issn'],
        'eissn'                   => $data[$i]['eissn'],
        'h5Index'                 => $data[$i]['h5Index'],
        'h5Median'                => $data[$i]['h5Median'],
        'impactFactor'            => $data[$i]['impactFactor'],
        'wordLimit'               => $data[$i]['wordLimit'],
        'briefWordLimit'          => $data[$i]['briefWordLimit'],
        'journalReviewerUrl'      => $data[$i]['journalReviewerUrl'],
        'submissionGuidelinesUrl' => $data[$i]['submissionGuidelinesUrl'],
      );
      unset($data[$i]['issn']);
      unset($data[$i]['eissn']);
      unset($data[$i]['h5Index']);
      unset($data[$i]['h5Median']);
      unset($data[$i]['impactFactor']);
      unset($data[$i]['wordLimit']);
      unset($data[$i]['briefWordLimit']);
      unset($data[$i]['journalReviewerUrl']);
      unset($data[$i]['submissionGuidelinesUrl']);
    }
    // Remove column headers (i.e., JSON keys) for detail fields
    unset($headers['homepage']);
    unset($headers['publisherHomepage']);
    unset($headers['issn']);
    unset($headers['eissn']);
    unset($headers['h5Index']);
    unset($headers['h5Median']);
    unset($headers['impactFactor']);
    unset($headers['wordLimit']);
    unset($headers['briefWordLimit']);
    unset($headers['journalReviewerUrl']);
    unset($headers['submissionGuidelinesUrl']);

    // Return result as array, ready to be JSON encoded
    return array('date' => time(), 'headers' => $headers, 'data' => $data);
  }

  /**
   * Parse positions CSV, cache JSON results, and return array of results ready for JSON encoding
   *
   * @param string $csv CSV data to be parsed
   * @return array Parsed content as array, ready to be JSON encoded; e.g., array('date' => time(), 'headers' => $headers, 'data' => $data);
   * @throws InvalidArgumentException If $csv does not contain a value
   * @throws RuntimeException Upon failure to write JSON-encoded results to file.
   */
  private function parsePositionsCsv($csv = false) {
    if(!$csv) {
      throw new \InvalidArgumentException('$csv argument must contain a value.');
    }

    // Split string into lines
    $rows = explode("\n", trim($csv));
    array_shift($rows);

    // Map column headers to JSON keys
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

    // Filter data
    for($i = 0; $i < count($data); ++$i) {
      foreach($data[$i] as $key => $value) {
        $data[$i][$key] = trim($value);
      }

      // Relocate detail fields
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
    // Remove column headers (i.e., JSON keys) for detail fields
    unset($headers['group']);
    unset($headers['contact']);
    unset($headers['link']);
    unset($headers['focus']);

    // Return result as array, ready to be JSON encoded
    return array('date' => time(), 'headers' => $headers, 'data' => $data);
  }

  /**
   * ????
   *
   * @param string $html HTML to be parsed
   */
  private function parseResearchPositionsHtml($html = false) {
    if(!$html) {
      throw new \InvalidArgumentException('$html argument must contain a value.');
    }

    // I fucking hate the DOMDocument class
    $temp = libxml_use_internal_errors(true);
    $dom = new \DOMDocument;
    $dom->loadHTML((string)$html);
    libxml_clear_errors();
    libxml_use_internal_errors($temp);

    // Find the table containing the "games research map"
    $xpath = new \DOMXPath($dom);
    $nodes = $xpath->query("///div[contains(concat(' ',normalize-space(@class),' '),' page ')]/div/table");
    if($nodes->length !== 1) {
      throw new \RuntimeException('Failed to parse HTML. Raw HTML response follows:' . PHP_EOL . PHP_EOL . $html);
    }
    $table = $nodes->item(0);

    // Did I mention that I hate DOMDocument et al.?
    $trs = $table->getElementsByTagName('tr');

    // Extract the headers and the data
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

    // Write raw data to CSV file
    $cacheDir = $this->getCacheDir();
    $filename = $cacheDir . '/positions.raw.csv';
    if(!($fh = fopen($filename, 'w'))) {
      throw new \RuntimeException('Failed to open output file for writing raw CSV data.');
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
    #$colHeaders = str_getcsv(array_shift($rows));

    // Map column headers to JSON keys
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

    /*
     * Filter data to be more consistent
     *
     * This generally consits of removing dangling hyphens and enforcing some naming conventions
     */
    for($i = 0; $i < count($data); ++$i) {
      foreach($data[$i] as $key => $value) {
        $data[$i][$key] = trim($value);
        $data[$i][$key] = preg_replace('/^- *| *-$/', '', $value);
      }
      $data[$i]['university'] = preg_replace('/^-(?:University-)?$/i', '', $data[$i]['university']);
      $data[$i]['department'] = preg_replace('/^-(?:(Faculty\/)?Department-)?$/i', '', $data[$i]['department']);
      $data[$i]['group']      = preg_replace('/^-(?:Lab-)?$/i'       , '', $data[$i]['group']);
      $data[$i]['contact']    = preg_replace('/^-(?:Contact-)?$/i',    '', $data[$i]['contact']);
      $data[$i]['focus']      = preg_replace('/^-(?:Focus-)?$/i',      '', $data[$i]['focus']);
      $data[$i]['link']       = preg_replace('/^-(?:www-)?$/i',        '', $data[$i]['link']);
      $data[$i]['program']    = preg_replace('/^-(?:PhD-)?$/i',     'PhD', $data[$i]['program']);
      $data[$i]['program']    = preg_replace('/Ph\.?D\.?/i',        'PhD', $data[$i]['program']);
      $data[$i]['program']    = preg_replace('/(B|M)\.?(A|S)\.?/', '$1$2', $data[$i]['program']);

      // Relocate detail fields
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
    // Remove column headers (i.e., JSON keys) for detail fields
    unset($headers['group']);
    unset($headers['contact']);
    unset($headers['link']);
    unset($headers['focus']);

    // Return result as array, ready to be JSON encoded
    $results = array('date' => time(), 'headers' => $headers, 'data' => $data);

    // Write JSON-encoded results to file
    $filename = $cacheDir . '/positions.json';
    if(false === file_put_contents($filename, json_encode($results))) {
      throw new \RuntimeException('Failed to write JSON-encoded results to file.');
    }

    return $results;
  }

  /*
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
  }*/
}
