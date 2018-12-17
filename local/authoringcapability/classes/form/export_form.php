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
 * Export form.
 *
 * @package   local_authoringcapability
 * @copyright 2016 Andreas Wagner Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authoringcapability\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/lib/formslib.php');

class export_form extends \moodleform {

    protected function definition() {
        global $CFG;

        require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/field.class.php');

        $mform = $this->_form;

        $mform->addElement('header', 'exportsettings', get_string('exportsettings', 'local_authoringcapability'));

        $columns = array();
        $columns[\profile_field_authoringlevelmenu::AUTHORING_LEVEL_BEGINNER] = get_string('beginner', 'profilefield_authoringlevelmenu');
        $columns[\profile_field_authoringlevelmenu::AUTHORING_LEVEL_INTERMEDIATE] = get_string('intermediate', 'profilefield_authoringlevelmenu');
        $columns[\profile_field_authoringlevelmenu::AUTHORING_LEVEL_ADVANCED] = get_string('advanced', 'profilefield_authoringlevelmenu');

        $levels = array();
        foreach ($columns as $levelcode => $leveltext) {
            $levels[] = $mform->createElement('checkbox', $levelcode, '', $leveltext);
        }

        $mform->addGroup($levels, 'level', get_string('levelstoexport', 'local_authoringcapability'));
        $mform->addRule('level', null, 'required', null, 'client');

        $choices = array();
        $choices['csv'] = get_string('csv', 'local_authoringcapability');
        $choices['xml'] = get_string('xml', 'local_authoringcapability');

        $mform->addElement('select', 'filetype', get_string('filetype', 'local_authoringcapability'), $choices);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'export', get_string('export', 'local_authoringcapability'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {

        $errors = array();

        if (empty($data['level'])) {
            $errors['level'] = get_string('levelsrequired', 'local_authoringcapability');
        }

        return $errors;
    }

}
