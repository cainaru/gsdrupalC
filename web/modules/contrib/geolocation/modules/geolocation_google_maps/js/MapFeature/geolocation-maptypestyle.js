/**
 * @typedef {Object} MapTypeStyleSettings
 *
 * @property {String} enable
 * @property {String} style
 */

(function ($, Drupal) {

  'use strict';

  /**
   * MapTypeStyleSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches MapTypeStyleSettings functionality to relevant elements.
   */
  Drupal.behaviors.geolocationGoogleMapTypeStyle = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {MapTypeStyleSettings} mapSettings.map_type_style - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.map_type_style !== 'undefined'
            && mapSettings.map_type_style.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            if (map.wrapper.hasClass('geolocation-map-style-type-processed')) {
              return;
            }

            map.wrapper.addClass('geolocation-map-style-type-processed');

            map.addReadyCallback(function (map) {

              var styles = [];
              if (typeof map.googleMap.styles !== 'undefined') {
                styles = map.googleMap.styles;
              }
              styles = $.merge(mapSettings.map_type_style.style, styles);

              map.googleMap.setOptions({styles: styles});
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
