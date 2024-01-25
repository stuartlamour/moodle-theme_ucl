<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds admin settings for the plugin.
 *
 * @package     theme_ucl
 * @category    admin
 * @copyright   2023 Dragos Suciu <dragos.suciu.15@ucl.ac.uk>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new admin_settingpage('themesettingucl', get_string('pluginname', 'theme_ucl'));

    // SSO redirect.
    $name = 'theme_ucl/ssoenableredirecttoaad';
    $title = get_string('ssoenableredirecttoaad', 'theme_ucl');
    $setting = new admin_setting_configcheckbox($name, $title, '', 1);
    $settings->add($setting);

    // Footer.
    $name = 'theme_ucl/footer';
    $title = get_string('footer', 'theme_ucl');
    $description = '';
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
