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

/**
 * Interface for executing queries related to quiz attempts.
 */
interface quizquery_interface {

    /**
     * Get question attempts for a given question ID and slot.
     *
     * @param int $qid The question ID.
     * @param int $slot The question slot.
     * @return moodle_recordset A moodle_recordset of question attempts.
     */
    public function get_question_attempts(int $qid, int $slot): moodle_recordset;

    /**
     * Get the reference answer for a given question ID.
     *
     * @param int $qid The question ID.
     * @return stdClass The reference answer.
     */
    public function get_reference_answer(int $qid): stdClass;

    /**
     * Get the attempt steps for a given question attempt ID.
     *
     * @param int $questionAttemptId The question attempt ID.
     * @return moodle_recordset A moodle_recordset of attempt steps.
     */
    public function get_attempt_steps(int $questionattemptid): moodle_recordset;

    /**
     * Get the student answer for a given attempt step ID.
     *
     * @param int $attemptStepId The attempt step ID.
     * @return stdClass The student answer.
     */
    public function get_student_answer(int $attemptstepid): stdClass;
}
