<?php

namespace Drupal\color_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the color_field spectrum widget.
 *
 * @FieldWidget(
 *   id = "color_field_widget_spectrum",
 *   module = "color_field",
 *   label = @Translation("Color spectrum"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class ColorFieldWidgetSpectrum extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'show_input' => FALSE,
      'show_palette' => FALSE,
      'palette' => '',
      'show_palette_only' => FALSE,
      'show_buttons' => FALSE,
      'allow_empty' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];

    $element['show_input'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show Input'),
      '#default_value' => $this->getSetting('show_input'),
      '#description' => $this->t('Allow free form typing.'),
    );
    $element['show_palette'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show Palette'),
      '#default_value' => $this->getSetting('show_palette'),
      '#description' => $this->t('Show or hide Palette in Spectrum Widget'),
    );
    $element['palette'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Color Palette'),
      '#default_value' => $this->getSetting('palette'),
      '#description' => $this->t('Selectable color palette to accompany the Spectrum Widget'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][show_palette]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $element['show_palette_only'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show Palette Only'),
      '#default_value' => $this->getSetting('show_palette_only'),
      '#description' => $this->t('Only show the palette in Spectrum Widget and nothing else'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings][show_palette]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $element['show_buttons'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show Buttons'),
      '#default_value' => $this->getSetting('show_buttons'),
      '#description' => $this->t('Add Cancel/Confirm Button.'),
    );
    $element['allow_empty'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Allow Empty'),
      '#default_value' => $this->getSetting('allow_empty'),
      '#description' => $this->t('Allow empty value.'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // We are nesting some sub-elements inside the parent, so we need a wrapper.
    // We also need to add another #title attribute at the top level for ease in
    // identifying this item in error messages. We do not want to display this
    // title because the actual title display is handled at a higher level by
    // the Field module.
    $element['#theme_wrappers'] = array('color_field_widget_spectrum');

    $element['#attached']['library'][] = 'color_field/color-field-widget-spectrum';

    // Set Drupal settings.
    $settings = $this->getSettings();
     // Compare with default settings make sure they are the same datatype.
     
    $defaults = self::defaultSettings();
    foreach ($settings as $key => $value) {
      if (is_bool($defaults[$key])) {
        $settings[$key] = boolval($value);
      }
    }

    // Parsing Palette data so that it works with spectrum colorpicker.
    // This will create a multidimensional array of hex values.
    if (!empty($settings['palette'])) {
      // Remove any whitespace.
      $settings['palette'] =  str_replace(' ', '', $settings['palette']);

      // Parse each row first and reset the palette.
      $rows = explode("\n", $settings['palette']);
      $settings['palette'] = array();

      foreach ($rows as $row) {
        // Next explode each row into an array of values.
        $settings['palette'][] = $columns = explode(',', $row);
      }
    }

    $settings['show_alpha'] = $this->getFieldSetting('opacity');
    $element['#attached']['drupalSettings']['color_field']['color_field_widget_spectrum'] = $settings;

    // Prepare color.
    $color = NULL;
    if (isset($items[$delta]->color)) {
      $color = $items[$delta]->color;
      if (substr($color, 0, 1) !== '#') {
        $color = '#' . $color;
      }
    }

    $element['color'] = array(
      '#type' => 'textfield',
      '#maxlength' => 7,
      '#size' => 7,
      '#required' => $element['#required'],
      '#default_value' => $color,
      '#attributes' => array('class' => array('js-color-field-widget-spectrum__color')),
    );

    if ($this->getFieldSetting('opacity')) {
      $element['opacity'] = array(
        '#type' => 'number',
        '#min' => 0,
        '#max' => 1,
        '#step' => 0.01,
        '#required' => $element['#required'],
        '#default_value' => isset($items[$delta]->opacity) ? $items[$delta]->opacity : NULL,
        '#attributes' => array('class' => array('js-color-field-widget-spectrum__opacity', 'visually-hidden')),
      );
    }

    return $element;
  }

}
