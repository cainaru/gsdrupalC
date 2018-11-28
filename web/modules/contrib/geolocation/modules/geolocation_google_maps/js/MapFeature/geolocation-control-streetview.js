/**
 * @typedef {Object} ControlStreetViewSettings
 *
 * @property {String} enable
 * @property {String} position
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Streetview control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationStreetViewControl = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ControlStreetViewSettings} mapSettings.control_streetview - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.control_streetview !== 'undefined'
            && mapSettings.control_streetview.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            map.addLoadedCallback(function (map) {
              var options = {
                streetViewControlOptions: {
                  position: google.maps.ControlPosition[mapSettings.control_streetview.position]
                }
              };

              if (mapSettings.control_streetview.behavior === 'always') {
                options.streetViewControl = true;
              }
              else {
                options.streetViewControl = undefined;
              }

              map.googleMap.setOptions(options);
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
