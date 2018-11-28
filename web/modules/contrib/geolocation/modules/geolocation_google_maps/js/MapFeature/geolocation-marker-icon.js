/**
 * @typedef {Object} MarkerIconSettings
 *
 * @property {String} enable
 * @property {String} markerIconPath
 * @property {Array} anchor
 * @property {Number} anchor.x
 * @property {Number} anchor.y
 * @property {Array} labelOrigin
 * @property {Number} labelOrigin.x
 * @property {Number} labelOrigin.y
 * @property {Array} origin
 * @property {Number} origin.x
 * @property {Number} origin.y
 * @property {Array} size
 * @property {Number} size.width
 * @property {Number} size.height
 * @property {Array} scaledSize
 * @property {Number} scaledSize.width
 * @property {Number} scaledSize.height
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Google MarkerIcon.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMarkerIcon = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {MarkerIconSettings} mapSettings.marker_icon - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.marker_icon !== 'undefined'
            && mapSettings.marker_icon.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            map.addMarkerAddedCallback(function (currentMarker) {
              var currentIcon = currentMarker.getIcon();

              var anchorX = currentMarker.locationWrapper.data('marker-icon-anchor-x') || mapSettings.marker_icon.anchor.x;
              var anchorY = currentMarker.locationWrapper.data('marker-icon-anchor-y') || mapSettings.marker_icon.anchor.y;
              var labelOriginX = currentMarker.locationWrapper.data('marker-icon-label-origin-x') || mapSettings.marker_icon.labelOrigin.y;
              var labelOriginY = currentMarker.locationWrapper.data('marker-icon-label-origin-y') || mapSettings.marker_icon.labelOrigin.y;
              var originX = currentMarker.locationWrapper.data('marker-icon-origin-x') || mapSettings.marker_icon.origin.y;
              var originY = currentMarker.locationWrapper.data('marker-icon-origin-y') || mapSettings.marker_icon.origin.y;
              var sizeWidth = currentMarker.locationWrapper.data('marker-icon-size-width') || mapSettings.marker_icon.size.width;
              var sizeHeight = currentMarker.locationWrapper.data('marker-icon-size-height') || mapSettings.marker_icon.size.height;
              var scaledSizeWidth = currentMarker.locationWrapper.data('marker-icon-scaled-size-width') || mapSettings.marker_icon.scaledSize.width;
              var scaledSizeHeight = currentMarker.locationWrapper.data('marker-icon-scaled-size-height') || mapSettings.marker_icon.scaledSize.height;

              var newIcon = {
                anchor: new google.maps.Point(anchorX, anchorY),
                labelOrigin: new google.maps.Point(labelOriginX, labelOriginY),
                origin: new google.maps.Point(originX, originY)
              };

              if (sizeWidth && sizeHeight) {
                newIcon.size = new google.maps.Size(sizeWidth, sizeHeight);
              }

              if (scaledSizeWidth && scaledSizeHeight) {
                newIcon.scaledSize = new google.maps.Size(scaledSizeWidth, scaledSizeHeight);
              }


              if (typeof currentIcon === 'undefined') {
                if (typeof mapSettings.marker_icon.markerIconPath === 'string') {
                  newIcon.url = mapSettings.marker_icon.markerIconPath;
                }
                else {
                  return;
                }
              }
              else if (typeof currentIcon === 'string') {
                newIcon.url = currentIcon;
              }
              else if (typeof currentIcon.url === 'string') {
                newIcon.url = currentIcon.url;
              }

              currentMarker.setIcon(newIcon);
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
