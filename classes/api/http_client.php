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

namespace local_asystgrade\api;

use curl;
use Exception;

/**
 * HTTP client class for handling HTTP POST requests.
 */
class http_client implements http_client_interface {
    /**
     * Sends a POST request using Moodle's curl wrapper.
     *
     * @param string $url An endpoint URL.
     * @param array $data The request payload.
     * @return bool|string
     * @throws Exception
     */
    public function post(string $url, array $data): bool|string {
        $curl = new curl();
        $options = [
            'CURLOPT_HTTPHEADER' => ['Content-Type: application/json'],
        ];

        $response = $curl->post($url, json_encode($data), $options);
        $info = $curl->get_info();

        if ($info['http_code'] !== 200) {
            debugging("API Error: HTTP {$info['http_code']} - $response");
            throw new Exception("HTTP request error: {$info['http_code']}");
        }

        return $response;
    }
}
