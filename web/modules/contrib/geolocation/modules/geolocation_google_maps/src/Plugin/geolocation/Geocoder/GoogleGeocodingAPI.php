<?php

namespace Drupal\geolocation_google_maps\Plugin\geolocation\Geocoder;

use Drupal\geolocation_google_maps\Plugin\geolocation\MapProvider\GoogleMaps;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;
use Drupal\geolocation_google_maps\GoogleGeocoderBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Provides the Google Geocoding API.
 *
 * @Geocoder(
 *   id = "google_geocoding_api",
 *   name = @Translation("Google Geocoding API"),
 *   description = @Translation("You do require an API key for this plugin to work."),
 *   locationCapable = true,
 *   boundaryCapable = true,
 * )
 */
class GoogleGeocodingAPI extends GoogleGeocoderBase {

  protected $geocoderId = 'googleGeocodingAPI';

  /**
   * {@inheritdoc}
   */
  public function attachments($input_id) {
    $attachments = parent::attachments($input_id);

    $attachments = BubbleableMetadata::mergeAttachments(
      $attachments,
      [
        'library' => [
          'geolocation_google_maps/geocoder.googlegeocodingapi',
        ],
      ]
    );

    return $attachments;
  }

  /**
   * {@inheritdoc}
   */
  public function formAttachGeocoder(array &$render_array, $element_name) {
    $config = \Drupal::config('geolocation_google_maps.settings');

    $settings = $this->getSettings();

    $render_array['geolocation_geocoder_google_geocoding_api'] = [
      '#type' => 'search',
      '#title' => $settings['label'],
      '#placeholder' => $settings['description'],
      '#description' => $settings['description'],
      '#description_display' => 'after',
      '#size' => '25',
      '#attributes' => [
        'class' => [
          'form-autocomplete',
          'geolocation-geocoder-google-geocoding-api',
        ],
        'data-source-identifier' => $element_name,
      ],
    ];

    $render_array['geolocation_geocoder_google_geocoding_api_state'] = [
      '#type' => 'hidden',
      '#default_value' => 1,
      '#attributes' => [
        'class' => [
          'geolocation-geocoder-google-geocoding-api-state',
        ],
        'data-source-identifier' => $element_name,
      ],
    ];

    $render_array['#attached'] = $this->attachments($element_name);

    if (!empty($config->get('google_map_custom_url_parameters')['region'])) {
      $render_array['#attached']['drupalSettings']['geolocation']['geocoder']['googleGeocodingAPI']['region'] = $config->get('google_map_custom_url_parameters')['region'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formValidateInput(FormStateInterface $form_state) {
    $validate = parent::formValidateInput($form_state);

    $input = $form_state->getUserInput();
    if (
      !empty($input['geolocation_geocoder_google_geocoding_api'])
      && empty($input['geolocation_geocoder_google_geocoding_api_state'])
    ) {
      $location_data = $this->geocode($input['geolocation_geocoder_google_geocoding_api']);

      if (empty($location_data)) {
        $form_state->setErrorByName('geolocation_geocoder_google_geocoding_api', $this->t('Failed to geocode %input.', ['%input' => $input['geolocation_geocoder_google_geocoding_api']]));
        return FALSE;
      }
    }
    return $validate;
  }

  /**
   * {@inheritdoc}
   */
  public function formProcessInput(array &$input, $element_name) {
    $return = parent::formProcessInput($input, $element_name);

    if (
      !empty($input['geolocation_geocoder_google_geocoding_api'])
      && empty($input['geolocation_geocoder_google_geocoding_api_state'])
    ) {
      $location_data = $this->geocode($input['geolocation_geocoder_google_geocoding_api']);

      if (empty($location_data)) {
        $input['geolocation_geocoder_google_geocoding_api_state'] = 0;
        return FALSE;
      }

      $input['geolocation_geocoder_google_geocoding_api'] = $location_data['address'];
      $input['geolocation_geocoder_google_geocoding_api_state'] = 1;

      return $location_data;
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function geocode($address) {
    $config = \Drupal::config('geolocation_google_maps.settings');
    if (empty($address)) {
      return FALSE;
    }

    $request_url = GoogleMaps::$GOOGLEMAPSAPIURLBASE;
    if ($config->get('china_mode')) {
      $request_url = GoogleMaps::$GOOGLEMAPSAPIURLBASECHINA;
    }
    $request_url .= '/maps/api/geocode/json?address=' . $address;

    if (!empty($config->get('google_map_api_server_key'))) {
      $request_url .= '&key=' . $config->get('google_map_api_key');
    }
    elseif (!empty($config->get('google_map_api_key'))) {
      $request_url .= '&key=' . $config->get('google_map_api_key');
    }
    if (!empty($this->configuration['component_restrictions'])) {
      $request_url .= '&components=';
      foreach ($this->configuration['component_restrictions'] as $component_id => $component_value) {
        $request_url .= $component_id . ':' . $component_value . '|';
      }
    }
    if (!empty($config->get('google_map_custom_url_parameters')['language'])) {
      $request_url .= '&language=' . $config->get('google_map_custom_url_parameters')['language'];
    }

    try {
      $result = Json::decode(\Drupal::httpClient()->request('GET', $request_url)->getBody());
    }
    catch (RequestException $e) {
      watchdog_exception('geolocation', $e);
      return FALSE;
    }

    if (
      $result['status'] != 'OK'
      || empty($result['results'][0]['geometry'])
    ) {
      return FALSE;
    }

    return [
      'location' => [
        'lat' => $result['results'][0]['geometry']['location']['lat'],
        'lng' => $result['results'][0]['geometry']['location']['lng'],
      ],
      // TODO: Add viewport or build it if missing.
      'boundary' => [
        'lat_north_east' => empty($result['results'][0]['geometry']['viewport']) ? $result['results'][0]['geometry']['location']['lat'] + 0.005 : $result['results'][0]['geometry']['viewport']['northeast']['lat'],
        'lng_north_east' => empty($result['results'][0]['geometry']['viewport']) ? $result['results'][0]['geometry']['location']['lng'] + 0.005 : $result['results'][0]['geometry']['viewport']['northeast']['lng'],
        'lat_south_west' => empty($result['results'][0]['geometry']['viewport']) ? $result['results'][0]['geometry']['location']['lat'] - 0.005 : $result['results'][0]['geometry']['viewport']['southwest']['lat'],
        'lng_south_west' => empty($result['results'][0]['geometry']['viewport']) ? $result['results'][0]['geometry']['location']['lng'] - 0.005 : $result['results'][0]['geometry']['viewport']['southwest']['lng'],
      ],
      'address' => empty($result['results'][0]['formatted_address']) ? '' : $result['results'][0]['formatted_address'],
    ];
  }

}
