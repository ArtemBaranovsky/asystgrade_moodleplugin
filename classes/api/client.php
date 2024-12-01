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

use Exception;
use local_asystgrade\api\http_client_interface;

/**
 * Client class for handling HTTP requests to Flask ML backend.
 *
 * This class provides methods for sending data to a specified endpoint using
 * an HTTP client interface.
 * @package   local_asystgrade
 */
class client {


    /** @var ?client This variable holds an object type for http_client */
    private static ?client $instance = null;

    /**
     * Client class for handling HTTP requests to Flask ML backend.
     * @param string $endpoint This variable holds a domain or IP to attached flask ML backend
     * @param \local_asystgrade\api\http_client_interface $httpclient This variable holds an interface for http_client
     */
    private function __construct(
        /**
         * @var string $endpoint This variable holds a domain or IP to attached flask ML backend
         */
        private string                $endpoint,
        /**
         * @var \local_asystgrade\api\http_client_interface $httpclient This variable holds an interface for http_client
         */
        private http_client_interface $httpclient
    ) {
    }

    /**
     * Returns the singleton instance of the client.
     *
     * @param string $endpoint
     * @param http_client_interface $httpclient
     * @return client
     */
    public static function getinstance(string $endpoint, http_client_interface $httpclient): client {
        if (self::$instance === null) {
            self::$instance = new client($endpoint, $httpclient);
        }
        return self::$instance;
    }

    /**
     * Sends data to the endpoint.
     *
     * @param array $data
     * @return bool|string
     * @throws Exception
     */
    public function send_data(array $data): bool|string {
        try {
            return $this->httpclient->post($this->endpoint, $data);
        } catch (Exception $e) {
            throw new Exception('HTTP request error: ' . $e->getMessage());
        }
    }
}
