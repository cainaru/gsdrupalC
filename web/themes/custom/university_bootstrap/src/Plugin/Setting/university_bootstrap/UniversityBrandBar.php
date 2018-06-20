<?php
namespace Drupal\university_bootstrap\Plugin\Setting\university_bootstrap;

use Drupal\bootstrap\Annotation\BootstrapSetting;
use Drupal\bootstrap\Plugin\Setting\SettingBase;
use Drupal\Core\Annotation\Translation;

/**
 * The "univversity_bootstrap_brand_bar" theme setting.
 *
 * @ingroup plugins_setting
 *
 * @BootstrapSetting(
 *   id = "university_bootstrap_brand_bar",
 *   weight = 5,
 *   type = "checkbox",
 *   title = @Translation("University Brand Bar"),
 *   defaultValue = 1,
 *   description = @Translation("Display university brand bar above Navbar."),
 *   groups = {
 *     "university_bootstrap" = "University Brand Bar and Navbar",
 *   },
 *   options = {
 *     0 = @Translation("No"),
 *     1 = @Translation("Yes")
 *   },
 * )
 */

class UniversityBrandBar extends SettingBase {}