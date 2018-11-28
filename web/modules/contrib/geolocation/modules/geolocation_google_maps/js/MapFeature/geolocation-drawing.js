/**
 * @typedef {Object} DrawingSettings
 *
 * @property {String} enable
 */

(function ($, Drupal) {

  'use strict';

  /**
   * DrawingSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationDrawing = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {DrawingSettings} mapSettings.drawing - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.drawing !== 'undefined'
            && mapSettings.drawing.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            map.addReadyCallback(function (map) {
              var locations = [];

              $('#' + map.id, context).find('.geolocation-location').each(function (index, location) {
                location = $(location);
                locations.push(new google.maps.LatLng(Number(location.data('lat')), Number(location.data('lng'))));
              });

              if (!locations.length) {
                return;
              }

              var drawingSettings = mapSettings.drawing.settings;


              if (drawingSettings.polygon && drawingSettings.polygon !== '0') {
                var polygon = new google.maps.Polygon({
                  paths: locations,
                  strokeColor: drawingSettings.strokeColor,
                  strokeOpacity: drawingSettings.strokeOpacity,
                  strokeWeight: drawingSettings.strokeWeight,
                  geodesic: drawingSettings.geodesic,
                  fillColor: drawingSettings.fillColor,
                  fillOpacity: drawingSettings.fillOpacity
                });
                polygon.setMap(map.googleMap);
              }

              if (drawingSettings.polyline && drawingSettings.polyline !== '0') {
                var polyline = new google.maps.Polyline({
                  path: locations,
                  strokeColor: drawingSettings.strokeColor,
                  strokeOpacity: drawingSettings.strokeOpacity,
                  strokeWeight: drawingSettings.strokeWeight,
                  geodesic: drawingSettings.geodesic
                });
                polyline.setMap(map.googleMap);
              }
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);


