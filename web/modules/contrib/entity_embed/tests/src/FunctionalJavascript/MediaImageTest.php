<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\Component\Utility\Html;
use Drupal\editor\Entity\Editor;
use Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\MediaImageDecorator;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Test Media Image specific functionality.
 *
 * @group entity_embed
 */
class MediaImageTest extends EntityEmbedTestBase {

  /**
   * The user to use during testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * A file entity to reference on the media's image field.
   *
   * @var \Drupal\file\Entity\File
   */
  protected $file;

  /**
   * A media entity used for embedding.
   *
   * @var \Drupal\media\Entity\Media
   */
  protected $media;

  /**
   * A host entity with ckeditor body field.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $host;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_embed_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'use text format full_html',
      'administer nodes',
      'edit any article content',
    ]);

    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    /** @var \Drupal\file\FileInterface $file */
    $this->file = File::create([
      'uri' => 'public://example.jpg',
      'uid' => $this->adminUser->id(),
    ]);
    $this->file->save();

    $this->createNode([
      'type' => 'article',
      'title' => 'Red-lipped batfish',
    ]);

    $this->media = Media::create([
      'bundle' => 'image',
      'name' => 'Screaming hairy armadillo',
      'field_media_image' => [
        [
          'target_id' => $this->file->id(),
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $this->media->save();

    $this->host = $this->createNode([
      'type' => 'article',
      'title' => 'Animals with strange names',
      'body' => [
        'value' => '',
        'format' => 'full_html',
      ],
    ]);
  }

  /**
   * Tests alt and title overriding for embedded images.
   */
  public function testAltAndTitle() {

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();

    $this->assignNameToCkeditorIframe();

    $this->pressEditorButton('test_node');
    $this->assertSession()->waitForId('drupal-modal');

    // Test that node embed doesn't display alt and title fields.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Red-lipped batfish (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert that the review step displays the selected entity with the label.
    $text = $form->getText();
    $this->assertContains('Red-lipped batfish', $text);

    $select = $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]');

    $select->setValue('view_mode:node.full');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The view_mode:node.full display shouldn't have alt and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $select = $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]');

    $select->setValue('view_mode:node.teaser');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The view_mode:node.teaser display shouldn't have alt and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    // Close the dialog.
    $this->assertSession()->elementExists('css', '.ui-dialog-titlebar-close')->press();

    // Now test with media.
    $this->pressEditorButton('test_media_entity_embed');
    $this->assertSession()->waitForId('drupal-modal');

    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Screaming hairy armadillo (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert that the review step displays the selected entity with the label.
    $text = $form->getText();
    $this->assertContains('Screaming hairy armadillo', $text);

    $select = $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]');

    $select->setValue('entity_reference:entity_reference_entity_id');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The entity_reference:entity_reference_entity_id display shouldn't have
    // alt and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $select->setValue('entity_reference:entity_reference_label');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The entity_reference:entity_reference_label display shouldn't have alt
    // and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    // Test the entity embed display that ships with core media.
    $select->setValue('entity_reference:media_thumbnail');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $alt = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertEquals($this->media->field_media_image->alt, $alt->getAttribute('placeholder'));
    $title = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]');
    $this->assertEquals($this->media->field_media_image->title, $title->getAttribute('placeholder'));

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("default alt", $img->getAttribute('alt'));
    $this->assertEquals("default title", $img->getAttribute('title'));

    $this->reopenDialog();

    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')
      ->setValue('Satanic leaf-tailed gecko alt');
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')
      ->setValue('Satanic leaf-tailed gecko title');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("Satanic leaf-tailed gecko alt", $img->getAttribute('alt'));
    $this->assertEquals("Satanic leaf-tailed gecko title", $img->getAttribute('title'));

    $this->reopenDialog();

    // Test a view mode that displays thumbnail field.
    $select->setValue('view_mode:media.thumb');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $alt = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $alt->getValue());
    $title = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]');
    $this->assertEquals('Satanic leaf-tailed gecko title', $title->getValue());

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $img->getAttribute('alt'));
    $this->assertEquals('Satanic leaf-tailed gecko title', $img->getAttribute('title'));

    $this->reopenDialog();

    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')
      ->setValue('Goblin shark alt');
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')
      ->setValue('Goblin shark title');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("Goblin shark alt", $img->getAttribute('alt'));
    $this->assertEquals("Goblin shark title", $img->getAttribute('title'));

    $this->reopenDialog();

    // Test a view mode that displays the media's image field.
    $select->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Test that the view_mode:media.embed display has alt and title fields,
    // and that the default values match the values on the media's
    // source image field.
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $alt = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertEquals("Goblin shark alt", $alt->getValue());
    $title = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]');
    $this->assertEquals("Goblin shark title", $title->getValue());

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("Goblin shark alt", $img->getAttribute('alt'));
    $this->assertEquals("Goblin shark title", $img->getAttribute('title'));

    $this->reopenDialog();

    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')
      ->setValue('Satanic leaf-tailed gecko alt');
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')
      ->setValue('Satanic leaf-tailed gecko title');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $img->getAttribute('alt'));
    $this->assertEquals('Satanic leaf-tailed gecko title', $img->getAttribute('title'));

    $this->config('field.field.media.image.field_media_image')
      ->set('settings.alt_field', FALSE)
      ->set('settings.title_field', FALSE)
      ->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('default alt', $img->getAttribute('alt'));
    $this->assertEquals('default title', $img->getAttribute('title'));

    $field = FieldConfig::load('media.image.field_media_image');
    $settings = $field->getSettings();
    $settings['alt_field'] = TRUE;
    $field->set('settings', $settings);
    $field->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    // Test that when only the alt field is enabled, only alt field should
    // display.
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')->setValue('Satanic leaf-tailed gecko alt');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $img->getAttribute('alt'));
    $this->assertEquals('default title', $img->getAttribute('title'));

    $field = FieldConfig::load('media.image.field_media_image');
    $settings = $field->getSettings();
    $settings['alt_field'] = FALSE;
    $settings['title_field'] = TRUE;
    $field->set('settings', $settings);
    $field->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    // With only title field enabled, only title field should display.
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')->setValue('Satanic leaf-tailed gecko title');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko title', $img->getAttribute('title'));
    $this->assertEquals('default alt', $img->getAttribute('alt'));

    $field = FieldConfig::load('media.image.field_media_image');
    $settings = $field->getSettings();
    $settings['alt_field'] = TRUE;
    $settings['title_field'] = TRUE;
    $field->set('settings', $settings);
    $field->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    // Test that setting value to double quote will allow setting the alt
    // and title to empty.
    $alt->setValue(MediaImageDecorator::EMPTY_STRING);
    $title->setValue(MediaImageDecorator::EMPTY_STRING);

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEmpty($img->getAttribute('alt'));
    $this->assertEmpty($img->getAttribute('title'));

    $this->reopenDialog();

    // Test that *not* filling out the fields makes it fall back to the default.
    $alt->setValue('');
    $title->setValue('');
    $this->submitDialog();
    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('default alt', $img->getAttribute('alt'));
    $this->assertEquals('default title', $img->getAttribute('title'));
  }

  /**
   * Tests caption editing in the CKEditor widget.
   */
  public function testCkeditorWidgetHasEditableCaption() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->pressEditorButton('test_media_entity_embed');
    $this->assertSession()->waitForId('drupal-modal');

    // Embed media.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Screaming hairy armadillo (1)');
    $this->assertSession()
      ->elementExists('css', 'button.js-button-next')
      ->click();
    $this->assertSession()
      ->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('entity_reference:media_thumbnail');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue('Is this the real life? Is this just fantasy?');
    $this->submitDialog();

    // Assert that the embedded media was upcasted to a CKEditor widget.
    $figcaption = $this->assertSession()
      ->elementExists('css', 'figcaption');
    $this->assertTrue($figcaption->hasAttribute('contenteditable'));

    // Type in the widget's editable for the caption.
    $this->setCaption('Caught in a <strong>landslide</strong>! No escape from <em>reality</em>!');
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementExists('xpath', '//figcaption//em');
    $this->assertSession()
      ->elementExists('xpath', '//figcaption//strong')
      ->click();

    // Select the <strong> element and unbold it.
    $this->clickPathLinkByTitleAttribute("strong element");
    $this->pressEditorButton('bold');
    $this->getSession()->switchToIFrame('ckeditor');
    $figcaption = $this->assertSession()
      ->elementExists('xpath', '//figcaption');
    $this->assertTrue($figcaption->has('css', 'em'));
    $this->assertFalse($figcaption->has('css', 'strong'));

    // Select the <em> element and unitalicize it.
    $this->assertSession()->elementExists('xpath', '//figcaption//em')->click();
    $this->clickPathLinkByTitleAttribute("em element");
    $this->pressEditorButton('italic');

    // The "source" button should reveal the HTML source in a state matching
    // what is shown in the CKEditor widget.
    $this->pressEditorButton('source');
    $source = $this->assertSession()
      ->elementExists('xpath', "//textarea[contains(@class, 'cke_source')]");
    $value = $source->getValue();
    $dom = Html::load($value);
    $xpath = new \DOMXPath($dom);
    $drupal_entity = $xpath->query('//drupal-entity')[0];
    $this->assertEquals('Caught in a landslide! No escape from reality!', $drupal_entity->getAttribute('data-caption'));

    // Change the caption by modifying the HTML source directly. When exiting
    // "source" mode, this should be respected. Note that there is a <strong>
    // tag and it must be HTML-encoded.
    $poor_boy_text = "I'm just a <strong>poor boy</strong>, I need no sympathy!";
    $drupal_entity->setAttribute("data-caption", htmlentities($poor_boy_text));
    $source->setValue(Html::serialize($dom));
    $this->pressEditorButton('source');
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $figcaption = $this->assertSession()->waitForElement('css', 'figcaption');
    $this->assertEquals($poor_boy_text, $figcaption->getHtml());

    // Select the <strong> element that we just set in "source" mode where we
    // also had to HTML-encode it. This proves that it was indeed rendered by
    // the CKEditor widget.
    $figcaption->find('css', 'strong')->click();
    $this->pressEditorButton('bold');

    // Insert a link into the caption.
    $this->clickPathLinkByTitleAttribute("Caption element");
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('http://www.drupal.org');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Save the entity.
    $this->assertSession()->buttonExists('Save')->press();

    // Verify the saved entity when viewed also contains the captioned media.
    $link = $this->assertSession()->elementExists('xpath', '//figcaption//a');
    $this->assertEquals('http://www.drupal.org', $link->getAttribute('href'));
    $this->assertEquals("I'm just a poor boy, I need no sympathy!", $link->getText());

    // Edit it again, type a different caption in the widget.
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $figcaption = $this->assertSession()
      ->waitForElementVisible('css', 'figcaption');
    $this->assertTrue($figcaption->hasAttribute('contenteditable'));
    $this->setCaption('Scaramouch, <em>Scaramouch</em>, will you do the <strong>Fandango</strong>?');

    // Verify that the element path usefully indicates the specific media type
    // that is being embedded.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementExists('xpath', '//figcaption//em')->click();
    $this->getSession()->switchToIFrame();
    $this->assertSession()
      ->elementTextContains('css', '#cke_1_path', 'Embedded Media Entity Embed');

    // Test that removing caption in the EntityEmbedDialog form sets the embed
    // to be captionless.
    $this->reopenDialog();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue('');
    $this->submitDialog();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->elementExists('css', 'drupal-entity');
    $this->assertSession()->elementNotExists('css', 'figcaption');

    // Set a caption again; this time not using the CKEditor Widget, but through
    // the dialog. We're typing HTML in the form field, but it will have to be
    // HTML-encoded for it to actually show up properly in the CKEditor Widget.
    $this->reopenDialog();
    $freddys_lament = "Mama, life had just begun. But now I've gone and <strong>thrown it all away</strong>! :(";
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue($freddys_lament);
    $this->submitDialog();
    $this->assertSession()->elementExists('css', 'figcaption');

    // Change the caption in the dialog to contain a link.
    $wind_markup = '<a href="http://www.drupal.org">anyway the wind blows</a>';
    $this->reopenDialog();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue($wind_markup);
    $this->submitDialog();

    // Assert the caption in the CKEditor widget was updated.
    $figcaption = $this->assertSession()
      ->waitForElementVisible('css', 'figcaption');
    $this->assertEquals('anyway the wind blows', $figcaption->getText());

    // Change the text of the link in the caption.
    $gallileo = '<a href="http://www.drupal.org">Gallileo, figaro, magnifico</a>';
    $this->reopenDialog();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue($gallileo);
    $this->submitDialog();

    // Assert the caption in the CKEditor widget was updated.
    $figcaption = $this->assertSession()
      ->waitForElementVisible('css', 'figcaption');
    $this->assertEquals('Gallileo, figaro, magnifico', $figcaption->getText());
  }

  /**
   * Tests linkability of the CKEditor widget when `drupalimage` is disabled.
   */
  public function testCkeditorWidgetIsLinkableWhenDrupalImageIsAbsent() {
    // Remove the `drupalimage` plugin's `DrupalImage` button.
    $editor = Editor::load('full_html');
    $settings = $editor->getSettings();
    $rows = $settings['toolbar']['rows'];
    foreach ($rows as $row_key => $row) {
      foreach ($row as $group_key => $group) {
        foreach ($group['items'] as $item_key => $item) {
          if ($item === 'DrupalImage') {
            unset($settings['toolbar']['rows'][$row_key][$group_key]['items'][$item_key]);
          }
        }
      }
    }
    $editor->setSettings($settings);
    $editor->save();

    $this->testCkeditorWidgetIsLinkable();
  }

  /**
   * Tests linkability of the CKEditor widget.
   */
  public function testCkeditorWidgetIsLinkable() {
    $this->host->body->value = '<drupal-entity data-caption="baz" data-embed-button="test_media_entity_embed" data-entity-embed-display="entity_reference:media_thumbnail" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="' . $this->media->uuid() . '"></drupal-entity>';
    $this->host->save();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');

    // Select the CKEditor Widget and click the "link" button.
    $this->assertSession()->elementExists('css', 'drupal-entity')->click();
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');

    // Enter a link in the link dialog and save.
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('http://www.drupal.org');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Save the entity.
    $this->assertSession()->buttonExists('Save')->press();

    // Verify the saved entity when viewed also contains the linked media.
    $this->assertSession()->elementExists('xpath', '//a[@href="http://www.drupal.org"]//div[@data-embed-button="test_media_entity_embed"]//img');

    // Test that `drupallink` also still works independently.
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('http://www.drupal.org');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Tests even <drupal-entity> elements whose button is not present are upcast.
   *
   * @param string $data_embed_button_attribute
   *   The HTML for a data-embed-button atttribute.
   *
   * @dataProvider providerCkeditorWidgetWorksForAllEmbeds
   */
  public function testCkeditorWidgetWorksForAllEmbeds($data_embed_button_attribute) {
    $this->host->body->value = '<drupal-entity data-caption="baz" ' . $data_embed_button_attribute . ' data-entity-embed-display="entity_reference:media_thumbnail" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="' . $this->media->uuid() . '"></drupal-entity>';
    $this->host->save();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();

    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertNotNull($this->assertSession()->waitForElementVisible('css', 'figcaption'));
  }

  /**
   * Data provider for testCkeditorWidgetWorksForAllEmbeds().
   */
  public function providerCkeditorWidgetWorksForAllEmbeds() {
    return [
      'present and active CKEditor button ID' => [
        'data-embed-button="test_media_entity_embed"',
      ],
      'present and inactive CKEditor button ID' => [
        'data-embed-button="user"',
      ],
      'present and nonsensical CKEditor button ID' => [
        'data-embed-button="ceci nest pas une pipe"',
      ],
      'absent' => [
        '',
      ],
    ];
  }

  /**
   * Helper function to submit dialog and focus on ckeditor frame.
   */
  protected function submitDialog() {
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('ckeditor');
  }

  /**
   * Set the text of the editable caption to the given text.
   *
   * @param string $text
   *   The text to set in the caption.
   */
  protected function setCaption($text) {
    $this->getSession()->switchToIFrame();
    $select_and_edit_caption = "var editor = CKEDITOR.instances['edit-body-0-value'];
       var figcaption = editor.widgets.getByElement(editor.editable().findOne('figcaption'));
       figcaption.editables.caption.setData('" . $text . "')";
    $this->getSession()->executeScript($select_and_edit_caption);
  }

  /**
   * Clicks a link in the editor's path links with the given title text.
   *
   * @param string $text
   *   The title attribute of the link to click.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function clickPathLinkByTitleAttribute($text) {
    $this->getSession()->switchToIFrame();
    $selector = '//span[@id="cke_1_path"]//a[@title="' . $text . '"]';
    $this->assertSession()->elementExists('xpath', $selector)->click();
  }

}
