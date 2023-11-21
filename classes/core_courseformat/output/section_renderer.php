<?php

namespace theme_ucl\core_courseformat\output;

defined('MOODLE_INTERNAL') || die();

abstract class section_renderer extends \core_courseformat\output\section_renderer {


    /**
     * Get the course index drawer with placeholder.
     *
     * The default course index is loaded after the page is ready. Format plugins can override
     * this method to provide an alternative course index.
     *
     * If the format is not compatible with the course index, this method will return an empty string.
     *
     * @param course_format $format the course format
     * @return String the course index HTML.
     */
    public function course_index_drawer(course_format $format): ?String {
        global $COURSE;
        echo "ucl theme";
        $template = new stdClass;
        $template->coursename = $COURSE->fullname;
        if ($format->uses_course_index()) {
          include_course_editor($format);
          return $this->render_from_template('theme_ucl/core_courseformat/local/courseindex/drawer', $template);
        }
        return '';
      }

}