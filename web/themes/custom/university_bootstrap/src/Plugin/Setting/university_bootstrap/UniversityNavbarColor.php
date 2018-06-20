<?php
namespace Drupal\university_bootstrap\Plugin\Setting\university_bootstrap;

use Drupal\bootstrap\Annotation\BootstrapSetting;
use Drupal\bootstrap\Plugin\Setting\SettingBase;
use Drupal\Core\Annotation\Translation;

/**
 * The "university_bootstrap_color" theme setting.
 *
 * @ingroup plugins_setting
 *
 * @BootstrapSetting(
 *   id = "university_bootstrap_color",
 *   weight = 3,
 *   type = "select",
 *   title = @Translation("University Navbar Color"),
 *   defaultValue = "university-default",
 *   description = @Translation("Choose a color theme for the Navbar header."),
 *   groups = {
 *     "university_bootstrap" = "University Brand Bar and Navbar",
 *   },
 *   options = {
 *     "university-default" = @Translation("Default Black"),
 *     "university-scarlet" = @Translation("University Scarlet"),
 *     "university-brown" = @Translation("University Brown"),
 *   },
 * )
 */

class UniversityNavbarColor extends SettingBase {}