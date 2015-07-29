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
 * Keeps track of upgrades to the workshop comments grading strategy
 *
 * @package    workshopform
 * @subpackage comments
 * @copyright  2010 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_workshopform_comments_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2010091700) {
        // clean up orphaned dimensions
        $orphans = $DB->get_records_sql("SELECT d.id
                                           FROM {workshopform_comments} d
                                      LEFT JOIN {workshop} w ON d.workshopid = w.id
                                          WHERE w.id IS NULL");
        if (!empty($orphans)) {
            echo $OUTPUT->notification('Orphaned assessment form elements found - cleaning...');
            $DB->delete_records_list('workshopform_comments', 'id', array_keys($orphans));
        }

        upgrade_plugin_savepoint(true, 2010091700, 'workshopform', 'comments');
    }

    return true;
}
