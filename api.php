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
    require_capability('mod/assign:grade', context_system::instance());
} catch (coding_exception | moodle_exception $e) {
    debugging($e->getMessage());
    redirect(
        new moodle_url('/local/asystgrade/error.php'),
        get_string('loginerror', 'local_asystgrade')
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new moodle_exception('invalidmethod', 'local_asystgrade');
}

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

    $response = retry_api_request($apiclient, $data);
    $grades = json_decode($response, true);

    // Check JSON validity.
    if (json_last_error() !== JSON_ERROR_NONE) {
        debugging('JSON decode error: ' . json_last_error_msg());
        throw new moodle_exception('invalidjson', 'local_asystgrade', '', json_last_error_msg());
    } else {
        echo json_encode(['success' => true, 'grades' => $grades]);
    }
} else {
    echo json_encode(['error' => 'No data received']);
}


/**
 * Validates the provided request payload data array.
 *
 * @param  array $data The data to validate.
 * @return array The cleaned data.
 * @throws moodle_exception If the data is invalid.
 */
function validate_data($data): array {
    if (!isset($data['referenceAnswer'], $data['studentAnswers']) || !is_array($data['studentAnswers'])) {
        throw new moodle_exception('invalidrequest', 'local_asystgrade');
    }
    return [
        'referenceAnswer' => clean_param($data['referenceAnswer'], PARAM_TEXT),
        'studentAnswers' => array_map(fn($answer) => clean_param($answer, PARAM_TEXT), $data['studentAnswers']),
    ];
}

/**
 * Retries an API request a specified number of times.
 *
 * @param client $apiclient The API client to use for the request.
 * @param array $payload The data to send in the request.
 * @param int $maxretries The maximum number of retry attempts.
 * @return string|bool The response from the API client.
 * @throws moodle_exception If the API request fails after the maximum retries.
 */
function retry_api_request(client $apiclient, array $payload, int $maxretries = 3): string|bool {
    for ($attempts = 0; $attempts < $maxretries; $attempts++) {
        try {
            return $apiclient->send_data(validate_data($payload));
        } catch (Exception $e) {
            debugging('API request error: ' . $e->getMessage());
            if ($attempts + 1 === $maxretries) {
                throw new moodle_exception('apifailure', 'local_asystgrade');
            }
        }
    }

    return false; // Return false if all retries fail.
}
