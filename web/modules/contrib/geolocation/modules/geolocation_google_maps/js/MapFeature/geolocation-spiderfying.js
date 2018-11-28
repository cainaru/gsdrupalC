/**
 * @typedef {Object} OverlappingMarkerSpiderfierInterface
 *
 * @property {function} addMarker
 * @property {string} markerStatus.SPIDERFIED
 * @property {string} markerStatus.UNSPIDERFIED
 * @property {string} markerStatus.SPIDERFIABLE
 * @property {string} markerStatus.UNSPIDERFIABLE
 */

/**
 * @typedef {Object} SpiderfyingSettings
 *
 * @property {String} enable
 * @property {String} spiderfiable_marker_path
 */

(function ($, Drupal) {

  'use strict';

  /**
   * SpiderfyingSettings.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationSpiderfying = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.maps,

        /**
         * @param {String} mapId - ID of current map
         * @param {Object} mapSettings - settings for current map
         * @param {SpiderfyingSettings} mapSettings.spiderfying - settings for current map
         */
        function (mapId, mapSettings) {
          if (
            typeof mapSettings.spiderfying !== 'undefined'
            && mapSettings.spiderfying.enable
            && typeof OverlappingMarkerSpiderfier !== 'undefined'
          ) {

            /* global OverlappingMarkerSpiderfier */

            var map = Drupal.geolocation.getMapById(mapId);

            var oms = null;

            /**
             * @param {OverlappingMarkerSpiderfierInterface} OverlappingMarkerSpiderfier
             */
            oms = new OverlappingMarkerSpiderfier(map.googleMap, {
              markersWontMove: true,
              keepSpiderfied: true
            });

            if (oms) {
              map.addMarkerAddedCallback(function (marker) {
                google.maps.event.addListener(marker, 'spider_format', function (status) {

                  /**
                   * @param {Object} marker.originalIcon
                   */
                  if (typeof marker.originalIcon === 'undefined') {
                    var originalIcon = marker.getIcon();

                    if (typeof originalIcon === 'undefined') {
                      marker.orginalIcon = '';
                    }
                    else if (
                      typeof originalIcon !== 'undefined'
                      && originalIcon !== null
                      && typeof originalIcon.url !== 'undefined'
                      && originalIcon.url === mapSettings.spiderfying.spiderfiable_marker_path
                    ) {
                      // Do nothing.
                    }
                    else {
                      marker.orginalIcon = originalIcon;
                    }
                  }

                  var icon = null;
                  var iconSize = new google.maps.Size(23, 32);
                  switch (status) {
                    case OverlappingMarkerSpiderfier.markerStatus.SPIDERFIABLE:
                      icon = {
                        url: mapSettings.spiderfying.spiderfiable_marker_path,
                        size: iconSize,
                        scaledSize: iconSize
                      };
                      break;

                    case OverlappingMarkerSpiderfier.markerStatus.SPIDERFIED:
                      icon = marker.orginalIcon;
                      break;

                    case OverlappingMarkerSpiderfier.markerStatus.UNSPIDERFIABLE:
                      icon = marker.orginalIcon;
                      break;

                    case OverlappingMarkerSpiderfier.markerStatus.UNSPIDERFIED:
                      icon = marker.orginalIcon;
                      break;
                  }
                  marker.setIcon(icon);
                });

                $.each(
                  marker.listeners,
                  function (index, listener) {
                    if (listener.e === 'click') {
                      google.maps.event.removeListener(listener.listener);
                      marker.addListener('spider_click', listener.f);
                    }
                  }
                );
                oms.addMarker(marker);
              });
            }
          }
        }
      );
    }
  };

})(jQuery, Drupal);
