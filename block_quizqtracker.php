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
 * Main interface to Question Tracker
 *
 * Provides block for registering question issues for quiz module
 *
 * @package     block_quizqtracker
 * @author      André Storhaug <andr3.storhaug@gmail.com>
 * @copyright   2020 NTNU
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \local_qtracker\output\issue_registration_block;

defined('MOODLE_INTERNAL') || die();

class block_quizqtracker extends block_base {
    public function init() {
        $this->title = get_string('quizqtracker_quiz', 'block_quizqtracker');
    }

    public function applicable_formats() {
        return array('all' => false, 'mod-quiz' => true);
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $USER, $PAGE, $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->text = '';
        $this->content->footer = '';

        // Get submitted parameters.
        $attemptid = optional_param('attempt', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $cmid = optional_param('cmid', null, PARAM_INT);

        if (!isset($attemptid)) {
            // Not in an active attempt.
            $url = new moodle_url('/local/qtracker/view.php', array('courseid' => $COURSE->id));
            $this->content->text = html_writer::link($url, "View issues...");
            return $this->content;
        }

        $currentcontext = $this->page->context->get_course_context(false);
        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        if (empty($currentcontext)) {
            return $this->content;
        }

        if ($this->page->course->id == SITEID) {
            $this->content->text .= "site context";
        }

        if (isset($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $this->content->text = html_writer::tag('p', get_string('question_problem_details', 'block_quizqtracker'));
        }

        $this->userid = $USER->id;

        if (!is_null($attemptid)) {
            $attemptobj = quiz_create_attempt_handling_errors($attemptid, $cmid);
            $slots = $attemptobj->get_slots($page);
            $quba = question_engine::load_questions_usage_by_activity($attemptobj->get_attempt()->uniqueid);
        }

        $renderer = $this->page->get_renderer('local_qtracker');
        $context = $PAGE->context;
        $templatable = new issue_registration_block($quba, $slots, $context->id);
        $this->content->text .= $renderer->render_block($templatable);

        //$url = new moodle_url('/local/qtracker/view.php', array('courseid' => $COURSE->id));
        //$this->content->footer = html_writer::link($url, "View issues...");

        return $this->content;
    }
}
