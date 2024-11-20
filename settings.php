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
 * Settings for the local_asystgrade plugin.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
 * @copyright based on work by 2023 Ulrike Pado <ulrike.pado@hft-stuttgart.de>,
 * @copyright Yunus Eryilmaz & Larissa Kirschner <https://link.springer.com/article/10.1007/s40593-023-00383-w>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use local_asystgrade\utils;

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $ADMIN;
    // Ensure the settings page is created under the correct location in the site admin menu.
    $ADMIN->fulltree = true;

    // Create a new settings page for your plugin.
    $settings = new admin_settingpage('local_asystgrade', get_string('pluginname', 'local_asystgrade'));

    // Add the settings page to the admin tree.
    $ADMIN->add('localplugins', $settings);

    // Add your settings here.
    try {
        $settings->add(new admin_setting_configtext(
            'local_asystgrade/apiendpoint',
            get_string('apiendpoint', 'local_asystgrade'),
            get_string('apiendpoint_desc', 'local_asystgrade'),
            utils::get_api_endpoint(),
            PARAM_URL
        ));
    } catch (coding_exception $e) {
        // Exception intentionally ignored because the setting might already exist or might not be critical.
        debugging('Failed to add API endpoint setting: ' . $e->getMessage());
    } catch (dml_exception $e) {
        debugging('Failed to get API endpoint setting: ' . $e->getMessage());
    }
}
