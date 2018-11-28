/**
 * @file
 *   Javascript for the Google Geocoding API geocoder.
 */

/**
 * @property {Object} drupalSettings.geolocation.geocoder.googleGeocodingAPI.componentRestrictions
 * @property {String[]} drupalSettings.geolocation.geocoder.googleGeocodingAPI.inputIds
 */

/**
 * Callback for geocoding.
 *
 * @callback googleGeocoderCallback
 * @param {GoogleAddress[]} results - Returned results
 * @param {String} status - Whether geocoding was successful
 */

/**
 * @typedef {Object} GoogleGeocoder
 * @property {function({}, googleGeocoderCallback)} Geocoder.geocode
 */

/**
 * @extends {GoogleMap}
 * @property {Object} GeocoderStatus
 * @property {String} GeocoderStatus.OK
 *
 * @function
 * @property {function():GoogleGeocoder} Geocoder
 */

(function ($, Drupal) {
  'use strict';

  if (typeof Drupal.geolocation.geocoder === 'undefined') {
    return false;
  }

  drupalSettings.geolocation.geocoder.googleGeocodingAPI = drupalSettings.geolocation.geocoder.googleGeocodingAPI || {};

  Drupal.geolocation.geocoder.googleGeocodingAPI = {};

  /**
   * @param {jQuery} geocoderInput - Input element.
   */
  Drupal.geolocation.geocoder.googleGeocodingAPI.attach = function (geocoderInput) {
    geocoderInput.once().autocomplete({
      autoFocus: true,
      source: function (request, response) {
        if (typeof Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder === 'undefined') {
          Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder = new google.maps.Geocoder();
        }

        var autocompleteResults = [];
        var componentRestrictions = {};
        if (typeof drupalSettings.geolocation.geocoder.googleGeocodingAPI.componentRestrictions !== 'undefined') {
          componentRestrictions = drupalSettings.geolocation.geocoder.googleGeocodingAPI.componentRestrictions;
        }

        Drupal.geolocation.geocoder.googleGeocodingAPI.geocoder.geocode(
          {
            address: request.term,
            componentRestrictions: componentRestrictions
          },
          function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
              $.each(results, function (index, result) {
                autocompleteResults.push({
                  value: result.formatted_address,
                  address: result
                });
              });
            }
            response(autocompleteResults);
          }
        );
      },

      /**
       * Option form autocomplete selected.
       *
       * @param {Object} event - See jquery doc
       * @param {Object} ui - See jquery doc
       * @param {Object} ui.item - See jquery doc
       */
      select: function (event, ui) {
        Drupal.geolocation.geocoder.resultCallback(ui.item.address, $(event.target).data('source-identifier'));
        $('.geolocation-geocoder-google-geocoding-api-state[data-source-identifier="' + $(event.target).data('source-identifier') + '"]').val(1);
      }
    })
    .on('input', function () {
      $('.geolocation-geocoder-google-geocoding-api-state[data-source-identifier="' + $(this).data('source-identifier') + '"]').val(0);
      Drupal.geolocation.geocoder.clearCallback($(this).data('source-identifier'));
    });
  };

  /**
   * Attach geocoder input for Google Geocoding API
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches views geocoder input for Google Geocoding API to relevant elements.
   */
  Drupal.behaviors.geolocationGeocoderGoogleGeocodingApi = {
    attach: function (context) {
      Drupal.geolocation.google.addLoadedCallback(function() {
        $.each(drupalSettings.geolocation.geocoder.googleGeocodingAPI.inputIds, function(index, inputId) {
          var geocoderInput = $('input.geolocation-geocoder-google-geocoding-api[data-source-identifier="' + inputId + '"]', context);
          if (geocoderInput) {
            Drupal.geolocation.geocoder.googleGeocodingAPI.attach(geocoderInput);
          }
        });
      });

      // Load Google Maps API and execute all callbacks.
      Drupal.geolocation.google.load();
    }
  };

})(jQuery, Drupal);
