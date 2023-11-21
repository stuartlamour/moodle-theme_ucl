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

defined('MOODLE_INTERNAL') || die();

use core_course\external\course_summary_exporter;

/**
 * Render core html for the theme.
 *
 * @package   theme_ucl
 * @copyright 2023 Stuart Lamour
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_ucl_core_renderer extends theme_boost\output\core_renderer {

    /**
     * UCL change - don't remove Sections from breadcrumb.
     *
     * Renders the "breadcrumb" for all pages in ucl.
     *
     * This renderer function is copied and modified from /theme/boost/classes/output/core_renderer.php
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_ucl\boostnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }

    /**
     * Return HTML for footer from WYSIWYG editor.
     *
     * @return string  HTML.
     */
    public function footer_content() {
        global $PAGE;
        $footer = empty($PAGE->theme->settings->footer) ? '' : $PAGE->theme->settings->footer;
        return format_text($footer, FORMAT_HTML, ['noclean' => true]);
    }

    /**
     * Return html template with footer metadata.
     *
     * @return string
     */
    public function footermetadata(): string {
        $template = new stdClass;
        // Moodle standard footer html content.
        $output = '';
        $pluginswithfunction = get_plugins_with_function('standard_footer_html', 'lib.php');
        foreach ($pluginswithfunction as $plugins) {
            foreach ($plugins as $p) {
                // Hide get the mobile app link.
                if ($p !== 'tool_mobile_standard_footer_html') {
                    $output .= $p();
                }
            }
        }
        $template->standard_footer_html = $output;
        $template->page_doc_link = $this->page_doc_link();
        $template->supportemail = $this->supportemail();
        $template->login_info = $this->login_info();

        return $this->render_from_template('theme_boost/footer-metadata', $template);
    }

    /**
     * Return html template with front page hero.
     *
     * @return string
     */
    public function frontpagehero(): string {
        global $SITE;

        $template = new stdClass();
        $template->name = $SITE->fullname;
        $template->description = html_to_text(format_string($SITE->summary, true));
        return $this->render_from_template('theme_ucl/hero', $template);
    }

        /**
     * Return html template with course page hero.
     *
     * @return string
     */
    public function courseheader(): string {
        global $COURSE;

        $template = new stdClass();
        $template->coursename = $COURSE->fullname;
        $template->courseimage = course_summary_exporter::get_course_image($COURSE);
        $template->coursesummary = html_to_text(format_string($COURSE->summary, true));
        $template->visible = $COURSE->visible;

        // Course category.
        if ($cat = core_course_category::get($COURSE->category, IGNORE_MISSING)) {
                // Add catagory to be output by template.
                $template->coursecat = $cat->get_formatted_name();
        }

        // Progress.
        if ($COURSE->enablecompletion) {
            $template->hasprogress = true;
        }

        // Actions (bulk edit).)
        $template->headeractions = $this->page->get_header_actions();

        return $this->render_from_template('theme_ucl/course-header', $template);
    }




    public function noty() {
        global $USER, $DB;
        $user = $USER;

        // Notifications.
        $notifications = $this->get_user_notifications($user);
        $unread = 0;
        $template = new stdClass();
        $template->userid = $user->id;
        foreach ($notifications as $n) {
            // Count unread for a badge.
            if (!$n->timeread) {
                $unread ++;
            }
            // How do notification links work?
            // e.g. there is no contexturl on badges...
            // SHAME - Core UX issue.
            $notification = new \stdClass();
            $notification->id = $n->id;
            $notification->subject = $n->subject;
            $notification->date = $n->timecreated;
            $notification->url = $n->contexturl;
            $notification->read = $n->timeread;
            $notification->fullmessage = $n->fullmessagehtml;
            $notification->image = $this->image_url('notification', 'theme');
            $notification->componant = $n->component;

            // Notification images.
            // This can be stored as string/array in customdata field.
            // {"notificationiconurl":"http:\/\/localhost:8888\/moodle381\/pluginfile.php\/37\/badges\/badgeimage\/1\/f1","hash":"9eb3e61db121d003e0be0c268c838ab9958f1f3a","courseid":"4"}
            $customdata = json_decode($n->customdata, true);
            // If we are in a mod notification, use the course image and mod icon.
            if ($customdata['courseid']) {
                // Has a course!
                // Use course image and mod as icon...
                $course = $DB->get_record('course', array('id' => $customdata['courseid']));
                $notification->image = course_summary_exporter::get_course_image($course);
            }

            // Badge notifications.
            if ($n->eventtype == 'badgerecipientnotice') {
                $notification->url = new \moodle_url('/badges/mybadges.php');
            }

            $template->notifications[] = $notification;
        }
        $template->unread = $unread;

        return $this->render_from_template('theme_ucl/notifications', $template);

    }

    /**
     * Return user notifications.
     * @param Object $user
     * @return array  user notifications.
     */
    public static function get_user_notifications($user): array {
        global $DB;

        // Get notifications.
        // Exculde message contact request notifications.
        $sql = "SELECT *
                FROM {notifications}
               WHERE useridto = :userid
                 AND eventtype != 'messagecontactrequests'
                 AND eventtype != 'availableupdate'
                 AND timecreated >= :since
            ORDER BY timecreated DESC
               LIMIT 10";

        // Limit to last 1 months.

        $since = strtotime('-1 month');
        $notifications = $DB->get_records_sql($sql, array('userid' => $user->id, 'since' => $since));

        return $notifications;
    }

    public function username() {
        global $USER;
        return fullname($USER);
    }

}
