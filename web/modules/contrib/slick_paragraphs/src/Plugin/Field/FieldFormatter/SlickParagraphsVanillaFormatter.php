<?php

namespace Drupal\slick_paragraphs\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\slick\Plugin\Field\FieldFormatter\SlickEntityFormatterBase;

/**
 * Plugin implementation of the 'Slick Paragraphs Vanilla' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_paragraphs_vanilla",
 *   label = @Translation("Slick Paragraphs Vanilla"),
 *   description = @Translation("Display the vanilla paragraph as a Slick carousel."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class SlickParagraphsVanillaFormatter extends SlickEntityFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();

    return $storage->isMultiple() && $storage->getSetting('target_type') === 'paragraph';
  }

}
