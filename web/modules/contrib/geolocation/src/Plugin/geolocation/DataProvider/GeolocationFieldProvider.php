<?php

namespace Drupal\geolocation\Plugin\geolocation\DataProvider;

use Drupal\geolocation\DataProviderInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\geolocation\Plugin\views\field\GeolocationField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides Google Maps.
 *
 * @DataProvider(
 *   id = "geolocation_field_provider",
 *   name = @Translation("Geolocation Field"),
 *   description = @Translation("Geolocation Field."),
 * )
 */
class GeolocationFieldProvider extends PluginBase implements DataProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function isCommonMapViewsStyleOption(FieldPluginBase $views_field) {
    return ($views_field->getPluginId() == 'geolocation_field');
  }

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromViewsRow(FieldPluginBase $views_field, ResultRow $row) {
    $positions = [];

    /** @var \Drupal\geolocation\Plugin\views\field\GeolocationField $geolocation_field */
    $entity = $views_field->getEntity($row);

    if (isset($entity->{$views_field->definition['field_name']})) {

      /** @var \Drupal\Core\Field\FieldItemListInterface $geo_items */
      $geo_items = $entity->{$views_field->definition['field_name']};

      foreach ($geo_items as $item) {
        $positions[] = [
          'lat' => $item->get('lat')->getValue(),
          'lng' => $item->get('lng')->getValue(),
        ];
      }
    }

    return $positions;
  }

}
