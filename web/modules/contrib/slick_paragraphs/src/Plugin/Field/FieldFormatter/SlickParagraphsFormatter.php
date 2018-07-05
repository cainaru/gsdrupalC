<?php

namespace Drupal\slick_paragraphs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\slick_media\Plugin\Field\FieldFormatter\SlickMediaFormatter;

/**
 * Plugin implementation of the 'Slick Paragraphs Media' formatter.
 */
class SlickParagraphsFormatter extends SlickMediaFormatter {

  /**
   * Overrides the scope for the form elements.
   */
  public function getScopedFormElements() {
    $admin       = $this->admin();
    $field       = $this->fieldDefinition;
    $entity_type = $field->getTargetEntityTypeId();
    $target_type = $this->getFieldSetting('target_type');
    $views_ui    = $this->getFieldSetting('handler') == 'default';
    $bundles     = $views_ui ? [] : $this->getFieldSetting('handler_settings')['target_bundles'];
    $media       = $admin->getFieldOptions($bundles, ['entity_reference'], $target_type, 'media');
    $stages      = $admin->getFieldOptions($bundles, ['image', 'video_embed_field'], $target_type);

    return [
      'images'   => $stages,
      'overlays' => $stages + $media,
    ] + parent::getScopedFormElements();
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();

    // Excludes host, prevents complication with multiple nested paragraphs.
    $paragraph = $storage->getTargetEntityTypeId() === 'paragraph';
    return $paragraph && $storage->isMultiple() && $storage->getSetting('target_type') === 'paragraph';
  }

}
