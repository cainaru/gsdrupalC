<?php

namespace Drupal\geolocation;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Search plugin manager.
 */
class MapProviderManager extends DefaultPluginManager {

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
    parent::__construct('Plugin/geolocation/MapProvider', $namespaces, $module_handler, 'Drupal\geolocation\MapProviderInterface', 'Drupal\geolocation\Annotation\MapProvider');
    $this->alterInfo('geolocation_mapprovider_info');
    $this->setCacheBackend($cache_backend, 'geolocation_mapprovider');
  }

  /**
   * Return MapProvider by ID.
   *
   * @param string $id
   *   Geocoder ID.
   * @param array $configuration
   *   Configuration.
   *
   * @return \Drupal\geolocation\MapProviderInterface|false
   *   Geocoder instance.
   */
  public function getMapProvider($id, array $configuration = []) {
    $definitions = $this->getDefinitions();
    if (empty($definitions[$id])) {
      return FALSE;
    }
    try {
      /** @var \Drupal\geolocation\MapProviderInterface $instance */
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

}
