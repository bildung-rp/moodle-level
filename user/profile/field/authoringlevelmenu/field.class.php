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
 * authoringlevelmenu profile field.
 *
 * @package    profilefield_authoringlevelmenu
 * @copyright  2016 Andreas Wagner, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/profile/field/menu/field.class.php');

class profile_field_authoringlevelmenu extends profile_field_menu {

    const AUTHORING_LEVEL_BEGINNER = 10;
    const AUTHORING_LEVEL_INTERMEDIATE = 20;
    const AUTHORING_LEVEL_ADVANCED = 30;
    const AUTHORING_LEVEL_CUSTOM = 40;

    public static function get_options() {
        return array(
            self::AUTHORING_LEVEL_BEGINNER => get_string('beginner', 'profilefield_authoringlevelmenu'),
            self::AUTHORING_LEVEL_INTERMEDIATE => get_string('intermediate', 'profilefield_authoringlevelmenu'),
            self::AUTHORING_LEVEL_ADVANCED => get_string('advanced', 'profilefield_authoringlevelmenu'),
            self::AUTHORING_LEVEL_CUSTOM => get_string('custom', 'profilefield_authoringlevelmenu')
        );
    }

    /**
     * Constructor method.
     *
     * Pulls out the options for the authoringlevelmenu from the database and sets the the corresponding key for the data if it exists.
     *
     * @param int $fieldid
     * @param int $userid
     */
    public function __construct($fieldid = 0, $userid = 0) {

        // First call parent constructor.
        parent::__construct($fieldid, $userid);

        $this->options = self::get_options();

        // Set the data key.
        if ($this->data !== null) {
            $key = $this->data;
            if (isset($this->options[$key]) || ($key = array_search($key, $this->options)) !== false) {
                $this->data = $key;
                $this->datakey = $key;
            }
        }
    }

    /**
     * Display the data for this field
     *
     * @return string HTML.
     */
    public function display_data() {
        global $USER;

        $options = new stdClass();
        $options->para = false;

        $url = new moodle_url('/user/profile/field/authoringlevelmenu/customleveledit.php', array('userid' => $USER->id));
        $link = html_writer::link($url, get_string('editcustomlevel', 'profilefield_authoringlevelmenu'));

        return format_text($this->options[$this->data], FORMAT_MOODLE, $options)." ($link)";
    }

    /**
     * Create the code snippet for this field instance
     * Overwrites the base class method
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_add($mform) {
        global $USER;

        parent::edit_field_add($mform);

        $userid = optional_param('id', $USER->id, PARAM_INT);

        $url = new moodle_url('/user/profile/field/authoringlevelmenu/customleveledit.php', array('userid' => $userid));
        $mform->addElement('static', 'editcustomlevel', '', html_writer::link($url, get_string('editcustomlevel', 'profilefield_authoringlevelmenu')));
    }
}


