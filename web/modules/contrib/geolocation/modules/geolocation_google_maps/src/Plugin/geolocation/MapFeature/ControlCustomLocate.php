<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Locate control element.
 *
 * @MapFeature(
 *   id = "control_locate",
 *   name = @Translation("Map Control - Locate"),
 *   description = @Translation("Add button to center on client location."),
 *   type = "google_maps",
 * )
 */
class ControlCustomLocate extends ControlCustomElementBase {

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $render_array, array $feature_settings, array $context = []) {
    $render_array = parent::alterMap($render_array, $feature_settings, $context);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.control_locate',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'control_locate' => [
                  'enable' => TRUE,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    $render_array['#controls'][$this->pluginId]['control_locate'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#value' => $this->t('Locate'),
      '#attributes' => [
        'class' => ['locate'],
      ],
    ];

    return $render_array;
  }

}
