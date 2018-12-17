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
 * Unit tests for local_authoringcapability
 *
 * @package   local_authoringcapability
 * @copyright 2016 Andreas Wagner, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class local_authoringcapability_testcase extends advanced_testcase {

    /*public function test_plugin_installed() {
        $config = get_config('local_authoringcapability');
        $this->assertTrue(isset($config->version));
    }*/

    public function test_importexport() {
        global $CFG;

        require_once($CFG->dirroot.'/course/lib.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        $settingshelper = new \local_authoringcapability\local\settingshelper();

        $moduletypenames = array_keys(get_module_types_names());

        // CSV-File test.
        $name1 = reset($moduletypenames);
        set_config($name1, '10', 'local_authoringcapability');

        $cap = get_config('local_authoringcapability', $name1);
        $this->assertEquals('10', $cap);

        $data = new \stdClass();
        $data->level = array('10' => 1, '20' => 1);
        $data->filetype = 'csv';
        $content = $settingshelper->export($data, false);

        set_config($name1, '10,20,30', 'local_authoringcapability');

        $cap = get_config('local_authoringcapability', $name1);
        $this->assertEquals('10,20,30', $cap);

        $settingshelper->import($content);

        $cap = get_config('local_authoringcapability', $name1);
        $this->assertEquals('10,30', $cap);

        // XML-File test.
        set_config($name1, '10', 'local_authoringcapability');

        $cap = get_config('local_authoringcapability', $name1);
        $this->assertEquals('10', $cap);

        $data = new \stdClass();
        $data->level = array('10' => 1, '20' => 1);
        $data->filetype = 'xml';
        $content = $settingshelper->export($data, false);

        set_config($name1, '10,20,30', 'local_authoringcapability');

        $cap = get_config('local_authoringcapability', $name1);
        $this->assertEquals('10,20,30', $cap);

        $settingshelper->import($content);

        $cap = get_config('local_authoringcapability', $name1);
        $this->assertEquals('10,30', $cap);
    }

}
