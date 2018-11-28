<?php

namespace Drupal\geolocation_leaflet\Plugin\Field\FieldFormatter;

use Drupal\geolocation\Plugin\Field\FieldFormatter\GeolocationMapFormatterBase;

/**
 * Leaflet map formatter.
 *
 * @FieldFormatter(
 *   id = "geolocation_leaflet_map",
 *   module = "geolocation",
 *   label = @Translation("Geolocation Leaflet - Map"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 *
 * @property \Drupal\geolocation_leaflet\Plugin\geolocation\MapProvider\Leaflet $mapProvider
 */
class GeolocationLeafletMapFormatter extends GeolocationMapFormatterBase {

  /**
   * {@inheritdoc}
   */
  protected $mapProviderId = 'leaflet';

}
