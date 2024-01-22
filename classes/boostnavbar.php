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

namespace theme_ucl;

use core_course\external\course_summary_exporter;

/**
 * Breadcrumbe for UCL which dosn't remove Sections.
 *
 * This class is copied and modified from /theme/boost/classes/boostnavbar.php
 *
 * @package    theme_ucl
 * @copyright  2023 Stuart Lamour
 * @copyright  based on code from theme_boost by Adrian Greeve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class boostnavbar extends \theme_boost\boostnavbar {


    /**
     * UCL Change - don't remove Sections from breadcrumb, add course image.
     *
     * Remove items that have no actions associated with them and optionally remove items that are sections.
     *
     * The only exception is the last item in the list which may not have a link but needs to be displayed.
     *
     * @param bool $removesections Whether section items should be also removed (only applies when they have an action)
     */
    protected function remove_no_link_items(bool $removesections = true): void {
        global $COURSE;

        foreach ($this->items as $key => $value) {
            if (!$value->is_last() && !$value->has_action()) {
                unset($this->items[$key]);
            }

            // Add icon to course link.
            if ($value->type == \navigation_node::TYPE_COURSE) {
                $value->image = course_summary_exporter::get_course_image($COURSE);
            }
        }
        $this->items = array_values($this->items);
    }
}