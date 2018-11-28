<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GeocoderBase.
 *
 * @package Drupal\geolocation
 */
abstract class GeocoderBase extends PluginBase implements GeocoderInterface {

  /**
   * Return plugin default settings.
   *
   * @return array
   *   Default settings.
   */
  protected function getDefaultSettings() {
    return [
      'label' => $this->t('Address'),
      'description' => $this->t('Enter an address to be localized.'),
    ];
  }

  /**
   * Return plugin settings.
   *
   * @return array
   *   Settings.
   */
  public function getSettings() {
    return array_replace_recursive($this->getDefaultSettings(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm() {
    $settings = $this->getSettings();

    return [
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $settings['label'],
        '#size' => 15,
      ],

      'description' => [
        '#type' => 'textfield',
        '#title' => $this->t('Description'),
        '#default_value' => $settings['description'],
        '#size' => 25,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processOptionsForm(array $form_element) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function attachments($input_id) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function formAttachGeocoder(array &$render_array, $element_name) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function formValidateInput(FormStateInterface $form_state) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function formProcessInput(array &$input, $element_name) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function geocode($address) {
    return NULL;
  }

}
