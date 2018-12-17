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

class import_form extends \moodleform {

    protected function definition() {
        global $CFG;

        require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/field.class.php');

        $mform = $this->_form;

        $mform->addElement('header', 'importsettings', get_string('importsettings', 'local_authoringcapability'));

        $filemanageroptions = array(
            'accepted_types' => array('xml', 'csv')
        );

        $mform->addElement('filepicker', 'settingsfile', get_string('settingsfile', 'local_authoringcapability'), '', $filemanageroptions);
        $mform->addRule('settingsfile', null, 'required', null, 'client');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'import', get_string('import', 'local_authoringcapability'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {

        $errors = array();

        if (empty($data['settingsfile'])) {
            $errors['settingsfile'] = get_string('filerequired', 'local_authoringcapability');
        }

        return $errors;
    }

}
