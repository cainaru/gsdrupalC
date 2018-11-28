/**
 * @file
 *   Javascript for the map geocoder widget.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * GeolocationLeafletMapWidget element.
   *
   * @constructor
   * @augments {GeolocationMapWidgetBase}
   * @implements {GeolocationMapWidgetInterface}
   * @inheritDoc
   */
  function GeolocationLeafletMapWidget(widgetSettings) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.call(this, widgetSettings);

    return this;
  }
  GeolocationLeafletMapWidget.prototype = Object.create(Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype);
  GeolocationLeafletMapWidget.prototype.constructor = GeolocationLeafletMapWidget;
  GeolocationLeafletMapWidget.prototype.addMarker = function (location, delta) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype.addMarker.call(this, location, delta);

    var that = this;

    // delta could legally be '0'.
    if (typeof delta === 'undefined') {
      delta = this.getNextDelta();
    }

    var marker = this.map.setMapMarker({
      position: location,
      title: Drupal.t('[@delta] Latitude: @latitude Longitude: @longitude', {
        '@delta': delta.toString(),
        '@latitude': location.lat,
        '@longitude': location.lng
      }),
      setMarker: true,
      draggable: true
    });
    marker.delta = delta;

    marker.on('dragend', function(e) {
      that.updateInput({lat: Number(e.latlng.lat), lng: Number(e.latlng.lng)}, marker.delta);
    });

    marker.on('click', function() {
      that.removeInput(marker.delta);
      that.removeMarker(marker.delta);
      that.locationRemovedCallback(marker.delta);
    });

    return marker;
  };
  GeolocationLeafletMapWidget.prototype.updateMarker = function (location, delta) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype.updateMarker.call(this, delta);

    var marker = this.getMarkerByDelta(delta);
    marker.setLatLng(location);

    return marker;
  };
  Drupal.geolocation.widget.GeolocationLeafletMapWidget = GeolocationLeafletMapWidget;

  Drupal.geolocation.widget.addWidgetProvider('geolocation_leaflet', 'GeolocationLeafletMapWidget');

})(jQuery, Drupal);
