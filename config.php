<?php
// This file is part of Moodle - http://moodle.org/
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
 * Config.
 * @package theme_ucl
 * @copyright 2023 Stuart Lamour
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$THEME->name = 'ucl';
$THEME->parents = ['boost'];
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->editor_scss = ['editor'];
$THEME->usefallback = true;
// SCSS from settings.
$THEME->prescsscallback = 'theme_ucl_get_pre_scss';
$THEME->scss = function($theme) {
    return theme_ucl_get_main_scss_content($theme);
};
$THEME->enable_dock = false;
$THEME->yuicssmodules = [];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->requiredblocks = '';
$THEME->addblockposition = BLOCK_ADDBLOCK_POSITION_FLATNAV;
$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;
$THEME->haseditswitch = true;
// Set to flase, so moodle outputs activity navigation.
$THEME->usescourseindex = false;
// By default, all boost theme do not need their titles displayed.
$THEME->activityheaderconfig = [
    'notitle' => true,
];

// Add class to arrows, so they can be removed with css.
$THEME->larrow = "<span class='larrow'>◀</span>";
$THEME->rarrow = "<span class='rarrow'>▶</span>";


$THEME->layouts = [
    // The site home page.
    'frontpage' => [
        'file' => 'frontpage.php',
        'regions' => ['full-b'],
        'defaultregion' => 'full-b',
        'options' => ['nonavbar' => true],
    ],
    // Dashboard page.
    'mydashboard' => [
        'file' => 'mydashboard.php',
        'regions' => ['full-t', 'full-b', 'side-pre'],
        'defaultregion' => 'side-pre',
        'options' => ['nonavbar' => true, 'langmenu' => true],
    ],
    // Course page.
    'course' => [
        'file' => 'course.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
        'options' => ['langmenu' => true],
    ],
    // Part of course, typical for modules.
    'incourse' => [
        'file' => 'incourse.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
];

