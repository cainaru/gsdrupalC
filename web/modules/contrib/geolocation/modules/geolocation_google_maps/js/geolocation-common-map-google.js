(function ($, Drupal) {
  'use strict';

  /**
   * Dynamic map handling aka "AirBnB mode".
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches common map style functionality to relevant elements.
   */
  Drupal.behaviors.geolocationCommonMapGoogle = {
    attach: function (context, drupalSettings) {
      $.each(
        drupalSettings.geolocation.commonMap,

        /**
         * @param {String} mapId - ID of current map
         * @param {CommonMapSettings} commonMapSettings - settings for current map
         */
        function (mapId, commonMapSettings) {
          if (
            typeof commonMapSettings.dynamic_map !== 'undefined'
            && commonMapSettings.dynamic_map.enable
          ) {
            var map = Drupal.geolocation.getMapById(mapId);

            if (!map) {
              return;
            }

            /**
             * Update the view depending on dynamic map settings and capability.
             *
             * One of several states might occur now. Possible state depends on whether:
             * - view using AJAX is enabled
             * - map view is the containing (page) view or an attachment
             * - the exposed form is present and contains the boundary filter
             * - map settings are consistent
             *
             * Given these factors, map boundary changes can be handled in one of three ways:
             * - trigger the views AJAX "RefreshView" command
             * - trigger the exposed form causing a regular POST reload
             * - fully reload the website
             *
             * These possibilities are ordered by UX preference.
             */
            if (
              map.container.length
              && !map.container.hasClass('geolocation-common-map-google-processed')
            ) {
              map.container.addClass('geolocation-common-map-google-processed');

              map.addLoadedCallback(function (map) {
                var geolocationMapIdleTimer;
                map.googleMap.addListener('bounds_changed', function () {
                  clearTimeout(geolocationMapIdleTimer);

                  geolocationMapIdleTimer = setTimeout(
                    function () {
                      // Make sure to load current form DOM element, which will change after every AJAX operation.
                      var view = $('.view-id-' + commonMapSettings.dynamic_map.update_view_id + '.view-display-id-' + commonMapSettings.dynamic_map.update_view_display_id);
                      var exposedForm = $('form#views-exposed-form-' + commonMapSettings.dynamic_map.update_view_id.replace(/_/g, '-') + '-' + commonMapSettings.dynamic_map.update_view_display_id.replace(/_/g, '-'));

                      if (!exposedForm.length) {
                        return;
                      }

                      if (typeof commonMapSettings.dynamic_map.boundary_filter === 'undefined') {
                        return;
                      }

                      var currentBounds = map.googleMap.getBounds();

                      // Extract the view DOM ID from the view classes.
                      var matches = /(js-view-dom-id-\w+)/.exec(view.attr('class'));
                      var currentViewId = matches[1].replace('js-view-dom-id-', 'views_dom_id:');

                      var viewInstance = Drupal.views.instances[currentViewId];
                      var ajaxSettings = $.extend(true, {}, viewInstance.element_settings);
                      ajaxSettings.progress.type = 'none';

                      // Add form values.
                      jQuery.each( exposedForm.serializeArray(), function( index, field ) {
                        var add = {};
                        add[field.name] = field.value;
                        ajaxSettings.submit = $.extend(ajaxSettings.submit, add);
                      });

                      // Add bounds.
                      var bound_parameters = {};
                      bound_parameters[commonMapSettings['dynamic_map']['parameter_identifier'] + '[lat_north_east]'] = currentBounds.getNorthEast().lat();
                      bound_parameters[commonMapSettings['dynamic_map']['parameter_identifier'] + '[lng_north_east]'] = currentBounds.getNorthEast().lng();
                      bound_parameters[commonMapSettings['dynamic_map']['parameter_identifier'] + '[lat_south_west]'] = currentBounds.getSouthWest().lat();
                      bound_parameters[commonMapSettings['dynamic_map']['parameter_identifier'] + '[lng_south_west]'] = currentBounds.getSouthWest().lng();
                      // Trigger geolocation bounds specific behavior.
                      bound_parameters['geolocation_common_map_google_bounds_changed'] = true;
                      ajaxSettings.submit = $.extend(
                        ajaxSettings.submit,
                        bound_parameters
                      );

                      Drupal.ajax(ajaxSettings).execute();
                    },
                    commonMapSettings.dynamic_map.views_refresh_delay
                  );
                });
              });
            }
          }
        });
    }
  };

})(jQuery, Drupal);
