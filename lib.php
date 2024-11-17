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
 * This file contains the library functions for the local_asystgrade plugin.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
 * @copyright based on work by 2023 Ulrike Pado <ulrike.pado@hft-stuttgart.de>,
 * @copyright Yunus Eryilmaz & Larissa Kirschner <https://link.springer.com/article/10.1007/s40593-023-00383-w>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\exception\moodle_exception;
use core\output\notification;

/**
 * A hook function that will process the data and insert the rating value.
 * The function must be called on the desired SAG page like
 * https://www.moodle.loc/mod/quiz/report.php?id=2&mode=grading&slot=1&qid=1&grade=needsgrading&includeauto=1
 *
 * @return void
 * @throws coding_exception|moodle_exception
 */
function local_asystgrade_before_footer(): void {
    global $PAGE;

    // Obtaining parameters from URL.
    $qid = optional_param('qid', null, PARAM_INT);
    $slot = optional_param('slot', false, PARAM_INT);

    if ($PAGE->url->compare(new moodle_url('/mod/quiz/report.php'), URL_MATCH_BASE) && $slot) {

        if (is_flask_backend_running()) {
            $jsdata = [
                'apiendpoint' => 'http://127.0.0.1:5001/api/autograde',
                'qid' => $qid,
                'slot' => $slot
            ];

            $PAGE->requires->js(new moodle_url('/local/asystgrade/js/grade.js', ['v' => time()]));
            $PAGE->requires->js_init_call('M.local_asystgrade.init', [$jsdata]);
        } else {
            \core\notification::add(
                'Flask API server is not running. Please check the server status.',
                notification::NOTIFY_ERROR
            );
        }
    }
}

/**
 * Checks if the Flask backend is running.
 *
 * @param string $host The hostname or IP address.
 * @param int $port The port number.
 * @param int $timeout The timeout in seconds.
 * @return bool True if the Flask backend is running, false otherwise.
 */
function is_flask_backend_running(string $host = '127.0.0.1', int $port = 5001, int $timeout = 3): bool {
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);

    if (is_resource($connection)) {
        fclose($connection);
        return true;
    } else {
        // Displaying an error message in Moodle.
        \core\notification::add("Flask backend connection failed: $errstr ($errno)", notification::NOTIFY_ERROR);
        return false;
    }
}
