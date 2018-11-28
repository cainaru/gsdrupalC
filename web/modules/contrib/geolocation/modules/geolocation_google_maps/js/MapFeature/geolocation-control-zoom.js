/**
 * @typedef {Object} ControlZoomSettings
 *
 * @property {String} enable
 * @property {String} position
 * @property {String} style
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Zoom control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationZoomControl = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ControlZoomSettings} mapSettings.control_zoom - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.control_zoom !== 'undefined'
            && mapSettings.control_zoom.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            map.addLoadedCallback(function (map) {
              var options = {
                zoomControlOptions: {
                  position: google.maps.ControlPosition[mapSettings.control_zoom.position],
                  style: google.maps.ZoomControlStyle[mapSettings.control_zoom.style]
                }
              };

              if (mapSettings.control_zoom.behavior === 'always') {
                options.zoomControl = true;
              }
              else {
                options.zoomControl = undefined;
              }

              map.googleMap.setOptions(options);
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
