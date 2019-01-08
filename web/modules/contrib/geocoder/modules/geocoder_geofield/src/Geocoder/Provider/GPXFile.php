<?php

namespace Drupal\geocoder_geofield\Geocoder\Provider;

/**
 * Provides a file handler to be used by 'gpxfile' plugin.
 */
class GPXFile extends GeoPhpProvider {

  /**
   * Geophp Type.
   *
   * @var string
   */
  protected $geophpType = 'gpx';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'gpxfile';
  }

}
