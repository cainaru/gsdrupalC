/**
 * @typedef {Object} ControlGeocoderSettings
 *
 * @property {String} enable
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Geocoder control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationControlGeocoder = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ControlGeocoderSettings} mapSettings.control_geocoder - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.control_geocoder !== 'undefined'
            && mapSettings.control_geocoder.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (map.wrapper.hasClass('geolocation-control-geocoder-processed')) {
              return;
            }

            map.wrapper.addClass('geolocation-control-geocoder-processed');

            Drupal.geolocation.geocoder.addResultCallback(function(address) {
              map.setCenterByCoordinates({lat: address.geometry.location.lat(), lng: address.geometry.location.lng()});
            }, mapId);
          }
        }
      );
    }
  };

})(jQuery, Drupal);
