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
 * authoringlevelmenu profile field .
 *
 * @package    profilefield_authoringlevelmenu
 * @copyright  2016 Andreas Wagner, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace profilefield_authoringlevelmenu\local;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class customleveledit_form extends \moodleform {

    protected function definition() {

        $mform = $this->_form;

        $configmodules = authlevelhelper::get_custom_configuration($this->_customdata['userid']);

        $mform->addElement('html', get_string('settingsforcustomleveldesc', 'profilefield_authoringlevelmenu'));

        // Items for visibility control in mod chooser.
        $mform->addElement('header', 'modchooservisibility', get_string('modchooservisibility', 'profilefield_authoringlevelmenu'));
        $modformitems = authlevelhelper::get_modchooser_items();

        foreach ($modformitems as $moduletype => $modulename) {
            $mform->addElement('checkbox', "authlevel[{$moduletype}]", $modulename);
            $mform->setDefault("authlevel[{$moduletype}]", in_array($moduletype, $configmodules));
        }

        // Items for visibility control in add block ui.
        $mform->addElement('header', 'blockuivisibility', get_string('blockuivisibility', 'profilefield_authoringlevelmenu'));
        $blocktypes = authlevelhelper::get_add_block_ui_items();

        foreach ($blocktypes as $blocktype => $blockname) {
            $mform->addElement('checkbox', "authlevel[{$blocktype}]", $blockname);
            $mform->setDefault("authlevel[{$blocktype}]", in_array($blocktype, $configmodules));
        }

        // Items for visibility control in modform.
        $modformitems = authlevelhelper::get_mod_form_items();
        foreach ($modformitems as $modtype => $coretypes) {

            $mform->addElement('header', 'modformvisibility'.$modtype,
                get_string('modformvisibility_'.$modtype, 'profilefield_authoringlevelmenu'));

            foreach ($coretypes as $coretype => $corename) {
                $mform->addElement('checkbox', "authlevel[{$coretype}]", $corename);
                $mform->setDefault("authlevel[{$coretype}]", in_array($coretype, $configmodules));
            }
        }

        // Items for visibility control in settingsnavigation.
        $navtypes = authlevelhelper::get_settings_navigation_items();

        if (!empty($navtypes)) {

            $mform->addElement('header', 'settingsnavvisibility', get_string('settingsnavvisibility', 'profilefield_authoringlevelmenu'));

            foreach ($navtypes as $navtype => $navname) {
                $mform->addElement('checkbox', "authlevel[{$navtype}]", $navname);
                $mform->setDefault("authlevel[{$navtype}]", in_array($navtype, $configmodules));
            }
        }

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', $this->_customdata['userid']);

        $this->add_action_buttons(true, get_string('submit'));
    }

}
