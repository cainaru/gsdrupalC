<?php

namespace Drupal\geocoder_geofield\Geocoder\Provider;

/**
 * Provides a file handler to be used by 'kmlfile' plugin.
 */
class KMLFile extends GeoPhpProvider {

  /**
   * Geophp Type.
   *
   * @var string
   */
  protected $geophpType = 'kml';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'kmlfile';
  }

}
