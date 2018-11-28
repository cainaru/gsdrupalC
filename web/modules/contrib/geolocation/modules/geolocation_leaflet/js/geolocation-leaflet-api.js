/**
 * @file
 *   Javascript for leaflet integration.
 */

/**
 * @typedef {Object} L
 *
 * @property {Function} L
 * @property {function(Object[]):Object} L.featureGroup
 */

/**
 * @typedef {Object} LeafletMap
 *
 * @property {Function} tileLayer
 * @property {Function} addTo
 * @property {Function} setView
 * @property {Function} featureGroup
 * @property {Function} marker
 */

/**
 * @typedef {Object} LeafletMarker
 *
 * @property {Function} bindPopup
 */


(function ($, Drupal) {
  'use strict';

  /**
   * GeolocationLeafletMap element.
   *
   * @constructor
   * @augments {GeolocationMapBase}
   * @implements {GeolocationMapInterface}
   * @inheritDoc
   *
   * @prop {Object} settings.leaflet_settings - Leaflet specific settings.
   */
  function GeolocationLeafletMap(mapSettings) {
    this.type = 'leaflet';

    Drupal.geolocation.GeolocationMapBase.call(this, mapSettings);

    var defaultLeafletSettings = {
      zoom: 10,
      height: '400px',
      width: '100%'
    };

    // Add any missing settings.
    this.settings.leaflet_settings = $.extend(defaultLeafletSettings, this.settings.leaflet_settings);

    // Set the container size.
    this.container.css({
      height: this.settings.leaflet_settings.height,
      width: this.settings.leaflet_settings.width
    });

    var leafletMap = L.map(this.container.get(0), {
      center: [this.lat, this.lng],
      zoom: this.settings.leaflet_settings.zoom
    });

    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(leafletMap);

    /** @property {LeafletMap} leafletMap */
    this.leafletMap = leafletMap;

    this.addLoadedCallback(function(map) {
      map.leafletMap.on('click', function(e) {
        map.clickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
      });

      map.leafletMap.on('contextmenu', function(e) {
        map.contextClickCallback({lat: e.latlng.lat, lng: e.latlng.lng});
      });
    });

    this.loadedCallback(this, this.id);

    this.readyCallback();
  }
  GeolocationLeafletMap.prototype = Object.create(Drupal.geolocation.GeolocationMapBase.prototype);
  GeolocationLeafletMap.prototype.constructor = GeolocationLeafletMap;
  GeolocationLeafletMap.prototype.setMapMarker = function (markerSettings) {
    if (markerSettings.setMarker === false) {
      return;
    }

    if (typeof markerSettings.icon === 'string') {
      markerSettings.icon = L.icon({
        iconUrl: markerSettings.icon
      });
    }

    /** @param {LeafletMarker} */
    var currentMarker = L.marker([markerSettings.position.lat, markerSettings.position.lng], markerSettings).addTo(this.leafletMap);

    this.mapMarkers.push(currentMarker);

    return currentMarker;
  };
  GeolocationLeafletMap.prototype.removeMapMarker = function (marker) {
    Drupal.geolocation.GeolocationMapBase.prototype.removeMapMarker.call(this, marker);
    this.leafletMap.removeLayer(marker);
  };
  GeolocationLeafletMap.prototype.fitMapToMarkers = function (locations) {

    locations = locations || this.mapMarkers;
    if (locations.length === 0) {
      return;
    }

    var group = new L.featureGroup(locations);

    this.leafletMap.fitBounds(group.getBounds());
  };


  Drupal.geolocation.GeolocationLeafletMap = GeolocationLeafletMap;
  Drupal.geolocation.addMapProvider('leaflet', 'GeolocationLeafletMap');

})(jQuery, Drupal);
