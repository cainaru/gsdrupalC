<?php

namespace Drupal\geolocation_leaflet\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\Plugin\Field\FieldWidget\GeolocationMapWidgetBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Plugin implementation of the 'geolocation_leaflet' widget.
 *
 * @FieldWidget(
 *   id = "geolocation_leaflet",
 *   label = @Translation("Geolocation Leaflet - Geocoding and Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 */
class GeolocationLeafletWidget extends GeolocationMapWidgetBase {

  /**
   * {@inheritdoc}
   */
  protected $mapProviderId = 'leaflet';

  /**
   * {@inheritdoc}
   */
  protected $mapProviderSettingsFormId = 'leaflet_settings';

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $element = parent::form($items, $form, $form_state, $get_delta);

    $element['#attached'] = BubbleableMetadata::mergeAttachments(
      $element['#attached'],
      [
        'library' => [
          'geolocation_leaflet/widget.api.leaflet',
        ],
      ]
    );

    return $element;
  }

}
