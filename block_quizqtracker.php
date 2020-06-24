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

class block_quizqtracker extends block_base
{
    public function init()
    {
        $this->title = get_string('quizqtracker_quiz', 'block_quizqtracker');
    }
    // The PHP tag and the curly bracket for the class definition 
    // will only be closed after there is another function added in the next section.

    public function instance_allow_multiple()
    {
        return true;
    }

    public function applicable_formats()
    {
        return array('all' => false, 'mod-quiz' => true);

    }

    function has_config()
    {
        return true;
    }

    function get_content()
    {
        global $CFG, $OUTPUT, $USER, $PAGE;

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

        // user/index.php expect course context, so get one if page has module context.
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

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        /*echo '<br><br><br><br><br>';
        echo '<pre>';
        //print_r($this->page->context);

        //print_r($this->page->context);
        echo '<br><br>';
        //print_r($currentcontext);

*/
        $this->userid = $USER->id;

        // Get submitted parameters.
        $attemptid = optional_param('attempt', null, PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $cmid = optional_param('cmid', null, PARAM_INT);

/* 
        echo '<br>';
        //print_r($attemptid);
        echo '<br>';
        //print_r($cmid);
        echo '<br>';
        //print_r($page); */

        //$OUTPUT->get_plugin_renderer('block_quizqtracker');
        $questions = [];
        if (!is_null($attemptid)) {
            $attemptobj = quiz_create_attempt_handling_errors($attemptid, $cmid);
            $slots = $attemptobj->get_slots($page);
            //print_r($slots);
            $qattempt = question_engine::load_questions_usage_by_activity($attemptobj->get_attempt()->uniqueid);

            foreach ($slots as $slot) {
                //print_r($qattempt->get_question($slot)->name);
                //print_r(":");
                $qname = $qattempt->get_question($slot)->name;
                $this->content->text .= html_writer::tag('div', $qname);

                $question = $qattempt->get_question($slot);
                array_push($questions, $question);
            }
        }
        
        $renderer = $this->page->get_renderer('local_qtracker');
        $templatable = new issue_registration_block($questions, $USER->id);
        $this->content->text .= $renderer->render($templatable);

        //quiz_get_user_attempts()
        // $quba = \question_engine::load_questions_usage_by_activity(512);
        // $slots = $quba->get_slots();
        // $latestslot = end($slots);
        // //print_r($latestslot);
        //print_r($quba->get_question_attempt($latestslot));
        //echo '</pre>';

        // question_usages : question_usage_id, contextid

        //$this->content->text .= $currentcontext;
        return $this->content;
    }
}
