/**
 * @file
 *   Javascript for the map geocoder widget.
 */

(function ($, Drupal) {
  'use strict';

  /**
   * GeolocationGoogleMapWidget element.
   *
   * @constructor
   * @augments {GeolocationMapWidgetBase}
   * @implements {GeolocationMapWidgetInterface}
   * @inheritDoc
   */
  function GeolocationGoogleMapWidget(widgetSettings) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.call(this, widgetSettings);

    return this;
  }
  GeolocationGoogleMapWidget.prototype = Object.create(Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype);
  GeolocationGoogleMapWidget.prototype.constructor = GeolocationGoogleMapWidget;
  GeolocationGoogleMapWidget.prototype.addMarker = function (location, delta) {
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
      label: (delta + 1).toString(),
      delta: delta,
      draggable: true
    });

    marker.addListener('dragend', function(e) {
      that.updateInput({lat: Number(e.latLng.lat()), lng: Number(e.latLng.lng())}, marker.delta);
    });

    marker.addListener('click', function() {
      that.removeInput(marker.delta);
      that.removeMarker(marker.delta);
      that.locationRemovedCallback(marker.delta);
    });

    return marker;
  };
  GeolocationGoogleMapWidget.prototype.updateMarker = function (location, delta) {
    Drupal.geolocation.widget.GeolocationMapWidgetBase.prototype.updateMarker.call(this, delta);

    /** @param {GoogleMarker} marker */
    var marker = this.getMarkerByDelta(delta);
    marker.setPosition(location);

    return marker;
  };
  Drupal.geolocation.widget.GeolocationGoogleMapWidget = GeolocationGoogleMapWidget;

  Drupal.geolocation.widget.addWidgetProvider('google', 'GeolocationGoogleMapWidget');

})(jQuery, Drupal);
