/**
 * @file
 *   Javascript for the map geocoder widget.
 */

/**
 * @param {GeolocationMapWidgetInterface[]} Drupal.geolocation.widgets - List of widget instances
 * @param {Object} Drupal.geolocation.widget - Prototype container
 * @param {GeolocationMapWidgetSettings[]} drupalSettings.geolocation.widgetSettings - Additional widget settings
 */

/**
 * @name GeolocationMapWidgetSettings
 * @property {String} autoClientLocationMarker
 * @property {jQuery} wrapper
 * @property {String} id
 * @property {String} type
 * @property {GeolocationMapInterface} map
 * @property {String} fieldName
 * @property {String} cardinality
 */

/**
 * Callback for location found or set by widget.
 *
 * @callback geolocationGeocoderLocationCallback
 * @param {GeolocationCoordinates} location - Location.
 * @param {int} [delta] - Delta.
 */

/**
 * Callback for location unset by widget.
 *
 * @callback geolocationGeocoderClearCallback
 */

/**
 * Interface for classes that represent a color.
 *
 * @interface GeolocationMapWidgetInterface
 * @property {GeolocationMapWidgetSettings} settings
 * @property {jQuery} wrapper
 * @property {jQuery} container
 * @property {Object[]} mapMarkers
 * @property {geolocationGeocoderLocationCallback[]} locationAddedCallbacks
 * @property {geolocationGeocoderLocationCallback[]} locationModifiedCallbacks
 * @property {geolocationGeocoderLocationCallback[]} locationRemovedCallbacks
 * @property {geolocationGeocoderClearCallback[]} clearCallbacks
 */

/**
 * @function
 * @name GeolocationMapWidgetInterface#locationAddedCallback
 * @param {GeolocationCoordinates} location - first returned address
 *
 * Adds a callback that will be called when a location is set.
 * @function
 * @name GeolocationMapWidgetInterface#addLocationAddedCallback
 * @param {geolocationGeocoderLocationCallback} callback - The callback
 *
 * @function
 * @name GeolocationMapWidgetInterface#locationModifiedCallback
 * @param {GeolocationCoordinates} location - first returned address
 * @param {int} delta - Delta
 *
 * Adds a callback that will be called when a location is modified.
 * @function
 * @name GeolocationMapWidgetInterface#addLocationModifiedCallback
 * @param {geolocationGeocoderLocationCallback} callback - The callback
 *
 * @function
 * @name GeolocationMapWidgetInterface#locationRemovedCallback
 * @param {int} delta - Delta
 *
 * Adds a callback that will be called when a location is removed.
 * @function
 * @name GeolocationMapWidgetInterface#addLocationRemovedCallback
 * @param {geolocationGeocoderLocationCallback} callback - The callback
 *
 * Load markers from input and add to map.
 * @function
 * @name GeolocationMapWidgetInterface#loadMarkersFromInput
 *
 * Get map marker by delta.
 * @function
 * @name GeolocationMapWidgetInterface#getMarkerByDelta
 * @param {int} delta - Delta
 *
 * Get map marker by delta.
 * @function
 * @name GeolocationMapWidgetInterface#getNextDelta
 * @return {int}
 *
 * Get map input by delta.
 * @function
 * @name GeolocationMapWidgetInterface#getInputByDelta
 * @param {int} delta - Delta
 *
 * Add input.
 * @function
 * @name GeolocationMapWidgetInterface#addInput
 *
 * Add marker.
 * @function
 * @name GeolocationMapWidgetInterface#addMarker
 *
 * Update input.
 * @function
 * @name GeolocationMapWidgetInterface#updateInput
 *
 * Add input.
 * @function
 * @name GeolocationMapWidgetInterface#updateMarker
 *
 * Add input.
 * @function
 * @name GeolocationMapWidgetInterface#removeInput
 *
 * Add input.
 * @function
 * @name GeolocationMapWidgetInterface#removeMarker
 *
 */

