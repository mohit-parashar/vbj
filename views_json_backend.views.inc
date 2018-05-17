<?php
/**
 * @file
 *
 * Views will look in this file for any views related hooks. Mainly, this is
 * where you put your hook_views_data() implementation.
 */


/**
 * Implements hook_views_data().
 */
function views_json_backend_views_data() {
  $data = [];

  // Base data.
  $data['views_json_backend']['table']['group'] = t('Json');
  $data['views_json_backend']['table']['base'] = [
    'title' => t('Json'),
    'query_id' => 'views_json_backend',
    'help' => t('Json data provided by the API\'s endpoint.'),
  ];

  // Fields.
  $data['views_json_backend']['json_text'] = [
    'title' => t('Json Text'),
    'help' => t('Text in the Json file.'),
    'field' => [
      'id' => 'views_json_backend_standard',
    ],
  ];

  return $data;
}