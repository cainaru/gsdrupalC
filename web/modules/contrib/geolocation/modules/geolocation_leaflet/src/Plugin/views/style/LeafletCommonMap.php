<?php

namespace Drupal\geolocation_leaflet\Plugin\views\style;

use Drupal\geolocation\Plugin\views\style\CommonMapBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Allow to display several field items on a common map.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "geolocation_leaflet",
 *   title = @Translation("Geolocation Leaflet - CommonMap"),
 *   help = @Translation("Display geolocations on a common map."),
 *   theme = "views_view_list",
 *   display_types = {"normal"},
 * )
 */
class LeafletCommonMap extends CommonMapBase {

  protected $mapProviderId = 'leaflet';

  /**
   * {@inheritdoc}
   */
  public function render() {

    $build = parent::render();
    $build['#maptype'] = 'leaflet';

    $build['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($build['#attached']) ? [] : $build['#attached'],
      [
        'library' => [
          'geolocation_leaflet/geolocation.leaflet',
        ],
      ]
    );

    return $build;
  }

}
