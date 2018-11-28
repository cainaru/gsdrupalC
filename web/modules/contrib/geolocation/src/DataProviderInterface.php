<?php

namespace Drupal\geolocation;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Defines an interface for geolocation DataProvider plugins.
 */
interface DataProviderInterface extends PluginInspectionInterface {

  /**
   * Get views field ID.
   *
   * @param \Drupal\views\Plugin\views\field\FieldPluginBase $views_field
   *   Views field definition.
   *
   * @return bool
   *   Yes or no.
   */
  public function isCommonMapViewsStyleOption(FieldPluginBase $views_field);

  /**
   * Get positions from field.
   *
   * @param \Drupal\views\Plugin\views\field\FieldPluginBase $views_field
   *   Views field definition.
   * @param \Drupal\views\ResultRow $row
   *   Row.
   *
   * @return array
   *   Retrieved locations.
   */
  public function getPositionsFromViewsRow(FieldPluginBase $views_field, ResultRow $row);

}
