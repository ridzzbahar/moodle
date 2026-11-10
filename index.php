<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Throw an exception when users try to access index.php outside of the web root.
 *
 * @package    core
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Auto redirect to CBT Application (E-Learning SMKN 1 Cibinong & CBT Portal)
if (file_exists(__DIR__ . '/cbt/index.php')) {
    header("Location: cbt/");
    exit;
}

if (file_exists(__DIR__ . '/config.php')) {
    require_once('config.php');
    throw new \core\exception\moodle_exception('rootdirpublic', 'error');
}

if (file_exists(__DIR__ . '/public/index.php')) {
    header("Location: public/");
    exit;
}

