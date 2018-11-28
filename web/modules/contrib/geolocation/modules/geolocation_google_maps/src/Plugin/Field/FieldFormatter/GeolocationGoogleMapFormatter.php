<?php

namespace Drupal\geolocation_google_maps\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;
use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Plugin implementation of the 'geolocation_googlemap' formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_map",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Google Maps API - Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 *
 * @property \Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps $mapProvider
 */
class GeolocationGoogleMapFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  protected $mapProviderId = 'google_maps';

  /**
   * {@inheritdoc}
   */
  protected $mapProviderSettingsFormId = 'google_map_settings';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();
    $settings['google_map_settings'] = GoogleMaps::getDefaultSettings();

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    $form = parent::settingsForm($form, $form_state);

    $form['use_overridden_map_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use custom map settings if provided'),
      '#description' => $this->t('The Geolocation GoogleGeocoder widget optionally allows to define custom map settings to use here.'),
      '#default_value' => $settings['use_overridden_map_settings'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);

    $settings = $this->getSettings();

    if (
      $settings['use_overridden_map_settings']
      && !empty($items->get(0)->getValue()['data'][$this->mapProviderSettingsFormId])
      && is_array($items->get(0)->getValue()['data'][$this->mapProviderSettingsFormId])
    ) {
      $google_map_settings = $this->mapProvider->getSettings($items->get(0)->getValue()['data'][$this->mapProviderSettingsFormId]);

      if (!empty($settings['common_map'])) {
        $elements['#settings'] = $google_map_settings;
      }
      else {
        foreach (Element::children($elements) as $delta => $element) {
          $elements[$delta]['#settings'] = $google_map_settings;
        }
      }
    }

    return $elements;
  }

}
