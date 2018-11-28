/**
 * @typedef {Object} MarkerInfoWindowSettings
 *
 * @property {String} enable
 * @property {bool} infoAutoDisplay
 * @property {bool} disableAutoPan
 * @property {bool} infoWindowSolitary
 */

/**
 * @typedef {Object} GoogleInfoWindow
 * @property {Function} open
 * @property {Function} close
 */

/**
 * @property {GoogleInfoWindow} GeolocationGoogleMap.infoWindow
 * @property {function({}):GoogleInfoWindow} GeolocationGoogleMap.InfoWindow
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Marker InfoWindow.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMarkerInfoWindow = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {MarkerInfoWindowSettings} mapSettings.marker_infowindow - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.marker_infowindow !== 'undefined'
            && mapSettings.marker_infowindow.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            if (map.wrapper.hasClass('geolocation-marker-infowindow-processed')) {
              return;
            }

            map.wrapper.addClass('geolocation-marker-infowindow-processed');

            map.addMarkerAddedCallback(function (currentMarker) {
              if (typeof (currentMarker.locationWrapper) === 'undefined') {
                return;
              }

              var content = currentMarker.locationWrapper.find('.location-content');

              if (content.length < 1) {
                return;
              }
              content = content.html();

              // Set the info popup text.
              var currentInfoWindow = new google.maps.InfoWindow({
                content: content,
                disableAutoPan: mapSettings.marker_infowindow.disableAutoPan
              });

              currentMarker.addListener('click', function () {
                if (mapSettings.marker_infowindow.infoWindowSolitary) {
                  if (typeof map.infoWindow !== 'undefined') {
                    map.infoWindow.close();
                  }
                  map.infoWindow = currentInfoWindow;
                }
                currentInfoWindow.open(map.googleMap, currentMarker);
              });

              if (mapSettings.marker_infowindow.infoAutoDisplay) {
                google.maps.event.addListenerOnce(map.googleMap, 'tilesloaded', function () {
                  google.maps.event.trigger(currentMarker, 'click');
                });
              }
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
