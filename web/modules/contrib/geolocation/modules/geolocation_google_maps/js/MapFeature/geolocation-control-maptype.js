/**
 * @typedef {Object} ControlMapTypeSettings
 *
 * @property {String} enable
 * @property {String} position
 * @property {String} style
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Maptype control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMapTypeControl = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ControlMapTypeSettings} mapSettings.control_maptype - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.control_maptype !== 'undefined'
            && mapSettings.control_maptype.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            map.addLoadedCallback(function (map) {
              var options = {
                mapTypeControlOptions: {
                  position: google.maps.ControlPosition[mapSettings.control_maptype.position],
                  style: google.maps.MapTypeControlStyle[mapSettings.control_maptype.style]
                }
              };

              if (mapSettings.control_maptype.behavior === 'always') {
                options.mapTypeControl = true;
              }
              else {
                options.mapTypeControl = undefined;
              }

              map.googleMap.setOptions(options);
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
