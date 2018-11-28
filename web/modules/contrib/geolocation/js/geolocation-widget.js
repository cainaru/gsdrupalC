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
  Drupal.behaviors.geolocationWidget = {
    attach: function (context, drupalSettings) {
      $('.geolocation-map-widget', context).each(function (index, item) {

        /** @type {GeolocationMapWidgetSettings} widgetSettings */
        var widgetSettings = {};
        var widgetWrapper = $(item);
        widgetSettings.wrapper = widgetWrapper;
        widgetSettings.id = widgetWrapper.attr('id');
        widgetSettings.type = widgetWrapper.data('widget-type');

        if (
          widgetWrapper.length === 0
          || widgetWrapper.hasClass('geolocation-widget-processed')
        ) {
          return;
        }

        widgetSettings.map = Drupal.geolocation.getMapById(widgetSettings.id + '-map');

        if (!widgetSettings.map) {
          console.error(widgetSettings, 'Could not find widget map.'); // eslint-disable-line no-console
          return;
        }

        if (typeof drupalSettings.geolocation.widgetSettings[widgetSettings.id] !== 'undefined') {
          widgetSettings = $.extend(drupalSettings.geolocation.widgetSettings[widgetSettings.id], widgetSettings);
        }

        var widget = Drupal.geolocation.widget.Factory(widgetSettings);

        if (!widget) {
          return;
        }

        var table = $('table.field-multiple-table', widgetWrapper);

        if (table.length) {
          var tableDrag = Drupal.tableDrag[table.attr('id')];

          if (tableDrag) {
            tableDrag.row.prototype.onSwap = function () {
              widget.map.removeMapMarkers();
              widget.loadMarkersFromInput();
            };
          }
        }

        widget.getAllInputs().each(function(index, input) {
          input = $(input);
          var delta = widget.getAllInputs().index(input);
          var longitude = input.find('input.geolocation-map-input-longitude');
          var latitude = input.find('input.geolocation-map-input-latitude');

          longitude.change(function() {
            var currentValue = $(this).val();
            if (currentValue === '') {
              widget.removeMarker(delta);
            }
            else if (latitude.val() !== '') {
              var location = {lat: Number(latitude.val()), lng: Number(currentValue)};
              var marker = widget.getMarkerByDelta(delta);
              if (marker) {
                widget.updateMarker(location, delta);
              }
              else {
                widget.addMarker(location, delta);
              }
              widget.map.fitMapToMarkers();
            }
          });

          latitude.change(function() {
            var currentValue = $(this).val();
            if (currentValue === '') {
              widget.removeMarker(delta);
            }
            else if (longitude.val() !== '') {
              var location = {lat: Number(currentValue), lng: Number(longitude.val())};
              var marker = widget.getMarkerByDelta(delta);
              if (marker) {
                widget.updateMarker(location, delta);
              }
              else {
                widget.addMarker(location, delta);
              }
              widget.map.fitMapToMarkers();
            }
          });
        });

        widgetWrapper.addClass('geolocation-widget-processed');

        widget.map.addReadyCallback(function (map) {
          widget.loadMarkersFromInput();
          widget.map.fitMapToMarkers();
        });

        // Add the click responders for setting the value.
        var singleClick;

        widget.map.addClickCallback(function (location) {

          // Create 500ms timeout to wait for double click.
          singleClick = setTimeout(function () {
            var delta = widget.getNextDelta();
            if (delta || delta === 0) {
              widget.addInput(location);
              widget.addMarker(location, delta);
              widget.locationAddedCallback(location, delta);
            }
          }, 500);

        });

        widget.map.addDoubleClickCallback(function () {
          clearTimeout(singleClick);
        });
      });
    }
  };

})(jQuery, Drupal);
