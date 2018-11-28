/**
 * @typedef {Object} MarkerInfoBubbleSettings
 *
 * @property {bool} enable
 * @property {bool} closeButton
 * @property {bool} closeOther
 * @property {String} closeButtonSrc
 * @property {String} shadowStyle
 * @property {Number} padding
 * @property {Number} borderRadius
 * @property {Number} borderWidth
 * @property {String} borderColor
 * @property {String} backgroundColor
 * @property {Number} minWidth
 * @property {Number} maxWidth
 * @property {Number} minHeight
 * @property {Number} maxHeight
 */

/**
 * @typedef {Object} GoogleInfoBubble
 * @property {Function} open
 * @property {Function} close
 *
 * @property {bool} enable
 * @property {bool} closeButton
 * @property {bool} closeOther
 * @property {String} closeButtonSrc
 * @property {String} shadowStyle
 * @property {Number} padding
 * @property {Number} borderRadius
 * @property {Number} borderWidth
 * @property {String} borderColor
 * @property {String} backgroundColor
 * @property {Number} minWidth
 * @property {Number} maxWidth
 * @property {Number} minHeight
 * @property {Number} maxHeight
 */

/**
 * @property {GoogleInfoBubble} GeolocationGoogleMap.infoBubble
 * @property {function({}):GoogleInfoBubble} InfoBubble
 */

/* global InfoBubble */

(function ($, Drupal) {

  'use strict';

  /**
   * Marker InfoBubble.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMarkerInfoBubble = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {MarkerInfoBubbleSettings} mapSettings.marker_infobubble - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.marker_infobubble !== 'undefined'
            && mapSettings.marker_infobubble.enable
          ) {

            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            map.addMarkerAddedCallback(function (currentMarker) {
              var content = currentMarker.locationWrapper.find('.location-content').html();

              if (content.length < 1) {
                return;
              }

              google.maps.event.addListener(currentMarker, 'click', function () {

                if (typeof currentMarker.infoBubble === 'undefined') {
                  currentMarker.infoBubble = new InfoBubble({
                    map: map.googleMap,
                    content: content,
                    shadowStyle: mapSettings.marker_infobubble.shadowStyle,
                    padding: mapSettings.marker_infobubble.padding,
                    borderRadius: mapSettings.marker_infobubble.borderRadius,
                    borderWidth: mapSettings.marker_infobubble.borderWidth,
                    borderColor: mapSettings.marker_infobubble.borderColor,

                    arrowSize: 10,
                    arrowPosition: 30,
                    arrowStyle: 2,

                    hideCloseButton: !mapSettings.marker_infobubble.closeButton,
                    closeButtonSrc: mapSettings.marker_infobubble.closeButtonSrc,
                    backgroundClassName: 'infobubble',
                    backgroundColor: mapSettings.marker_infobubble.backgroundColor,
                    minWidth: mapSettings.marker_infobubble.minWidth,
                    maxWidth: mapSettings.marker_infobubble.maxWidth,
                    minHeight: mapSettings.marker_infobubble.minHeight,
                    maxHeight: mapSettings.marker_infobubble.maxHeight
                  });
                }

                if (mapSettings.marker_infobubble.closeOther) {
                  if (typeof map.infoBubble !== 'undefined') {
                    map.infoBubble.close();
                  }
                  map.infoBubble = currentMarker.infoBubble;
                }

                currentMarker.infoBubble.open(map.googleMap, currentMarker);
              });
            });
          }
        }
      );
    }
  };
})(jQuery, Drupal);
