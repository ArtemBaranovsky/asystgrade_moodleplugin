# api.py
# This file is part of Moodle - https://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle. If not, see <https://www.gnu.org/licenses/>.


# The plugin uses the ASYST grading tool <https://transfer.hft-stuttgart.de/gitlab/ulrike.pado/ASYST> modified to work
# as a web endpoint.
#
# @package   local_asystgrade
# @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
# @copyright based on work by 2023 Ulrike Padó <ulrike.pado@hft-stuttgart.de>, Yunus Eryilmaz & Larissa Kirschner
# @copyright <https://link.springer.com/article/10.1007/s40593-023-00383-w>
# @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

from flask import Flask, jsonify, request
import os
import sys

import logging

logging.basicConfig(level=logging.DEBUG)

# Adding a path of module to system path
sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), 'asyst/Source/Skript/german')))

from run_LR_SBERT import process_data

app = Flask(__name__)

@app.route('/api/autograde', methods=['POST'])
def get_data():
    try:
        data = request.get_json()
        app.logger.debug(f"Received data: {data}")
        if not data:
            return jsonify({"error": "No data provided"}), 400

        results = process_data(data)
        app.logger.debug(f"Processed results: {results}")

        return jsonify(results)
    except Exception as e:
        app.logger.error(f"Error during processing: {e}")
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)