<?php

namespace rodolfo\Apigoogle;

/**
 * @file
 * SearchConsoleApi class file.
 */

use Google_Service_Webmasters;
use Google_Client;
use Google_Service_Webmasters_SearchAnalyticsQueryRequest;
use Google_Service_Webmasters_ApiDimensionFilter;
use Google_Service_Webmasters_ApiDimensionFilterGroup;

/**
 * Class SearchConsoleApi.
 */
class SearchConsoleApi extends Google_Service_Webmasters {

  /**
   * Instance of Google_Service_Webmasters_SearchAnalyticsQueryRequest.
   *
   * @var Google_Service_Webmasters_SearchAnalyticsQueryRequest
   */
  public $query;
  private $queryOptions;
  private $client;
  private $authJson;
  private $applicationName;
  private $scopes;
  private $connectionInitTime = 0;

  const WEBMASTERS_ROW_LIMIT = 25000;

  /**
   * SearchConsoleApi constructor.
   */
  public function __construct($auth_json = 'service-account.json') {
    $this->applicationName = "SearchConsoleApi";
    $this->scopes = ['https://www.googleapis.com/auth/webmasters.readonly'];
    $this->authJson = $auth_json;
  }

  /**
   * Get default options for queries.
   *
   * @return array
   *   Search console query options.
   */
  public static function getDefaultOptions() {
    return [
      'dimensions' => ['date', 'device', 'page', 'query', 'country'],
    ];
  }

  /**
   * Set up connection to Google.
   */
  public function initNewConnection() {
    if ($this->connectionInitTime === 0 || time() - $this->connectionInitTime > 3500) {
      $this->connectionInitTime = time();
      $this->client = new Google_Client();
      // Note that using json for "Service accounts" login is the prefered way
      // according to docs at vendor/google/apiclient/UPGRADING.md.
      $this->client->setAuthConfig($this->authJson);
      $this->client->setApplicationName($this->applicationName);
      $this->client->setScopes($this->scopes);
      parent::__construct($this->client);
    }
  }

  /**
   * Set query options.
   *
   * @param object $query_options
   *   Google_Service_Webmasters_SearchAnalyticsQueryRequest() object - creates
   *   the query.
   */
  public function setQueryOptions($query_options) {
    $this->query = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();
    $this->query->setStartDate($query_options['start_date']);
    $this->query->setEndDate($query_options['end_date']);
    $this->query->setDimensions($query_options['dimensions']);
    $this->query->setRowLimit(self::WEBMASTERS_ROW_LIMIT);
    $this->query->setStartRow(0);
    $this->queryOptions = $query_options;
    if (isset($query_options['setDimensionFilterGroups'])) {
      $filter = new Google_Service_Webmasters_ApiDimensionFilter();
      $filter->setDimension($query_options['setDimensionFilterGroups']['filters']['dimension']);
      $filter->setOperator($query_options['setDimensionFilterGroups']['filters']['operator']);
      $filter->setExpression($query_options['setDimensionFilterGroups']['filters']['expression']);

      $filter_group = new Google_Service_Webmasters_ApiDimensionFilterGroup();
      $filter_group->setFilters([$filter]);

      $this->query->setDimensionFilterGroups([$filter_group]);
    }
  }

  /**
   * Get data from the Search Console API.
   *
   * @param array $options
   * @return array
   */
   */
     public function getRows($options) {

		$this->initNewConnection();
		$this->setQueryOptions($options);

		 $result="";
		 $rows="";
		  try {
			$result = $this->searchanalytics->query($options['site_url'], $this->query);
			$rows = $result->getRows();
			return json_encode($rows,true);
		  }
		  catch (\Google_Service_Exception $e) {
		   return "0"; 
		  }  
	}
  }
