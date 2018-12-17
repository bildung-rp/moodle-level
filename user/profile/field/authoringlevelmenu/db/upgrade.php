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
 * authoringlevelmenu profile field upgrade.
 *
 * @package    profilefield_authoringlevelmenu
 * @copyright  2016 Andreas Wagner, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_profilefield_authoringlevelmenu_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016011002) {

        // Define table profilefield_authlevelmenu to be created.
        $table = new xmldb_table('profilefield_authlevelmenu');

        // Adding fields to table profilefield_authlevelmenu.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('module', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table profilefield_authlevelmenu.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table profilefield_authlevelmenu.
        $table->add_index('idx_usr_mod', XMLDB_INDEX_NOTUNIQUE, array('userid', 'module'));

        // Conditionally launch create table for profilefield_authlevelmenu.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Authoringlevelmenu savepoint reached.
        upgrade_plugin_savepoint(true, 2016011002, 'profilefield', 'authoringlevelmenu');
    }

    return true;
}
