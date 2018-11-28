<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Utility\SortArray;

/**
 * Search plugin manager.
 */
class MapFeatureManager extends DefaultPluginManager {

  /**
   * Constructs an GeocoderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/geolocation/MapFeature', $namespaces, $module_handler, 'Drupal\geolocation\MapFeatureInterface', 'Drupal\geolocation\Annotation\MapFeature');
    $this->alterInfo('geolocation_mapfeature_info');
    $this->setCacheBackend($cache_backend, 'geolocation_mapfeature');
  }

  /**
   * Return MapFeature by ID.
   *
   * @param string $id
   *   Geocoder ID.
   * @param array $configuration
   *   Configuration.
   *
   * @return \Drupal\geolocation\MapFeatureInterface|false
   *   Geocoder instance.
   */
  public function getMapFeature($id, array $configuration = []) {
    $definitions = $this->getDefinitions();
    if (empty($definitions[$id])) {
      return FALSE;
    }
    try {
      /** @var \Drupal\geolocation\MapFeatureInterface $instance */
      $instance = $this->createInstance($id, $configuration);
      if ($instance) {
        return $instance;
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }
    return FALSE;
  }

  /**
   * Return MapFeature by ID.
   *
   * @param string $type
   *   Map type.
   *
   * @return string[]
   *   Map feature list.
   */
  public function getMapFeaturesByMapType($type) {
    $definitions = $this->getDefinitions();
    $list = [];
    try {
      foreach ($definitions as $id => $definition) {
        if ($definition['type'] == $type) {
          $list[$id] = $definition;
        }
      }
    }
    catch (\Exception $e) {
      return [];
    }

    uasort($list, [self::class, 'sortByName']);

    return $list;
  }

  /**
   * Support sorting function.
   *
   * @param mixed $a
   *   Element entry.
   * @param mixed $b
   *   Element entry.
   *
   * @return int
   *   Sorting value.
   */
  public static function sortByName($a, $b) {
    return SortArray::sortByKeyString($a, $b, 'name');
  }

}
