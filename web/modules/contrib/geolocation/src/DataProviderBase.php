<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\PluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\views\ResultRow;

/**
 * Class DataProviderBase.
 *
 * @package Drupal\geolocation
 */
abstract class DataProviderBase extends PluginBase implements DataProviderInterface, ContainerFactoryPluginInterface {

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new GeocoderBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isCommonMapViewsStyleOption(FieldPluginBase $views_field) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPositionsFromViewsRow(FieldPluginBase $views_field, ResultRow $row) {
    return [];
  }

}
