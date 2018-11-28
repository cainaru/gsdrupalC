/**
 * @typedef {Object} ContextPopupSettings
 *
 * @property {String} enable
 * @property {String} content
 */

(function ($, Drupal) {

  'use strict';

  /**
   * ContextPopupSettings.
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
         * @param {ContextPopupSettings} mapSettings.context_popup - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.context_popup !== 'undefined'
            && mapSettings.context_popup.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            if (map.wrapper.hasClass('geolocation-map-contextpopup-processed')) {
              return;
            }

            map.wrapper.addClass('geolocation-map-contextpopup-processed');

            map.addReadyCallback(function (map) {

              /** @param {jQuery} */
              var contextContainer = jQuery('<div class="geolocation-context-popup"></div>');
              contextContainer.hide();
              contextContainer.appendTo(map.container);

              /**
               * Context popup handling.
               *
               * @param {GeolocationCoordinates} location - Coordinates.
               * @return {GoogleMapPoint} - Pixel offset against top left corner of map container.
               */
              map.googleMap.fromLocationToPixel = function (location) {
                var numTiles = 1 << map.googleMap.getZoom();
                var projection = map.googleMap.getProjection();
                var worldCoordinate = projection.fromLatLngToPoint(new google.maps.LatLng(location.lat, location.lng));
                var pixelCoordinate = new google.maps.Point(
                  worldCoordinate.x * numTiles,
                  worldCoordinate.y * numTiles);

                var topLeft = new google.maps.LatLng(
                  map.googleMap.getBounds().getNorthEast().lat(),
                  map.googleMap.getBounds().getSouthWest().lng()
                );

                var topLeftWorldCoordinate = projection.fromLatLngToPoint(topLeft);
                var topLeftPixelCoordinate = new google.maps.Point(
                  topLeftWorldCoordinate.x * numTiles,
                  topLeftWorldCoordinate.y * numTiles);

                return new google.maps.Point(
                  pixelCoordinate.x - topLeftPixelCoordinate.x,
                  pixelCoordinate.y - topLeftPixelCoordinate.y
                );
              };

              map.addClickCallback(function (location) {
                if (typeof contextContainer !== 'undefined') {
                  contextContainer.hide();
                }
              });

              map.addContextClickCallback(function (location) {
                var content = Drupal.formatString(mapSettings.context_popup.content, {
                  '@lat': location.lat,
                  '@lng': location.lng
                });

                contextContainer.html(content);

                if (content.length > 0) {
                  var pos = map.googleMap.fromLocationToPixel(location);
                  contextContainer.show();
                  contextContainer.css('left', pos.x);
                  contextContainer.css('top', pos.y);
                }
              });
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
