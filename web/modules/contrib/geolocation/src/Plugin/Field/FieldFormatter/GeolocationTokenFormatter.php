<?php

namespace Drupal\geolocation\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\geolocation\GeolocationItemTokenTrait;

/**
 * Plugin implementation of the 'geolocation_token' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_token",
 *   module = "geolocation",
 *   label = @Translation("Geolocation tokenized text"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 */
class GeolocationTokenFormatter extends FormatterBase {

  use GeolocationItemTokenTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [];
    $settings['tokenized_text'] = [
      'value' => '',
      'format' => filter_default_format(),
    ];
    $settings += parent::defaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $element['tokenized_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Tokenized text'),
      '#description' => $this->t('Enter any text or HTML to be shown for each value. Tokens will be replaced as available. The "token" module greatly expands the number of available tokens as well as provides a comfortable token browser.'),
    ];
    if (!empty($settings['tokenized_text']['value'])) {
      $form['tokenized_text']['#default_value'] = $settings['tokenized_text']['value'];
    }

    if (!empty($settings['info_text']['format'])) {
      $form['tokenized_text']['#format'] = $settings['tokenized_text']['format'];
    }

    $element['token_help'] = $this->getTokenHelp();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings();

    $summary = [];
    if (
      !empty($settings['tokenized_text']['value'])
      && !empty($settings['tokenized_text']['format'])
    ) {
      $summary[] = $this->t('Tokenized Text: %text', [
        '%text' => Unicode::truncate(
          check_markup($settings['tokenized_text']['value'], $settings['tokenized_text']['format']),
          100,
          TRUE,
          TRUE
        ),
      ]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $token_context = [
      $this->fieldDefinition->getTargetEntityTypeId() => $items->getEntity(),
    ];

    $elements = [];
    foreach ($items as $delta => $item) {
      $token_context['geolocation_current_item'] = $item;

      $tokenized_text = $this->getSetting('tokenized_text');

      if (
        !empty($tokenized_text['value'])
        && !empty($tokenized_text['format'])
      ) {
        $elements[$delta] = [
          '#type' => 'processed_text',
          '#text' => \Drupal::token()->replace(
            $tokenized_text['value'],
            $token_context,
            [
              'callback' => [$this, 'geolocationItemTokens'],
              'clear' => TRUE,
            ]
          ),
          '#format' => $tokenized_text['format'],
        ];
      }
    }

    return $elements;
  }

}
