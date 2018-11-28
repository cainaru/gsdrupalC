<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider;

use Drupal\Core\Url;
use Drupal\geolocation\MapProviderBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides Google Maps.
 *
 * @MapProvider(
 *   id = "google_maps",
 *   name = @Translation("Google Maps"),
 *   description = @Translation("You do require an API key for this plugin to work."),
 * )
 */
class GoogleMaps extends MapProviderBase {

  /**
   * Google map style - Roadmap.
   *
   * @var string
   */
  public static $ROADMAP = 'ROADMAP';

  /**
   * Google map style - Satellite.
   *
   * @var string
   */
  public static $SATELLITE = 'SATELLITE';

  /**
   * Google map style - Hybrid.
   *
   * @var string
   */
  public static $HYBRID = 'HYBRID';

  /**
   * Google map style - Terrain.
   *
   * @var string
   */
  public static $TERRAIN = 'TERRAIN';

  /**
   * Google maps url.
   *
   * @var string
   */
  public static $GOOGLEMAPSAPIURLBASE = 'https://maps.googleapis.com';

  /**
   * Google maps url from PR China.
   *
   * @var string
   */
  public static $GOOGLEMAPSAPIURLBASECHINA = 'https://maps.google.cn';

  /**
   * Google map max zoom level.
   *
   * @var int
   */
  public static $MAXZOOMLEVEL = 18;

  /**
   * Google map min zoom level.
   *
   * @var int
   */
  public static $MINZOOMLEVEL = 0;

  /**
   * Return all module and custom defined parameters.
   *
   * @return array
   *   Parameters
   */
  public function getGoogleMapsApiParameters() {
    $config = \Drupal::config('geolocation_google_maps.settings');
    $geolocation_parameters = [
      'callback' => 'Drupal.geolocation.google.load',
      'key' => $config->get('google_map_api_key'),
    ];
    $module_parameters = \Drupal::moduleHandler()->invokeAll('geolocation_google_maps_parameters') ?: [];
    $custom_parameters = $config->get('google_map_custom_url_parameters') ?: [];

    // Set the map language to site language if desired and possible.
    if ($config->get('use_current_language') &&  \Drupal::moduleHandler()->moduleExists('language')) {
      $custom_parameters['language'] = \Drupal::languageManager()->getCurrentLanguage()->getId();
    }

    $parameters = array_replace_recursive($custom_parameters, $module_parameters, $geolocation_parameters);

    if (!empty($parameters['client'])) {
      unset($parameters['key']);
    }

    return $parameters;
  }

  /**
   * Return the fully build URL to load Google Maps API.
   *
   * @return string
   *   Google Maps API URL
   */
  public function getGoogleMapsApiUrl() {
    $config = \Drupal::config('geolocation_google_maps.settings');

    $google_url = static::$GOOGLEMAPSAPIURLBASE;
    if ($config->get('china_mode')) {
      $google_url = static::$GOOGLEMAPSAPIURLBASECHINA;
    }

    $parameters = [];
    foreach ($this->getGoogleMapsApiParameters() as $parameter => $value) {
      $parameters[$parameter] = is_array($value) ? implode(',', $value) : $value;
    }
    $url = Url::fromUri($google_url . '/maps/api/js', [
      'query' => $parameters,
      'https' => TRUE,
    ]);
    return $url->toString();
  }

