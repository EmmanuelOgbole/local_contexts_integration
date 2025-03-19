<?php

namespace Drupal\local_contexts_integration\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Handles API calls to Local Contexts.
 */
class LocalContextsController extends ControllerBase {
  /**
   * The HTTP client for making API requests.
   *
   * @var \GuzzleHttp\Client
   */
  protected $http_client;

  /**
   * The Route Match service for accessing route parameters.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $route_match;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;



  /**
   * Constructs the LocalContextsController object.
   *
   * @param \GuzzleHttp\Client $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The Route Match service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend service.
   */
  public function __construct(Client $http_client, RouteMatchInterface $route_match, CacheBackendInterface $cache) {
    $this->http_client = $http_client;
    $this->route_match = $route_match;
    $this->cache = $cache;
  }

  /**
   * Dependency injection for services.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   An instance of the LocalContextsController.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('current_route_match'),
      $container->get('cache.local_contexts_integration')
    );
  }

  /**
  * Fetch project data, handling the node retrieval from the route.
  *
  * @return array
  *   The processed API response as a structured array, or an empty array on failure.
  */
  public function fetchProjectData() {
    $node = $this->route_match->getParameter('node');

    if ($node instanceof Node) {
      return $this->fetchProjectDataFromNode($node);
    }

    $message = $node === NULL
      ? 'No node found in the current route.'
      : 'The route parameter is not a valid Node entity.';

    \Drupal::logger('local_contexts_integration')->warning($message);
    return [];
  }


  /**
   * Fetch project data using a specific node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node entity.
   *
   * @return array
   *   The structured response data.
   */
  protected function fetchProjectDataFromNode(Node $node) {
    $config = \Drupal::config('local_contexts_integration.settings');
    $field_identifier = $config->get('field_identifier');

    if (!$node->hasField($field_identifier) || $node->get($field_identifier)->isEmpty()) {
        \Drupal::logger('local_contexts_integration')->warning("Field '$field_identifier' is missing or empty on node ID: {$node->id()}");
        return [];
    }

    $project_id = $node->get($field_identifier)->value;
    $cid = 'local_contexts_project:' . $project_id;
    
    // Check if data is already cached
    if ($cache = $this->cache->get($cid)) {
        \Drupal::logger('local_contexts_integration')->notice("Cache HIT for Project ID: $project_id");
        return $cache->data;
    }

    \Drupal::logger('local_contexts_integration')->notice("Cache MISS for Project ID: $project_id, Fetching from API");

    $raw_data = $this->performApiCall($project_id);
    if (empty($raw_data) || !is_array($raw_data)) {
        \Drupal::logger('local_contexts_integration')->warning("API returned empty or invalid data for Project ID: $project_id");
        return [];
    }

    $filtered_data = $this->filterApiResponse($raw_data);

    if ($this->isFilteredDataEmpty($filtered_data)) {
        \Drupal::logger('local_contexts_integration')->warning("Filtered data is empty for Project ID: $project_id, not caching.");
        return [];
    }

    $expire_time = REQUEST_TIME + (60 * 60 * 24 * 7);
    $this->cache->set($cid, $filtered_data, $expire_time, ['local_contexts_project']);

    return $filtered_data;
  }


  
  /**
   * Filter the API response to keep only specified fields.
   *
   * @param array $data
   *   The original API response data.
   *
   * @return array
   *   The filtered response data.
   */
  protected function filterApiResponse(array $data) {
    return [
      'tk_labels' => $data['tk_labels'] ?? [],
    ];
  }


  /**
   * Perform the actual API call to fetch project data.
   *
   * @param string $project_id
   *   The project ID to fetch data for.
   *
   * @return array
   *   The raw API response data as an associative array, or an empty array on failure.
   */
  protected function performApiCall(string $project_id) {
    $config = \Drupal::config('local_contexts_integration.settings');
    $api_key = $config->get('api_key');
    $api_url = $config->get('api_url');

    if (empty($api_url) || empty($api_key)) {
      \Drupal::logger('local_contexts_integration')->error('API URL or API Key is not configured.');
      return [];
    }

    $url = $api_url . '/projects/' . $project_id . '/';


    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      \Drupal::logger('local_contexts_integration')->error('Invalid API URL: @url', ['@url' => $url]);
      return [];
    }

    try {
      $response = $this->http_client->get($url, [
        'headers' => [
          'X-Api-Key' => $api_key,
          'accept' => 'application/json',
        ],
      ]);

      // Decode the JSON response into an associative array.
      $data = json_decode($response->getBody(), TRUE);


      return is_array($data) ? $data : [];
    } catch (\Exception $e) {
      \Drupal::logger('local_contexts_integration')->error('API Error: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Checks if the filtered API response contains actual values.
   *
   * @param array $filtered_data
   *   The processed API response.
   *
   * @return bool
   *   TRUE if the data is empty, FALSE otherwise.
   */
  protected function isFilteredDataEmpty(array $filtered_data) {
    foreach ($filtered_data as $key => $value) {
        if (!empty($value)) {
            return FALSE; // Data contains actual values
        }
    }
    return TRUE; // Data is completely empty
  }

}