<?php

namespace Drupal\geocoder_geofield\Geocoder\Provider;

use Geocoder\Exception\NoResult;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Provider\AbstractProvider;
use Geocoder\Provider\Provider;

/**
 * Provides an abstract file handler to be used by GeoPHP Wrapper.
 */
abstract class GeoPhpProvider extends AbstractProvider implements Provider {

  /**
   * Geophp interface.
   *
   * @var \Drupal\geofield\GeoPHP\GeoPHPInterface
   */
  protected $geophp;

  /**
   * Geophp Type.
   *
   * @var string
   */
  protected $geophpType = '';

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();
    $this->geophp = \Drupal::service('geofield.geophp');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'geophp_provider';
  }

  /**
   * {@inheritdoc}
   */
  public function geocode($filename) {
    if (file_exists($filename)) {
      $geophp_string = file_get_contents($filename);
      /* @var \Geometry|\GeometryCollection $geometry */
      $geometry = $this->geophp->load($geophp_string, $this->geophpType);
      if (!empty($geometry->components) || $geometry instanceof \Geometry) {
        return $geometry;
      }
    }
    throw new NoResult(sprintf('Could not find %s data in file: "%s".', $this->geophpType, basename($filename)));
  }

  /**
   * {@inheritdoc}
   */
  public function reverse($latitude, $longitude) {
    throw new UnsupportedOperation(sprintf('The %s plugin is not able to do reverse geocoding.', $this->geophpType));
  }

}
