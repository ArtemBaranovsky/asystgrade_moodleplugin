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
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
 * @copyright based on work by 2023 Ulrike Padó <ulrike.pado@hft-stuttgart.de>,
 * @copyright Yunus Eryilmaz & Larissa Kirschner <https://link.springer.com/article/10.1007/s40593-023-00383-w>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_asystgrade\db;

use dml_exception;
use moodle_database;
use moodle_recordset;
use stdClass;

/**
 * Class quizquery handles some SQL queries to fetch data about Quiz
 */
class quizquery implements quizquery_interface {

    /**
     * @var moodle_database $db $DB Holds Moodle Database connection
     */
    private moodle_database $db;

    /**
     * Setting DB connection as a class property
     */
    public function __construct() {
        global $DB;
        $this->db = $DB;
    }

    /**
     * Function fetches set of question_attempt records
     *
     * @param int $qid
     * @param int $slot
     * @return moodle_recordset
     * @throws dml_exception
     */
    public function get_question_attempts(int $qid, int $slot): moodle_recordset {
        return $this->db->get_recordset(
            'question_attempts',
            [
                'questionid' => $qid,
                'slot' => $slot
            ],
            '',
            '*'
        );
    }

    /**
     * Function fetches reference_answer
     *
     * @param int $qid
     * @return stdClass
     * @throws dml_exception
     */
    public function get_reference_answer(int $qid): stdClass {
        return $this->db->get_record(
            'qtype_essay_options',
            [
                'questionid' => $qid
            ],
            '*',
            MUST_EXIST
        )->graderinfo;
    }

    /**
     * Function fetches set of question_attempt_steps records
     *
     * @param int $questionAttemptId
     * @return moodle_recordset
     * @throws dml_exception
     */
    public function get_attempt_steps(int $questionattemptid): moodle_recordset {
        return $this->db->get_recordset(
            'question_attempt_steps',
            [
                'questionattemptid' => $questionattemptid
            ],
            '',
            '*'
        );
    }

    /**
     * Function fetches reference_answer
     *
     * @param int $attemptStepId
     * @return stdClass
     * @throws dml_exception
     */
    public function get_student_answer(int $attemptstepid): stdClass {
        return $this->db->get_record(
            'question_attempt_step_data',
            [
                'attemptstepid' => $attemptstepid,
                'name' => 'answer'
            ],
            '*',
            MUST_EXIST
        )->value;
    }
}
