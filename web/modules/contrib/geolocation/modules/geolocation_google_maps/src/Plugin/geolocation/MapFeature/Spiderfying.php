<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\geolocation\MapFeatureBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Spiderfying.
 *
 * @MapFeature(
 *   id = "spiderfying",
 *   name = @Translation("Spiderfying"),
 *   description = @Translation("Split up overlapping markers on click."),
 *   type = "google_maps",
 * )
 */
class Spiderfying extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $render_array, array $feature_settings, array $context = []) {
    $render_array = parent::alterMap($render_array, $feature_settings, $context);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.spiderfying',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'spiderfying' => [
                  'enable' => TRUE,
                  'spiderfiable_marker_path' => base_path() . drupal_get_path('module', 'geolocation_google_maps') . '/images/marker-plus.svg',
                ],
              ],
            ],
          ],
        ],
      ]
    );

    return $render_array;
  }

}
