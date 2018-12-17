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
 * Export page.
 *
 * @package   local_authoringcapability
 * @copyright 2016 Andreas Wagner Synergy Learning,
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../../config.php');

global $CFG, $PAGE, $OUTPUT;

require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('authoringcapabilityexportsettings', '', null, '', array('pagelayout' => 'admin'));

$baseurl = new moodle_url('/local/authoringcapability/admin/export.php');
$PAGE->set_url($baseurl);

$exportform = new \local_authoringcapability\form\export_form($baseurl);

// Get data.
if ($data = $exportform->get_data()) {
    $settingshelper = \local_authoringcapability\local\settingshelper::get_instance();
    $settingshelper->export($data);
}

$PAGE->set_heading(get_string('exportsettings', 'local_authoringcapability'));
$PAGE->set_title(get_string('exportsettings', 'local_authoringcapability'));

echo $OUTPUT->header();
$exportform->display();
echo $OUTPUT->footer();
