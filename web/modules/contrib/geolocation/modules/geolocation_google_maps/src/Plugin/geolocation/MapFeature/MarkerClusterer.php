<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapFeature;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\MapFeatureBase;
use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;

/**
 * Provides marker clusterer.
 *
 * @MapFeature(
 *   id = "marker_clusterer",
 *   name = @Translation("Marker Clusterer"),
 *   description = @Translation("Group elements on the map."),
 *   type = "google_maps",
 * )
 */
class MarkerClusterer extends MapFeatureBase {

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    return [
      'image_path' => '',
      'styles' => [],
      'max_zoom' => 15,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents) {
    $settings = $this->getSettings($settings);
    $form['description'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => $this->t('Various <a href=":url">examples</a> are available.', [':url' => 'https://developers.google.com/maps/documentation/javascript/marker-clustering']),
    ];
    $form['image_path'] = [
      '#title' => $this->t('Cluster image path'),
      '#type' => 'textfield',
      '#default_value' => $settings['image_path'],
      '#description' => $this->t("Set the marker image path. If omitted, the default image path %url will be used.", ['%url' => 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m']),
    ];
    $form['styles'] = [
      '#title' => $this->t('Styles of the Cluster'),
      '#type' => 'textarea',
      '#default_value' => $settings['styles'],
      '#description' => $this->t(
        'Set custom Cluster styles in JSON Format. Custom Styles have to be set for all 5 Cluster Images. See the <a href=":reference">reference</a> for details.',
        [
          ':reference' => 'https://googlemaps.github.io/js-marker-clusterer/docs/reference.html',
        ]
      ),
    ];
    $form['max_zoom'] = [
      '#title' => $this->t('Max Zoom'),
      '#type' => 'select',
      '#options' => range(GoogleMaps::$MINZOOMLEVEL, GoogleMaps::$MAXZOOMLEVEL),
      '#default_value' => $settings['max_zoom'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateSettingsForm(array $values, FormStateInterface $form_state, array $parents) {
    $marker_clusterer_styles = $values['styles'];
    if (!empty($marker_clusterer_styles)) {
      $style_parents = $parents;
      $style_parents[] = 'styles';
      if (!is_string($marker_clusterer_styles)) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Please enter a JSON string as style.'));
      }
      $json_result = json_decode($marker_clusterer_styles);
      if ($json_result === NULL) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Decoding style JSON failed. Error: %error.', ['%error' => json_last_error()]));
      }
      elseif (!is_array($json_result)) {
        $form_state->setErrorByName(implode('][', $style_parents), $this->t('Decoded style JSON is not an array.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterMap(array $render_array, array $feature_settings, array $context = []) {
    $render_array = parent::alterMap($render_array, $feature_settings, $context);

    $feature_settings = $this->getSettings($feature_settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/geolocation.markerclusterer',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'maps' => [
              $render_array['#id'] => [
                'marker_clusterer' => [
                  'enable' => TRUE,
                  'imagePath' => $feature_settings['image_path'],
                  'styles' => $feature_settings['styles'],
                  'maxZoom' => (int) $feature_settings['max_zoom'],
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
