<?php

/**
 * @file
 * Contains \Drupal\views_json_backend\Plugin\views\query\Json.
 */

namespace Drupal\views_json_backend\Plugin\views\query;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Views query plugin for an Json query.
 *
 * @ingroup views_query_plugins
 *
 * @ViewsQuery(
 *   id = "views_json_backend",
 *   title = @Translation("Kson Query"),
 *   help = @Translation("Query will be generated and run using the Json backend.")
 * )
 */
class Json extends QueryPluginBase {

	 /**
   * Constructs an Json object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['json_file']['default'] = '';
    $options['row_xpath']['default'] = '';
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['json_file'] = [
      '#type' => 'textfield',
      '#title' => $this->t('json File'),
      '#default_value' => $this->options['json_file'],
      '#description' => $this->t('The URL or path to the json file.'),
      '#maxlength' => 1024,
      '#required' => TRUE,
    ];

    $form['row_xpath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Row Xpath'),
      '#default_value' => $this->options['row_xpath'],
      '#description' => $this->t('An xpath function that selects rows.'),
      '#maxlength' => 1024,
      '#required' => TRUE,
    ];
  }

  /**
   * Ensures a table exists in the query.
   *
   * This replicates the interface of Views' default SQL backend to simplify
   * the Views integration of the Fitbit API. Since the Fitbit API has no
   * concept of "tables", this method implementation does nothing. If you are
   * writing Fitbit API-specific Views code, there is therefore no reason at all
   * to call this method.
   * See https://www.drupal.org/node/2484565 for more information.
   *
   * @return string
   *   An empty string.
   */
  public function ensureTable($table, $relationship = NULL) {
    return $table;
  }

  /**
   * Adds a field to the table. In our case, the Fitibt API has no
   * notion of limiting the fields that come back, so tracking a list
   * of fields to fetch is irrellevant for us. Hence this function body is more
   * or less empty and it serves only to satisfy handlers that may assume an
   * addField method is present b/c they were written against Views' default SQL
   * backend.
   *
   * This replicates the interface of Views' default SQL backend to simplify
   * the Views integration of the Fitbit API.
   *
   * @param string $table
   *   NULL in most cases, we could probably remove this altogether.
   * @param string $field
   *   The name of the metric/dimension/field to add.
   * @param string $alias
   *   Probably could get rid of this too.
   * @param array $params
   *   Probably could get rid of this too.
   *
   * @return string
   *   The name that this field can be referred to as.
   *
   * @see \Drupal\views\Plugin\views\query\Sql::addField()
   */
  public function addField($field, $xpath) {
    $this->extraFields[$field] = $xpath;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ViewExecutable $view) {
    // When creating a new view, there won't be a query set yet.
    /*if ($view->build_info['query'] === '') {
      $this->messenger->setMessage($this->t('Please configure the query settings.'), 'warning');
      return;
    }*/


  try {
    $response = $this->httpClient->get(
      $this->options['json_file'],
      array(
        'headers' => 
        array(
          'Accept' => 'application/json'
        )
      )
    );

    $data = (string) $response->getBody();
    if (empty($data)) {
      $data = '';
    }else{
        $json = json_decode($data);
    }
  }
  catch (RequestException $e) {
      $data = '';
  }

    $xpath = $this->options['row_xpath'];
    foreach ($json->$xpath as $row) {
      $result_row = new ResultRow();
      $view->result[] = $result_row;

      foreach ($view->field as $field_name => $field) {
        if (!isset($field->options['xpath_selector']) || $field->options['xpath_selector'] === '') {
          continue;
        }
        $xpath_selector = $field->options['xpath_selector'];
        //$result_row->$field_name = $row->$field->options['xpath_selector'];
        $result_row->$field_name = $row->$xpath_selector;
      }
    }

    if (!empty($view->result)) {
      // Re-index array
      $index = 0;
      foreach ($view->result as &$row) {
        $row->index = $index++;
      }
    }
  }
}