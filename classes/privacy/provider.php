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

namespace local_asystgrade\privacy;

use coding_exception;
use core_privacy\local\metadata\null_provider;

/**
 * https://moodledev.io/docs/4.5/apis/subsystems/privacy#implementation-requirements
 * GDPR Implementation requirements
 * In order to let Moodle know that you have audited your plugin, and that you do not store
 * any personal user data, you must implement the \core_privacy\local\metadata\null_provider
 * interface in your plugin's provider.
 */

/**
 * Privacy Subsystem implementation for local_asystgrade.
 *
 * @package    local_asystgrade
 */
class provider implements null_provider {

    /**
     * Get the reason why this plugin stores no data.
     *
     * @return string
     * @throws coding_exception
     */
    public static function get_reason(): string {
        return get_string('privacy:metadata', 'local_asystgrade');
    }
}
