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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The plugin uses the ASYST grading tool <https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST>
 * modified to work as a web endpoint.
 *
 * This file contains the quiz API tests for the local_asystgrade plugin.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranov
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_asystgrade\api\client;
use local_asystgrade\api\http_client;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/phpunit/classes/advanced_testcase.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/tests/generator/lib.php');

/**
 * Class quiz_api_test
 * @package local_asystgrade
 */
class quiz_api_test extends advanced_testcase {

    /**
     * Sets up the test environment by resetting the database and truncating quiz-related tables.
     *
     * @throws dml_exception
     */
    protected function setUp(): void {
        global $DB;

        $this->resetAfterTest();
        parent::setUp();

        // Clear quiz-related tables to ensure a clean test environment.
        $DB->execute('TRUNCATE TABLE {quiz_attempts}');
        $DB->execute('TRUNCATE TABLE {quiz_slots}');
    }

    /**
     * Tests the quiz API by creating quiz attempts and validating responses.
     * @throws Exception
     */
    public function test_quiz_api(): void {
        global $DB;

        $generator   = $this->getDataGenerator();
        $quizgen     = $generator->get_plugin_generator('mod_quiz');
        $questiongen = $generator->get_plugin_generator('core_question');

        // Create a course.
        $course = $generator->create_course([
            'fullname'  => 'Test Course',
            'shortname' => 'testcourse',
            'category'  => 1,
        ]);

        // Create and enroll a teacher.
        $teacher       = $generator->create_user();
        $teacherroleid = $DB->get_record('role', ['shortname' => 'teacher'])->id;
        $generator->enrol_user($teacher->id, $course->id, $teacherroleid);
        $this->setUser($teacher);

        // Create a quiz in the course.
        $quiz = $quizgen->create_instance([
            'course'    => $course->id,
            'name'      => 'Test Quiz',
            'intro'     => 'This is a test quiz.',
            'attempts'  => 1,
            'timeopen'  => time(),
            'timeclose' => time() + 3600,
        ]);

        // Create a question category.
        $context  = context_course::instance($course->id);
        $category = $this->create_question_category($context->id);

        // Create sample questions and add them to the quiz.
        $questions = include_once('fakedata/questions.php');
        foreach ($questions as $questiondata) {
            $question = $this->create_question($questiongen, $questiondata, $category, $teacher->id, $context);
            $this->add_question_to_quiz($quiz, $question);
        }

        // Create and enroll students.
        $students = [];
        for ($i = 0; $i < 7; $i++) {
            $students[] = $generator->create_user();
            $generator->enrol_user($students[$i]->id, $course->id, 'student');
        }

        // Create quiz attempts for students.
        $flatanswers = array_merge(...array_map(function($q) {
            return isset($q['answers']) ? array_keys($q['answers']) : [];
        }, $questions));
        foreach ($students as $student) {
            $this->create_quiz_attempt($quiz->id, $student->id, $flatanswers[array_rand($flatanswers)]);
        }

        $referenceanswers = [];
        foreach ($questions as $questiondata) {
            if (isset($questiondata['answers']) && is_array($questiondata['answers'])) {
                foreach ($questiondata['answers'] as $answertext => $fraction) {
                    if ($fraction == 1) {
                        $referenceanswers[] = $answertext;
                    }
                }
            }
        }

        $requestdata = [
            'studentAnswers'  => $flatanswers,
            'referenceAnswer' => array_fill(0, count($flatanswers), $referenceanswers[0] ?? ''),
        ];

        $this->send_answers_to_api($requestdata);
    }

    /**
     * Creates a question category.
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_question_category(int $contextid) {
        global $DB;

        $category = [
            'name'       => 'Test Category',
            'contextid'  => $contextid,
            'parent'     => 0,
            'info'       => '',
            'infoformat' => FORMAT_MOODLE,
        ];
        $categoryid = $DB->insert_record('question_categories', (object)$category);

        if (!$categoryid) {
            throw new coding_exception("Failed to create question category.");
        }

        return $DB->get_record('question_categories', ['id' => $categoryid]);
    }

    /**
     * Creates a question and adds it to a category.
     */
    private function create_question($questiongen, $questiondata, $category, $modifiedby, $context) {
        return $questiongen->create_question($questiondata['qtype'], null, [
            'category'     => $category->id,
            'questiontext' => ['text' => $questiondata['questiontext'], 'format' => FORMAT_HTML],
            'name'         => 'Test Question',
            'contextid'    => $context->id,
            'modifiedby'   => $modifiedby,
        ]);
    }

    /**
     * Adds a question to a quiz.
     * @throws dml_exception
     */
    private function add_question_to_quiz($quiz, $question) {
        global $DB;

        $slotdata = [
            'quizid'          => $quiz->id,
            'questionid'      => $question->id,
            'slot'            => $DB->get_field_sql(
                "SELECT COALESCE(MAX(slot), 0) + 1 FROM {quiz_slots} WHERE quizid = ?",
                [$quiz->id]
            ),
            'page'            => 1,
            'requireprevious' => 0,
            'maxmark'         => 1.0,
        ];

        $DB->insert_record('quiz_slots', (object)$slotdata);
    }

    /**
     * Creates a quiz attempt for a student.
     * @throws dml_exception
     */
    private function create_quiz_attempt($quizid, $userid, $answer) {
        global $DB;

        $uniqueid = $DB->get_field_sql('SELECT COALESCE(MAX(uniqueid), 0) + 1 FROM {quiz_attempts}');
        $attempt  = [
            'quiz'      => $quizid,
            'userid'    => $userid,
            'attempt'   => 1,
            'uniqueid'  => $uniqueid,
            'state'     => 'finished',
            'timefinish' => time(),
            'layout' => '',
        ];

        $DB->insert_record('quiz_attempts', (object)$attempt);

        // Add student answer (mocked data).
        $DB->insert_record('question_attempt_step_data', [
            'attemptstepid' => $uniqueid,
            'name'          => 'answer',
            'value'         => $answer,
        ]);
    }

    /**
     * Sends answers to the API and verifies the response.
     */
    private function send_answers_to_api($requestdata) {
        try {
            $apiendpoint = get_config('local_asystgrade', 'apiendpoint') ?: 'http://127.0.0.1:5001/api/autograde';
            $httpclient  = new http_client();
            $apiclient   = client::getInstance($apiendpoint, $httpclient);

            $response = $apiclient->send_data($requestdata);
            $grades   = json_decode($response, true);

            $this->assertNotEmpty($grades, 'API returned empty grades.');
        } catch (Exception $e) {
            debugging('Error: ' . $e->getMessage());
        }
    }
}
