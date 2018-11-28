<?php

namespace Drupal\geolocation_google_maps\Plugin\views\style;

use Drupal\geolocation\Plugin\views\style\CommonMapBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Allow to display several field items on a common map.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "maps_common",
 *   title = @Translation("Geolocation Google Maps API - CommonMap"),
 *   help = @Translation("Display geolocations on a common map."),
 *   theme = "views_view_list",
 *   display_types = {"normal"},
 * )
 */
class GoogleCommonMap extends CommonMapBase {

  protected $mapProviderId = 'google_maps';
  protected $mapProviderSettingsFormId = 'google_map_settings';

  /**
   * {@inheritdoc}
   */
  public function render() {

    $build = parent::render();

    if ($this->view->getRequest()->get('geolocation_common_map_google_bounds_changed')) {
      $build['#centre'] = [
        'behavior' => 'preserve',
      ];
    }

    $build['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($build['#attached']) ? [] : $build['#attached'],
      [
        'library' => [
          'geolocation_google_maps/commonmap.google',
        ],
      ]
    );

    return $build;
  }

}
