/**
 * @file
 * Javascript for the Geolocation map formatter.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Find and display all maps.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches Geolocation Maps formatter functionality to relevant elements.
   */
  Drupal.behaviors.geolocationMap = {
    attach: function (context, drupalSettings) {
      $('.geolocation-map-wrapper', context).each(function (index, item) {
        var mapWrapper = $(item);
        var mapSettings = {};
        mapSettings.centreBehavior = 'fitlocations';
        mapSettings.id = mapWrapper.attr('id');
        mapSettings.wrapper = mapWrapper;

        if (
          mapWrapper.length === 0
          || mapWrapper.hasClass('geolocation-map-processed')
        ) {
          return;
        }

        mapSettings.lat = 0;
        mapSettings.lng = 0;

        if (
          mapWrapper.data('centre-lat')
          && mapWrapper.data('centre-lng')
        ) {
          mapSettings.lat = Number(mapWrapper.data('centre-lat'));
          mapSettings.lng = Number(mapWrapper.data('centre-lng'));
        }

        if (mapWrapper.data('map-type')) {
          mapSettings.type = mapWrapper.data('map-type');
        }

        if (mapWrapper.data('centre-behavior')) {
          mapSettings.centreBehavior = mapWrapper.data('centre-behavior');
        }

        if (typeof drupalSettings.geolocation === 'undefined') {
          console.err("Bailing out for lack of settings.");  // eslint-disable-line no-console
          return;
        }

        $.each(drupalSettings.geolocation.maps, function (mapId, currentSettings) {
          if (mapId === mapSettings.id) {
            mapSettings = $.extend(currentSettings, mapSettings);
          }
        });

        var map = Drupal.geolocation.Factory(mapSettings);

        if (!map) {
          console.error(mapSettings, 'Geolocation - Couldn\'t initialize map.'); // eslint-disable-line no-console
          return;
        }

        // Set the already processed flag.
        map.wrapper.addClass('geolocation-map-processed');

        map.addLoadedCallback(function (map) {
          $('.geolocation-map-controls > *', map.wrapper).each(function (index, control) {
            map.addControl(control);
          });

          map.removeMapMarkers();

          var locations = map.loadMarkersFromContainer();

          $.each(locations, function (index, location) {
            map.setMapMarker(location);
          });

          map.wrapper.find('.geolocation-location').hide();
          map.setCenterByBehavior();
        });

      });
    }
  };

})(jQuery, Drupal);
