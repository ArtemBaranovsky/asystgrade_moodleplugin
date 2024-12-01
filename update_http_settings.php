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
 * CLI Script for the local_asystgrade plugin to set HTTP Curl port and domain permissions.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
 * @copyright based on work by 2023 Ulrike Pado <ulrike.pado@hft-stuttgart.de>,
 * @copyright Yunus Eryilmaz & Larissa Kirschner <https://link.springer.com/article/10.1007/s40593-023-00383-w>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define CLI_SCRIPT to indicate this script is being run from the command line.
 */
const CLI_SCRIPT = true;
require('config.php');

global $CFG, $DB;

// Remove 127.0.0.0/8 and localhost from blocked hosts.
$blockedhosts = get_config('core', 'curlsecurityblockedhosts');
$newblockedhosts = str_replace(['127.0.0.0/8', 'localhost'], '', $blockedhosts);
set_config('curlsecurityblockedhosts', trim($newblockedhosts, ','));

// Add 5001 to allowed ports.
$allowedports = get_config('core', 'curlsecurityallowedport');
$newallowedports = $allowedports ? $allowedports . "\r\n5001" : '5001';
set_config('curlsecurityallowedport', $newallowedports);

echo "Settings updated.\n";
