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
 * This is an api endpoint that sends a request to a local container with a flask-based server.
 * The plugin uses the ASYST grading tool <https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST>
 * modified to work as a web endpoint.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
 * @copyright based on work by 2023 Ulrike Pado <ulrike.pado@hft-stuttgart.de>,
 * @copyright Yunus Eryilmaz & Larissa Kirschner <https://link.springer.com/article/10.1007/s40593-023-00383-w>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once('lib.php');

use local_asystgrade\api\client;
use local_asystgrade\api\http_client;
use local_asystgrade\utils;

try {
    require_login();
} catch (coding_exception | moodle_exception $e) {
    debugging($e->getMessage());
    redirect(
        new moodle_url('/local/asystgrade/error.php'),
        get_string('loginerror', 'local_asystgrade')
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data) {
        // Preparing Flask API.
        try {
            $apiendpoint = utils::get_api_endpoint();
        } catch (dml_exception $e) {
            debugging('Failed to get API endpoint setting: ' . $e->getMessage());
        }

        $httpclient = new http_client();
        $apiclient = client::getInstance($apiendpoint, $httpclient);

        $maxretries = 3;
        $attempts = 0;
        $success = false;

        while ($attempts < $maxretries && !$success) {
            try {
                // Sending data to Flask and obtaining an answer.
                $response = $apiclient->send_data($data);
                $success = true;
            } catch (Exception $e) {
                $attempts++;
                debugging('API request error: ' . $e->getMessage());
                if ($attempts >= $maxretries) {
                    echo json_encode(['error' => 'A server error occurred. Please try again later.']);
                    exit; // Ensure to stop further processing.
                }
            }
        }

        if ($success) {
            $grades = json_decode($response, true);

            // Check JSON validity.
            if (json_last_error() === JSON_ERROR_NONE) {
                echo json_encode(['success' => true, 'grades' => $grades]);
            } else {
                debugging('JSON decode error: ' . json_last_error_msg());
                echo json_encode(['error' => 'Invalid JSON from Flask API']);
            }
        }
    } else {
        echo json_encode(['error' => 'No data received']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}
