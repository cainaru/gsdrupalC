<?php

namespace Drupal\geolocation_search_api\Plugin\geolocation\DataProvider;

use Drupal\geolocation\DataProviderBase;
use Drupal\views\Plugin\views\field\EntityField;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Provides Google Maps.
 *
 * @DataProvider(
 *   id = "search_api",
 *   name = @Translation("SearchAPI"),
 *   description = @Translation("SearchAPI."),
 * )
 */
class SearchAPI extends DataProviderBase {

  /**
   * {@inheritdoc}
   */
  public function isCommonMapViewsStyleOption(FieldPluginBase $views_field) {
    if (
      $views_field instanceof EntityField
      && $views_field->getPluginId() == 'search_api_field'
    ) {
      $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($views_field->getEntityType());
      if (!empty($field_storage_definitions[$views_field->field])) {
        $field_storage_definition = $field_storage_definitions[$views_field->field];

        if ($field_storage_definition->getType() == 'geolocation') {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromViewsRow(FieldPluginBase $views_field, ResultRow $row) {
    $positions = [];

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
