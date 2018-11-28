<?php

namespace Drupal\geolocation_google_maps\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\Plugin\Field\FieldWidget\GeolocationMapWidgetBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Plugin implementation of the 'geolocation_googlegeocoder' widget.
 *
 * @FieldWidget(
 *   id = "geolocation_googlegeocoder",
 *   label = @Translation("Geolocation Google Maps API - Geocoding and Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 */
class GeolocationGoogleWidget extends GeolocationMapWidgetBase {

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

    $settings['google_map_settings']['map_features']['control_geocoder'] = [
      'enabled' => TRUE,
      'weight' => -100,
    ];
    $settings['google_map_settings']['map_features']['control_recenter']['enabled'] = TRUE;
    $settings['google_map_settings']['map_features']['control_locate']['enabled'] = TRUE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $element = parent::form($items, $form, $form_state, $get_delta);

    $element['#attributes']['data-widget-type'] = 'google';

    $element['#attached'] = BubbleableMetadata::mergeAttachments(
      $element['#attached'],
      [
        'library' => [
          'geolocation_google_maps/widget.api.google',
        ],
      ]
    );

    return $element;
  }

}