(function ($, Drupal) {
  'use strict';

  /**
   * @namespace
   */

  Drupal.geolocation.widget = Drupal.geolocation.widget || {};

  Drupal.geolocation.widgets = Drupal.geolocation.widgets || [];

  /**
   * Geolocation map widget.
   *
   * @constructor
   * @abstract
   * @implements {GeolocationMapWidgetInterface}
   * @param {GeolocationMapWidgetSettings} widgetSettings - Setting to create map.
   */
  function GeolocationMapWidgetBase(widgetSettings) {

    this.locationAddedCallbacks = [];
    this.locationModifiedCallbacks = [];
    this.locationRemovedCallbacks = [];

    this.clearCallbacks = [];

    this.settings = widgetSettings || {};
    this.wrapper = widgetSettings.wrapper;
    this.fieldName = widgetSettings.fieldName;
    this.cardinality = widgetSettings.cardinality || 1;

    this.map = widgetSettings.map;

    return this;
  }

  GeolocationMapWidgetBase.prototype = {

    locationAddedCallback: function (location) {
      $.each(this.locationAddedCallbacks, function (index, callback) {
        callback(location);
      });
    },
    addLocationAddedCallback: function (callback) {
      this.locationAddedCallbacks.push(callback);
    },
    locationModifiedCallback: function (location, delta) {
      $.each(this.locationModifiedCallbacks, function (index, callback) {
        callback(location, delta);
      });
    },
    addLocationModifiedCallback: function (callback) {
      this.locationModifiedCallbacks.push(callback);
    },
    locationRemovedCallback: function (delta) {
      $.each(this.locationRemovedCallbacks, function (index, callback) {
        callback(delta);
      });
    },
    addLocationRemovedCallback: function (callback) {
      this.locationRemovedCallbacks.push(callback);
    },
    loadMarkersFromInput: function() {
      var that = this;
      $('.geolocation-widget-input', this.wrapper).each(function(delta, input) {
        input = $(input);
        var lng = input.find('input.geolocation-map-input-longitude').val();
        var lat = input.find('input.geolocation-map-input-latitude').val();

        if (lng && lat) {
          that.addMarker({lat: Number(lat), lng: Number(lng)}, delta);
        }
      });
    },
    getAllInputs: function() {
      return $('.geolocation-widget-input', this.wrapper);
    },
    getMarkerByDelta: function (delta) {
      delta = parseInt(delta) || 0;
      var marker = null;
      $.each(this.map.mapMarkers, function(index, currentMarker) {
        /** @param {GeolocationMapMarker} currentMarker */
        if (currentMarker.delta === delta) {
          marker = currentMarker;
          return false;
        }
      });
      return marker;
    },
    getInputByDelta: function (delta) {
      delta = parseInt(delta) || 0;
      var input = this.getAllInputs().eq(delta);
      if (input.length) {
        return input;
      }
    },
    getNextDelta: function() {
      var inputs = this.getAllInputs();
      var lastDelta = inputs.length - 1;
      var input = inputs.eq(lastDelta);
      if (
        input.find('input.geolocation-map-input-longitude').val()
        || input.find('input.geolocation-map-input-latitude').val()
        && (lastDelta + 1) === this.cardinality
      ) {
        alert(Drupal.t('Maximum number of entries reached.'));
        return false;
      }
      else {
        // TODO: multiple entries at the bottom might be empty.
        return lastDelta;
      }
    },
    addMarker: function (location, delta) {},
    addInput: function (location, delta) {
      delta = delta || this.getNextDelta();
      if (typeof delta === 'undefined') {
        return;
      }
      var input = this.getInputByDelta(delta);
      if (input) {
        input.find('input.geolocation-map-input-longitude').val(location.lng);
        input.find('input.geolocation-map-input-latitude').val(location.lat);
      }

      var button = this.wrapper.find('[name="' + this.fieldName + '_add_more"]');
      if (button.length) {
        button.trigger("mousedown");
      }

      return delta;
    },
    updateMarker: function (location, delta) {},
    updateInput: function (location, delta) {
      var input = this.getInputByDelta(delta);
      input.find('input.geolocation-map-input-longitude').val(location.lng);
      input.find('input.geolocation-map-input-latitude').val(location.lat);
    },
    removeMarker: function (delta) {
      var marker = this.getMarkerByDelta(delta);

      if (marker) {
        this.map.removeMapMarker(marker);
      }
    },
    removeInput: function (delta) {
      var input = this.getInputByDelta(delta);
      input.find('input.geolocation-map-input-longitude').val('');
      input.find('input.geolocation-map-input-latitude').val('');
    }
  };

  Drupal.geolocation.widget.GeolocationMapWidgetBase = GeolocationMapWidgetBase;

  /**
   * Factory creating map instances.
   *
   * @constructor
   * @param {GeolocationMapWidgetSettings} widgetSettings - The widget settings.
   * @param {Boolean} [reset] Force creation of new widget.
   * @return {GeolocationMapWidgetInterface|boolean} - New or updated widget.
   */
  function Factory(widgetSettings, reset) {
    reset = reset || false;
    widgetSettings.type = widgetSettings.type || 'google';

    var widget = null;

    /**
     * Previously stored map.
     * @type {boolean|GeolocationMapInterface}
     */
    var existingWidget = false;

    $.each(Drupal.geolocation.widgets, function (index, widget) {
      if (widget.id === widgetSettings.id) {
        existingWidget = Drupal.geolocation.widgets[index];
      }
    });

    if (reset === true || !existingWidget) {
      if (typeof Drupal.geolocation.widget[Drupal.geolocation.widget.widgetProviders[widgetSettings.type]] !== 'undefined') {
        var widgetProvider = Drupal.geolocation.widget[Drupal.geolocation.widget.widgetProviders[widgetSettings.type]];
        widget = new widgetProvider(widgetSettings);
        Drupal.geolocation.widgets.push(this);
      }
    }
    else {
      widget = existingWidget;
    }

    if (!widget) {
      console.error(widgetSettings, "Widget could not be initialzed"); // eslint-disable-line no-console
      return false;
    }

    return widget;
  }

  Drupal.geolocation.widget.Factory = Factory;

  /**
   * @type {Object[]}
   */
  Drupal.geolocation.widget.widgetProviders = {};

  Drupal.geolocation.widget.addWidgetProvider = function (type, name) {
    Drupal.geolocation.widget.widgetProviders[type] = name;
  };

})(jQuery, Drupal);
