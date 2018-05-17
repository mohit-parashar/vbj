<?php

/**
 * @file
 * Contains \Drupal\views_json_backend\Plugin\views\field\Standard.
 */

namespace Drupal\views_json_backend\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\Plugin\views\field\MultiItemsFieldHandlerInterface;

/**
 * A handler to provide an Json text field.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("views_json_backend_standard")
 */
class Standard extends FieldPluginBase implements MultiItemsFieldHandlerInterface {

  use JsonFieldHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    return parent::defineOptions() + $this->getDefaultJsonOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form = $this->getDefaultJsonOptionsForm($form, $form_state);

    parent::buildOptionsForm($form, $form_state);
  }

}
