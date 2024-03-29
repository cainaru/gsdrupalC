/**
 * @file
 * Drupal Entity embed plugin.
 */

(function ($, Drupal, CKEDITOR) {

  "use strict";

  function getFocusedWidget(editor) {
    var widget = editor.widgets.focused;

    if (widget && widget.name === 'drupalentity') {
      return widget;
    }

    return null;
  }

  function linkCommandIntegrator(editor) {
    if (!editor.plugins.drupallink) {
      return;
    }

    editor.getCommand('drupalunlink').on('exec', function (evt) {
      var widget = getFocusedWidget(editor);

      if (!widget) {
        return;
      }

      widget.setData('link', null);

      this.refresh(editor, editor.elementPath());

      evt.cancel();
    });

    editor.getCommand('drupalunlink').on('refresh', function (evt) {
      var widget = getFocusedWidget(editor);

      if (!widget) {
        return;
      }

      this.setState(widget.data.link ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_DISABLED);

      evt.cancel();
    });
  }

  CKEDITOR.plugins.add('drupalentity', {
    // This plugin requires the Widgets System defined in the 'widget' plugin.
    requires: 'widget',

    // The plugin initialization logic goes inside this method.
    beforeInit: function (editor) {
      // Configure CKEditor DTD for custom drupal-entity element.
      // @see https://www.drupal.org/node/2448449#comment-9717735
      var dtd = CKEDITOR.dtd, tagName;
      dtd['drupal-entity'] = {'#': 1};
      // Register drupal-entity element as allowed child, in each tag that can
      // contain a div element.
      for (tagName in dtd) {
        if (dtd[tagName].div) {
          dtd[tagName]['drupal-entity'] = 1;
        }
      }
      dtd['a']['drupal-entity'] = 1;

      // drupallink has a hardcoded integration with drupalimage. Work around that, to reuse the same integration.

      var originalGetFocusedWidget = null;
      if (CKEDITOR.plugins.drupalimage) {
        originalGetFocusedWidget = CKEDITOR.plugins.drupalimage.getFocusedWidget;
      }
      else {
        CKEDITOR.plugins.drupalimage = {};
      }
      CKEDITOR.plugins.drupalimage.getFocusedWidget = function () {
        var ourFocusedWidget = getFocusedWidget(editor);
        if (ourFocusedWidget) {
          return ourFocusedWidget;
        }
        // If drupalimage is loaded, call that next, to not break its link command integration.
        if (originalGetFocusedWidget) {
          return originalGetFocusedWidget(editor);
        }
        return null;
      };

      // Generic command for adding/editing entities of all types.
      editor.addCommand('editdrupalentity', {
        allowedContent: 'drupal-entity[data-embed-button,data-entity-type,data-entity-uuid,data-entity-embed-display,data-entity-embed-display-settings,data-align,data-caption]',
        requiredContent: 'drupal-entity[data-embed-button,data-entity-type,data-entity-uuid,data-entity-embed-display,data-entity-embed-display-settings,data-align,data-caption]',
        modes: { wysiwyg : 1 },
        canUndo: true,
        exec: function (editor, data) {
          data = data || {};

          var existingElement = getSelectedEmbeddedEntity(editor);
          var existingWidget = (existingElement) ? editor.widgets.getByElement(existingElement, true) : null;

          var existingValues = {};

          // Host entity's langcode added in entity_embed_field_widget_form_alter().
          var hostEntityLangcode = document.getElementById(editor.name).getAttribute('data-entity_embed-host-entity-langcode');
          if (hostEntityLangcode) {
            existingValues['data-langcode'] = hostEntityLangcode;
          }

          if (existingWidget) {
            existingValues = existingWidget.data.attributes;
          }

          var embed_button_id = data.id ? data.id : existingValues['data-embed-button'];

          var dialogSettings = {
            dialogClass: 'entity-select-dialog',
            resizable: false
          };

          var saveCallback = function (values) {
            editor.fire('saveSnapshot');
            if (!existingElement) {
              var entityElement = editor.document.createElement('drupal-entity');
              var attributes = values.attributes;
              for (var key in attributes) {
                entityElement.setAttribute(key, attributes[key]);
              }
              editor.insertHtml(entityElement.getOuterHtml());
            }
            else {
              // Detach the behaviors that were attached when the entity content
              // was inserted.
              Drupal.runEmbedBehaviors('detach', existingElement.$);
              var hasCaption = false;
              if (values.attributes['data-caption']) {
                values.attributes['data-caption'] = CKEDITOR.tools.htmlDecodeAttr(values.attributes['data-caption']);
                hasCaption = true;
              }
              existingWidget.setData({ attributes: values.attributes, hasCaption: hasCaption });
            }
            editor.fire('saveSnapshot');
          };

          // Open the entity embed dialog for corresponding EmbedButton.
          Drupal.ckeditor.openDialog(editor, Drupal.url('entity-embed/dialog/' + editor.config.drupal.format + '/' + embed_button_id), existingValues, saveCallback, dialogSettings);
        }
      });

      // Register the entity embed widget.
      editor.widgets.add('drupalentity', {
        // Minimum HTML which is required by this widget to work.
        allowedContent: 'drupal-entity[data-entity-type,data-entity-uuid,data-entity-embed-display,data-entity-embed-display-settings,data-align,data-caption]',
        requiredContent: 'drupal-entity[data-entity-type,data-entity-uuid,data-entity-embed-display,data-entity-embed-display-settings,data-align,data-caption]',

        pathName: Drupal.t('Embedded entity'),

        editables: {
          caption: {
            selector: 'figcaption',
            allowedContent: 'a[!href]; em strong cite code br',
            pathName: Drupal.t('Caption'),
          }
        },

        upcast: function (element, data) {
          var attributes = element.attributes;
          if (attributes['data-entity-type'] === undefined || (attributes['data-entity-id'] === undefined && attributes['data-entity-uuid'] === undefined) || (attributes['data-view-mode'] === undefined && attributes['data-entity-embed-display'] === undefined)) {
            return;
          }
          data.attributes = CKEDITOR.tools.copy(attributes);
          data.hasCaption = data.attributes.hasOwnProperty('data-caption');
          data.link = null;
          if (element.parent.name === 'a') {
            data.link = CKEDITOR.tools.copy(element.parent.attributes);
            // @todo Ask CKEditor team how to get rid of this, see CKEDITOR.plugins.link.getLinkAttributes
            if (data.link['data-cke-saved-href']) {
              delete data.link['data-cke-saved-href'];
            }
          }
          return element;
        },

        init: function () {
          /** @type {CKEDITOR.dom.element} */
          var element = this.element;

          // See https://www.drupal.org/node/2544018.
          if (element.hasAttribute('data-embed-button')) {
            var buttonId = element.getAttribute('data-embed-button');
            if (editor.config.DrupalEntity_buttons[buttonId]) {
              var button = editor.config.DrupalEntity_buttons[buttonId];
              this.wrapper.data('cke-display-name', Drupal.t('Embedded @buttonLabel', {'@buttonLabel': button.label}));
            }
          }
        },

        destroy: function() {
          this._tearDownDynamicEditables();
        },

        data: function (event) {
          if (this._previewNeedsServersideUpdate()) {
            editor.fire('lockSnapshot');
            this._tearDownDynamicEditables();

            this._loadPreview(function (widget) {
              widget._setUpDynamicEditables();
              editor.fire('unlockSnapshot');
            });
          }
          else if (this._previewNeedsClientsideUpdate()) {
            this._performClientsideUpdate();
            editor.fire('saveSnapshot');
          }

          // Track the previous state, to allow for smarter decisions.
          this.oldData = CKEDITOR.tools.clone(this.data);
        },

        // Downcast the element.
        downcast: function () {
          var downcastElement = new CKEDITOR.htmlParser.element('drupal-entity', this.data.attributes);
          if (this.data.link) {
            var link = new CKEDITOR.htmlParser.element('a', this.data.link);
            link.add(downcastElement);
            downcastElement = link;
          }
          return downcastElement;
        },

        _setUpDynamicEditables() {
          // Now that the caption is available in the DOM, make it editable.
          if (this.initEditable('caption', this.definition.editables.caption)) {
            // And ensure that any changes made to it are persisted.
            var captionDomNode = this.editables.caption.$;
            var config = {characterData: true, attributes: false, childList: true, subtree: true};
            var widget = this;
            this.captionEditableMutationObserver = new MutationObserver(function () {
              var entityAttributes = CKEDITOR.tools.clone(widget.data.attributes);
              entityAttributes['data-caption'] = captionDomNode.innerHTML;
              widget.setData('attributes', entityAttributes);
            });
            this.captionEditableMutationObserver.observe(captionDomNode, config);
          }
        },

        _tearDownDynamicEditables() {
          if (this.captionEditableMutationObserver) {
            this.captionEditableMutationObserver.disconnect();
          }
        },

        _previewNeedsServersideUpdate() {
          // When the widget is first loading, it of course needs to still get a preview!
          if (!this.ready) {
            return true;
          }

          return this._hashData(this.oldData) !== this._hashData(this.data);
        },

        _previewNeedsClientsideUpdate() {
          if (this.data.hasCaption && this.editables.caption.$.innerHTML !== this.data.attributes['data-caption']) {
            return true;
          }

          return false;
        },

        _performClientsideUpdate() {
          if (this.data.hasCaption) {
            this.captionEditableMutationObserver.disconnect();
            this.editables.caption.$.innerHTML = this.data.attributes['data-caption'];
            var config = {characterData: true, attributes: false, childList: true, subtree: true};
            this.captionEditableMutationObserver.observe(this.editables.caption.$, config);
          }
        },

        _hashData(data) {
          var dataToHash = CKEDITOR.tools.clone(data);
          if (dataToHash.attributes['data-caption']) {
            delete dataToHash.attributes['data-caption'];
          }
          if (dataToHash.link && dataToHash.link.href) {
            delete dataToHash.link.href;
          }
          return JSON.stringify(dataToHash);
        },

        /**
         * Loads an entity embed preview, calls a callback to insert.
         *
         * Leverages {@link Drupal.Ajax}' ability to have scoped (per-instance)
         * command implementations to be able to call a callback.
         *
         * @todo Since previews use the downcasted representation, and `downcast()` relies 100% on `this.data`, and
         * `_hashData()` knows which changes are immaterial, we should be able to cache preview responses.
         *
         * @param {function} callback
         *   A callback function that will be called after the preview has loaded, and receives the widget instance.
         */
        _loadPreview(callback) {
          var widget = this;
          var previewLoaderAjax = Drupal.ajax({
            url: Drupal.url('embed/preview/' + editor.config.drupal.format + '?' + $.param({
              value: this.downcast().getOuterHtml()
            })),
            progress: { type: 'none' },
          });
          // Implement a scoped embed_insert AJAX command: calls the callback.
          previewLoaderAjax.commands.embed_insert = function(ajax, response, status) {
            // `ajax.element` must be set for `Drupal.AjaxCommands.prototype.embed_insert` to work.
            ajax.element = widget.element.$;
            Drupal.AjaxCommands.prototype.embed_insert(ajax, response, status);
            callback(widget);
            // Clean up after ourselves: delete the Drupal.ajax instance.
            Drupal.ajax.instances[ajax.instanceIndex] = null;
          };
          previewLoaderAjax.execute();
        }
      });

      editor.widgets.on('instanceCreated', function (event) {
        var widget = event.data;

        if (widget.name !== 'drupalentity') {
          return;
        }

        widget.on('edit', function (event) {
          event.cancel();
          // @see https://www.drupal.org/node/2544018
          if (isEditableEntityWidget(editor, event.sender.wrapper)) {
            editor.execCommand('editdrupalentity');
          }
        });
      });

      // Register the toolbar buttons.
      if (editor.ui.addButton) {
        for (var key in editor.config.DrupalEntity_buttons) {
          var button = editor.config.DrupalEntity_buttons[key];
          editor.ui.addButton(button.id, {
            label: button.label,
            data: button,
            allowedContent: 'drupal-entity[!data-entity-type,!data-entity-uuid,!data-entity-embed-display,!data-entity-embed-display-settings,!data-align,!data-caption,!data-embed-button,!data-langcode,!alt,!title]',
            click: function(editor) {
              editor.execCommand('editdrupalentity', this.data);
            },
            icon: button.image,
            modes: {wysiwyg: 1, source: 0}
          });
        }
      }

      // Register context menu items for editing widget.
      if (editor.contextMenu) {
        editor.addMenuGroup('drupalentity');

        for (var key in editor.config.DrupalEntity_buttons) {
          var button = editor.config.DrupalEntity_buttons[key];

          var label = Drupal.t('Edit @buttonLabel', { '@buttonLabel': button.label });

          editor.addMenuItem('drupalentity_' + button.id, {
            label: label,
            icon: button.image,
            command: 'editdrupalentity',
            group: 'drupalentity'
          });
        }

        editor.contextMenu.addListener(function(element) {
          if (isEditableEntityWidget(editor, element)) {
            var button_id = element.getFirst().getAttribute('data-embed-button');
            var returnData = {};
            returnData['drupalentity_' + button_id] = CKEDITOR.TRISTATE_OFF;
            return returnData;
          }
        });
      }
    },

    afterInit: function (editor) {
      linkCommandIntegrator(editor);
    }
  });

  /**
   * Get the surrounding drupalentity widget element.
   *
   * @param {CKEDITOR.editor} editor
   */
  function getSelectedEmbeddedEntity(editor) {
    var selection = editor.getSelection();
    var selectedElement = selection.getSelectedElement();
    if (isEditableEntityWidget(editor, selectedElement)) {
      return selectedElement;
    }

    return null;
  }

  /**
   * Checks if the given element is an editable drupalentity widget.
   *
   * @param {CKEDITOR.editor} editor
   * @param {CKEDITOR.htmlParser.element} element
   */
  function isEditableEntityWidget (editor, element) {
    var widget = editor.widgets.getByElement(element, true);
    if (!widget || widget.name !== 'drupalentity') {
      return false;
    }

    var button = $(element.$.firstChild).attr('data-embed-button');
    if (!button) {
      // If there was no data-embed-button attribute, not editable.
      return false;
    }

    // The button itself must be valid.
    return editor.config.DrupalEntity_buttons.hasOwnProperty(button);
  }

})(jQuery, Drupal, CKEDITOR);