  /**
   * An array of all available map types.
   *
   * @return array
   *   The map types.
   */
  private function getMapTypes() {
    $mapTypes = [
      static::$ROADMAP => 'Road map view',
      static::$SATELLITE => 'Google Earth satellite images',
      static::$HYBRID => 'A mixture of normal and satellite views',
      static::$TERRAIN => 'A physical map based on terrain information',
    ];

    return array_map([$this, 't'], $mapTypes);
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultSettings() {
    $settings['google_map_settings']['map_features']['control_locate']['enabled'] = TRUE;

    return array_replace_recursive(
      parent::getDefaultSettings(),
      [
        'type' => static::$ROADMAP,
        'zoom' => 10,
        'minZoom' => static::$MINZOOMLEVEL,
        'maxZoom' => static::$MAXZOOMLEVEL,
        'rotateControl' => FALSE,
        'scrollwheel' => TRUE,
        'disableDoubleClickZoom' => FALSE,
        'height' => '400px',
        'width' => '100%',
        'preferScrollingToZooming' => FALSE,
        'gestureHandling' => 'auto',
        'map_features' => [
          'marker_infowindow' => [
            'enabled' => TRUE,
          ],
          'control_zoom' => [
            'enabled' => TRUE,
          ],
          'control_maptype' => [
            'enabled' => TRUE,
          ],
        ],
      ]
    );
  }

  /**
   * Return available control positions.
   *
   * @return array
   *   Positions.
   */
  public static function getControlPositions() {
    return [
      'LEFT_TOP' => t('Left top'),
      'LEFT_CENTER' => t('Left center'),
      'LEFT_BOTTOM' => t('Left bottom'),
      'TOP_LEFT' => t('Top left'),
      'TOP_CENTER' => t('Top center'),
      'TOP_RIGHT' => t('Top right'),
      'RIGHT_TOP' => t('Right top'),
      'RIGHT_CENTER' => t('Right center'),
      'RIGHT_BOTTOM' => t('Right bottom'),
      'BOTTOM_LEFT' => t('Bottom left'),
      'BOTTOM_CENTER' => t('Bottom center'),
      'BOTTOM_RIGHT' => t('Bottom right'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings(array $settings) {
    $settings = parent::getSettings($settings);

    $settings['rotateControl'] = (bool) $settings['rotateControl'];
    $settings['scrollwheel'] = (bool) $settings['scrollwheel'];
    $settings['disableDoubleClickZoom'] = (bool) $settings['disableDoubleClickZoom'];
    $settings['preferScrollingToZooming'] = (bool) $settings['preferScrollingToZooming'];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsSummary(array $settings) {
    $types = $this->getMapTypes();
    $settings = array_replace_recursive(
      self::getDefaultSettings(),
      $settings
    );
    $summary = parent::getSettingsSummary($settings);
    $summary[] = $this->t('Map Type: @type', ['@type' => $types[$settings['type']]]);
    $summary[] = $this->t('Zoom level: @zoom', ['@zoom' => $settings['zoom']]);
    $summary[] = $this->t('Height: @height', ['@height' => $settings['height']]);
    $summary[] = $this->t('Width: @width', ['@width' => $settings['width']]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettingsForm(array $settings, array $parents = []) {
    $settings = $this->getSettings($settings);
    $parents_string = '';
    if ($parents) {
      $parents_string = implode('][', $parents) . '][';
    }

    $form = parent::getSettingsForm($settings, $parents);

    /*
     * General settings.
     */
    $form['general_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('General'),
    ];
    $form['height'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['height'],
    ];
    $form['width'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'textfield',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Enter the dimensions and the measurement units. E.g. 200px or 100%.'),
      '#size' => 4,
      '#default_value' => $settings['width'],
    ];
    $form['type'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'select',
      '#title' => $this->t('Default map type'),
      '#options' => $this->getMapTypes(),
      '#default_value' => $settings['type'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['zoom'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'select',
      '#title' => $this->t('Zoom level'),
      '#options' => range(static::$MINZOOMLEVEL, static::$MAXZOOMLEVEL),
      '#description' => $this->t('The initial resolution at which to display the map, where zoom 0 corresponds to a map of the Earth fully zoomed out, and higher zoom levels zoom in at a higher resolution.'),
      '#default_value' => $settings['zoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['maxZoom'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'select',
      '#title' => $this->t('Max Zoom level'),
      '#options' => range(static::$MINZOOMLEVEL, static::$MAXZOOMLEVEL),
      '#description' => $this->t('The maximum zoom level which will be displayed on the map. If omitted, or set to null, the maximum zoom from the current map type is used instead.'),
      '#default_value' => $settings['maxZoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['minZoom'] = [
      '#group' => $parents_string . 'general_settings',
      '#type' => 'select',
      '#title' => $this->t('Min Zoom level'),
      '#options' => range(static::$MINZOOMLEVEL, static::$MAXZOOMLEVEL),
      '#description' => $this->t('The minimum zoom level which will be displayed on the map. If omitted, or set to null, the minimum zoom from the current map type is used instead.'),
      '#default_value' => $settings['minZoom'],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];

    /*
     * Control settings.
     */

    $form['control_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Controls'),
    ];
    $form['rotateControl'] = [
      '#group' => $parents_string . 'control_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Rotate control'),
      '#description' => $this->t('Show rotate control.'),
      '#default_value' => $settings['rotateControl'],
    ];

    /*
     * Behavior settings.
     */
    $form['behavior_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Behavior'),
    ];

    $form['scrollwheel'] = [
      '#group' => $parents_string . 'behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Scrollwheel'),
      '#description' => $this->t('Allow the user to zoom the map using the scrollwheel.'),
      '#default_value' => $settings['scrollwheel'],
    ];
    $form['gestureHandling'] = [
      '#group' => $parents_string . 'behavior_settings',
      '#type' => 'select',
      '#title' => $this->t('Gesture Handling'),
      '#default_value' => $settings['gestureHandling'],
      '#description' => $this->t('Define how to handle interactions with map on mobile. Read the <a href=":introduction">introduction</a> for handling or the <a href=":details">details</a>, <i>available as of v3.27 / Nov. 2016</i>.', [
        ':introduction' => 'https://googlegeodevelopers.blogspot.de/2016/11/smart-scrolling-comes-to-mobile-web-maps.html',
        ':details' => 'https://developers.google.com/maps/documentation/javascript/3.exp/reference#MapOptions',
      ]),
      '#options' => [
        'auto' => $this->t('auto (default)'),
        'cooperative' => $this->t('cooperative'),
        'greedy' => $this->t('greedy'),
        'none' => $this->t('none'),
      ],
      '#process' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'processGroup'],
        ['\Drupal\Core\Render\Element\Select', 'processSelect'],
      ],
      '#pre_render' => [
        ['\Drupal\Core\Render\Element\RenderElement', 'preRenderGroup'],
      ],
    ];
    $form['preferScrollingToZooming'] = [
      '#group' => $parents_string . 'behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Require the user to click the map once to zoom, to ease scrolling behavior.'),
      '#description' => $this->t('Note: this is only relevant, when the Scrollwheel option is enabled.'),
      '#default_value' => $settings['preferScrollingToZooming'],
    ];
    $form['disableDoubleClickZoom'] = [
      '#group' => $parents_string . 'behavior_settings',
      '#type' => 'checkbox',
      '#title' => $this->t('Disable double click zoom'),
      '#description' => $this->t('Disables the double click zoom functionality.'),
      '#default_value' => $settings['disableDoubleClickZoom'],
    ];

    // Push to bottom.
    $map_features = $form['map_features'];
    unset($form['map_features']);
    $form['map_features'] = $map_features;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRenderArray(array $render_array, array $map_settings, array $context = []) {

    $map_settings = $this->getSettings($map_settings);

    $render_array['#attached'] = BubbleableMetadata::mergeAttachments(
      empty($render_array['#attached']) ? [] : $render_array['#attached'],
      [
        'library' => [
          'geolocation_google_maps/googlemapsapi',
        ],
        'drupalSettings' => [
          'geolocation' => [
            'google_map_url' => $this->getGoogleMapsApiUrl(),
            'maps' => [
              $render_array['#id'] => [
                'settings' => [
                  'google_map_settings' => $map_settings,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    $render_array = parent::alterRenderArray($render_array, $map_settings, $context);

    return $render_array;
  }

}
