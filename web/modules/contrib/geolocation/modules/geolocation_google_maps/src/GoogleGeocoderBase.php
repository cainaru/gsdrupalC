<?php

namespace Drupal\geolocation_google_maps;

use Drupal\geolocation\GeocoderBase;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Base class.
 *
 * @package Drupal\geolocation_google_places_api
 */
abstract class GoogleGeocoderBase extends GeocoderBase {

  protected $geocoderId = NULL;

  /**
   * Google Maps Provider.
   *
   * @var \Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps
   */
  protected $googleMapsProvider = NULL;

  /**
   * {@inheritdoc}
   */
  public function attachments($input_id) {
    $attachments = parent::attachments($input_id);

    $attachments = BubbleableMetadata::mergeAttachments(
      $attachments,
      [
        'drupalSettings' => [
          'geolocation' => [
            'google_map_url' => $this->googleMapsProvider->getGoogleMapsApiUrl(),
            'geocoder' => [
              $this->geocoderId => [
                'inputIds' => [
                  $input_id,
                ],
              ],
            ],
          ],
        ],
      ]
    );

    if (!empty($this->configuration['component_restrictions'])) {
      foreach ($this->configuration['component_restrictions'] as $component => $restriction) {
        if (empty($restriction)) {
          continue;
        }

        switch ($component) {
          case 'administrative_area':
            $component = 'administrativeArea';
            break;

          case 'postal_code':
            $component = 'postalCode';
            break;
        }

        $attachments = BubbleableMetadata::mergeAttachments(
          $attachments,
          [
            'drupalSettings' => [
              'geolocation' => [
                'geocoder' => [
                  $this->geocoderId => [
                    'componentRestrictions' => [
                      $component => $restriction,
                    ],
                  ],
                ],
              ],
            ],
          ]
        );
      }
    }

    return $attachments;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->googleMapsProvider = \Drupal::service('plugin.manager.geolocation.mapprovider')->getMapProvider('google_maps');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultSettings() {
    $default_settings = parent::getDefaultSettings();

    $default_settings['component_restrictions'] = [
      'route' => '',
      'locality' => '',
      'administrative_area' => '',
      'postal_code' => '',
      'country' => '',
    ];

    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptionsForm() {

    $settings = $this->getSettings();

    $form = parent::getOptionsForm();

    $form += [
      'component_restrictions' => [
        '#type' => 'fieldset',
        '#title' => $this->t('Component Restrictions'),
        '#description' => $this->t('See https://developers.google.com/maps/documentation/geocoding/intro#ComponentFiltering'),
        'route' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['route'],
          '#title' => $this->t('Route'),
          '#size' => 15,
        ],
        'locality' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['locality'],
          '#title' => $this->t('Locality'),
          '#size' => 15,
        ],
        'administrative_area' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['administrative_area'],
          '#title' => $this->t('Administrative Area'),
          '#size' => 15,
        ],
        'postal_code' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['postal_code'],
          '#title' => $this->t('Postal code'),
          '#size' => 5,
        ],
        'country' => [
          '#type' => 'textfield',
          '#default_value' => $settings['component_restrictions']['country'],
          '#title' => $this->t('Country'),
          '#size' => 5,
        ],
      ],
    ];

    return $form;
  }

}
