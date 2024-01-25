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

namespace theme_ucl\output\core;

defined('MOODLE_INTERNAL') || die();

use coursecat_helper;
use core_course_list_element;
use context_course;
use core_course_category;
use core_course\external\course_summary_exporter;
use core_user;
use html_writer;
use moodle_url;
use stdClass;

require_once($CFG->dirroot . '/course/renderer.php');

class course_renderer extends \core_course_renderer {

    /**
     * Renders course info box used on enrol and info pages.
     *
     * @param stdClass $course
     */
    public function course_info_box(stdClass $course): string {
        // Template for outputing course as a list iteam.
        $template = new stdClass();
        $template->fullname = $course->fullname;
        $template->courseimage = course_summary_exporter::get_course_image($course);
        $template->coursesummary = html_to_text(format_string($course->summary, true));
        // Course contacts.
        $template->coursecontacts = $this->course_contacts_list($course);
        return $this->render_from_template('theme_ucl/course-enrol', $template);
    }

    /**
     * Returns UCL course contacts.
     *
     * @param stdClass $course
     */
    public function course_contacts_list(stdClass $course): stdClass {
        $course = new core_course_list_element($course);
        $contacts = $course->get_course_contacts();
        $template = new stdClass();

        if (!empty($contacts)) {
            $template->hascontacts = true;

            foreach ($contacts as $c) {
                $contact = new stdClass();
                $contact->id = $c['user']->id;
                $contact->name = $c['username'];
                $contact->role = $c['rolename'];

                // Image.
                $user = core_user::get_user($contact->id);
                $userpicture = new \user_picture($user);
                $userpicture->link = false;
                $userpicture->alttext = false;
                $userpicture->size = 50;
                $contact->picture = $this->render($userpicture);

                // Email.
                $contact->email = $user->email;

                // Group by UCL specific roles.
                switch (strtolower($contact->role)) {
                    case "leader":
                        $template->leader[] = $contact;
                        $template->hasleader = true;
                      break;
                    case "tutor":
                        $template->tutor[] = $contact;
                        $template->hastutor = true;
                      break;
                    case "course administrator":
                        $template->admin[] = $contact;
                        $template->hasadmin = true;
                      break;
                    default:
                      // Do nothing.
                }
            }
        }

        return $template;
    }

    /**
     * Altered to remove enrolment icons as we add these elsewhere.
     *
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $content = '';
        $classes = trim('coursebox clearfix '. $additionalclasses);

        // .coursebox
        $content .= html_writer::start_tag('div', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => self::COURSECAT_TYPE_COURSE,
        ));

        $content .= html_writer::start_tag('div', array('class' => 'info'));
        $content .= $this->course_name($chelper, $course);
        $content .= html_writer::end_tag('div');


        $content .= html_writer::start_tag('div', array('class' => 'content'));
        $content .= $this->coursecat_coursebox_content($chelper, $course);
        $content .= html_writer::end_tag('div');

        $content .= html_writer::end_tag('div'); // .coursebox
        return $content;
    }

    /**
     * Altered to return nothing, as we always use the list view.
     *
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_coursebox() and may be re-used in AJAX
     *
     * @param coursecat_helper $chelper various display options
     * @param stdClass|core_course_list_element $course
     * @return string
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
        return '';
    }

    /**
     * Altered to add data and use mustache template.
     *
     * Returns HTML to display course name.
     *
     * @param coursecat_helper $chelper
     * @param core_course_list_element $course
     * @return string
     */
    protected function course_name(coursecat_helper $chelper, core_course_list_element $course): string {
        global $OUTPUT;
        // Template for outputing course as a list iteam.
        $template = new stdClass();
        $template->fullname = $course->fullname;
        $template->image = course_summary_exporter::get_course_image($course);
        $template->viewurl = new moodle_url('/course/view.php', array('id' => $course->id));
        /* Locked. */
        $context = context_course::instance($course->id);
        if ($context->is_locked()) {
            $template->readonly = true;
        }
        $template->visible = $course->visible;
        $template->enrolmenticons = $this->course_enrolment_icons($course);

        // Check if we are in search.
        $search = optional_param('q', 0, PARAM_RAW);
        if ($search) {
            $template->search = true;
            // Add extra data for search.
            $cat = core_course_category::get($course->category, IGNORE_MISSING);
            $template->coursecategory = $cat->get_formatted_name();
            $template->caturl = new moodle_url('/course/index.php', array('categoryid' => $cat->id));
            https://moodle.ucl.ac.uk/course/index.php?categoryid=1599
            /* Academic year. */
            $template->year = $OUTPUT->get_course_academic_year($course->id);
        }

        return $this->render_from_template('theme_ucl/courselink', $template);
    }

     /**
     * Altered to output courses before sub-catagories.
     *
     * Returns HTML to display the subcategories and courses in the given category
     *
     * This method is re-used by AJAX to expand content of not loaded category
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of the category in the current tree
     * @return string
     */
    protected function coursecat_category_content(coursecat_helper $chelper, $coursecat, $depth) {
        $content = '';

        // AUTO show courses: Courses will be shown expanded if this is not nested category,
        // and number of courses no bigger than $CFG->courseswithsummarieslimit.
        $showcoursesauto = $chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO;
        if ($showcoursesauto && $depth) {
            // this is definitely collapsed mode
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
        }

        // Courses.
        if ($chelper->get_show_courses() > course_renderer::COURSECAT_SHOW_COURSES_COUNT) {
            $courses = array();
            if (!$chelper->get_courses_display_option('nodisplay')) {
                $courses = $coursecat->get_courses($chelper->get_courses_display_options());
            }
            if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // the option for 'View more' link was specified, display more link (if it is link to category view page, add category id)
                if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
                    $chelper->set_courses_display_option('viewmoreurl', new moodle_url($viewmoreurl, array('categoryid' => $coursecat->id)));
                }
            }
            $content .= $this->coursecat_courses($chelper, $courses, $coursecat->get_courses_count());
        }

        if ($showcoursesauto) {
            // restore the show_courses back to AUTO
            $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO);
        }

        // Subcategories.
        $content .= $this->coursecat_subcategories($chelper, $coursecat, $depth);

        return $content;
    }
}