/**
 * @typedef {Object} ControlRecenterSettings
 *
 * @property {String} enable
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Recenter control.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationControlRecenter = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ControlRecenterSettings} mapSettings.control_recenter - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.control_recenter !== 'undefined'
            && mapSettings.control_recenter.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            map.addReadyCallback(function (map) {
              var recenterButton = $('.geolocation-map-control .recenter', map.wrapper);
              recenterButton.click(function (e) {
                map.setCenterByBehavior();
                e.preventDefault();
              });
            });
          }
        }
      );
    }
  };

})(jQuery, Drupal);
