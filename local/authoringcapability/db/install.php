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
 * Installation DB changes
 *
 * @package   local_authoringcapability
 * @copyright 2015 Andrew Hancox, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_authoringcapability_install() {
    global $CFG, $DB, $USER;

    // Install profile field, when theris not at least one already installed.
    $profilefieldexists = $DB->get_record('user_info_field', array('datatype' => 'authoringlevelmenu'));

    if (!$profilefieldexists) {
        require_once($CFG->dirroot . '/user/profile/definelib.php');
        require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/define.class.php');
        require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/field.class.php');
        require_once($CFG->dirroot . '/user/profile/lib.php');

        // Check that we have at least one category defined.
        $categories = $DB->get_records('user_info_category', null, 'sortorder ASC');
        if (empty($categories)) {
            $defaultcategory = new stdClass();
            $defaultcategory->name = get_string('profiledefaultcategory', 'admin');
            $defaultcategory->sortorder = 1;
            $DB->insert_record('user_info_category', $defaultcategory);
        }

        $data = new stdClass();
        $data->defaultdata = profile_field_authoringlevelmenu::AUTHORING_LEVEL_ADVANCED;
        $data->categoryid = 1;
        $data->visible = PROFILE_VISIBLE_PRIVATE;
        $data->signup = 0;
        $data->forceunique = 0;
        $data->locked = 0;
        $data->required = 1;
        $data->description = get_string('levelprofilefield:description', 'local_authoringcapability');
        $data->name = get_string('levelprofilefield:name', 'local_authoringcapability');
        $data->shortname = 'authoringcapability';
        $data->datatype = 'authoringlevelmenu';
        $data->descriptionformat = FORMAT_HTML;

        $formfield = new profile_define_authoringlevelmenu();
        $formfield->define_save($data);

        // Save profiledata for this user, to continue installation process.
        $USER->profile_field_authoringcapability = \profile_field_authoringlevelmenu::AUTHORING_LEVEL_ADVANCED;
        profile_save_data($USER);
    }

    return true;
}
