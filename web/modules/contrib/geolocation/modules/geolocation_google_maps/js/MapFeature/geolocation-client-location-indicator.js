/**
 * @typedef {Object} ClientLocationIndicatorSettings
 *
 * @property {String} enable
 */

(function ($, Drupal) {

  'use strict';

  /**
   * ClientLocationIndicatorSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationClientLocationIndicator = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ClientLocationIndicatorSettings} mapSettings.client_location_indicator - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.client_location_indicator !== 'undefined'
            && mapSettings.client_location_indicator.enable
            && navigator.geolocation
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            map.addReadyCallback(function (map) {
              var clientLocationMarker = new google.maps.Marker({
                clickable: false,
                icon: {
                  path: google.maps.SymbolPath.CIRCLE,
                  fillColor: '#039be5',
                  fillOpacity: 1.0,
                  scale: 8,
                  strokeColor: 'white',
                  strokeWeight: 2
                },
                shadow: null,
                zIndex: 999,
                map: map.googleMap
              });

              var indicatorCircle = null;

              setInterval(function(){
                navigator.geolocation.getCurrentPosition(function (currentPosition) {
                  var currentLocation = new google.maps.LatLng(currentPosition.coords.latitude, currentPosition.coords.longitude);
                  clientLocationMarker.setPosition(currentLocation);

                  if (!indicatorCircle) {
                    indicatorCircle = map.addAccuracyIndicatorCircle(currentLocation, parseInt(currentPosition.coords.accuracy));
                  }
                  else {
                    indicatorCircle.setCenter(currentLocation);
                    indicatorCircle.setRadius(parseInt(currentPosition.coords.accuracy));
                  }
                });
              }, 5000);
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
