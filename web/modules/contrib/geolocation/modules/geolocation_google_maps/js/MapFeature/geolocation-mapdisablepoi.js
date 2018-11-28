(function ($, Drupal) {

  'use strict';

  /**
   * Disable POI.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationContextPopup = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {ContextPopupSettings} mapSettings.map_disable_poi.enable - Enabled
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.map_disable_poi !== 'undefined'
            && mapSettings.map_disable_poi.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            if (map.wrapper.hasClass('geolocation-map-disable-poi-processed')) {
              return;
            }

            map.wrapper.addClass('geolocation-map-disable-poi-processed');

            map.addReadyCallback(function (map) {

              var styles = [];
              if (typeof map.googleMap.styles !== 'undefined') {
                styles = map.googleMap.styles;
              }
              styles = $.merge(styles, [{
                featureType: "poi",
                stylers: [
                  { visibility: "off" }
                ]
              }]);

              map.googleMap.setOptions({styles: styles});
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
