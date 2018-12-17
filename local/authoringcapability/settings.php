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
 * Settings Page for local plugin authoringcapability
 *
 * @package   local_authoringcapability
 * @copyright 2016 Andreas Wagner Synergy Learning,
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

use local_authoringcapability\local\admin_setting_configtablecheck as admin_setting_configtablecheck;

require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/field.class.php');

if ($hassiteconfig) {

    $ADMIN->add('localplugins', new admin_category('localauthoringcapabilityfolder',
        new lang_string('pluginname', 'local_authoringcapability')));

    $settings = new admin_settingpage('local_authoringcapability', get_string('pluginname', 'local_authoringcapability'));

    $url = new moodle_url('/local/authoringcapability/admin/export.php');
    $link = html_writer::link($url, get_string('exportsettings', 'local_authoringcapability'));
    $settings->add(new admin_setting_heading('exportauthoringcapability', '', $link));

    $url = new moodle_url('/local/authoringcapability/admin/import.php');
    $link = html_writer::link($url, get_string('importsettings', 'local_authoringcapability'));
    $settings->add(new admin_setting_heading('importauthoringcapability', '', $link));

    $levels = array();
    $levels[profile_field_authoringlevelmenu::AUTHORING_LEVEL_BEGINNER] = get_string('beginner', 'profilefield_authoringlevelmenu');
    $levels[profile_field_authoringlevelmenu::AUTHORING_LEVEL_INTERMEDIATE] = get_string('intermediate', 'profilefield_authoringlevelmenu');
    $levels[profile_field_authoringlevelmenu::AUTHORING_LEVEL_ADVANCED] = get_string('advanced', 'profilefield_authoringlevelmenu');

    $settingshelper = new \local_authoringcapability\local\settingshelper();

    // Items for visibility control in modchooser.
    $modchooseritems = $settingshelper->get_modchooser_items();

    $defaultsetting = $settingshelper->get_default_settings(array_keys($modchooseritems));
    $defaultdescription = new lang_string('authoringleveldefaultdesc', 'local_authoringcapability');

    $settings->add(new admin_setting_configtablecheck('local_authoringcapability/authoringlevel',
            new lang_string('modchooserauthoringlevel', 'local_authoringcapability'),
            '', $defaultsetting, $defaultdescription, $modchooseritems, $levels, array('style' => 'width:auto')));

    // Items for visibility control in block_add_block_ui.
    $blockuiitems = $settingshelper->get_add_block_ui_items();

    $defaultsetting = $settingshelper->get_default_settings(array_keys($blockuiitems));
    $defaultdescription = new lang_string('authoringleveldefaultdesc', 'local_authoringcapability');

    $settings->add(new admin_setting_configtablecheck('local_authoringcapability/blockuiauthoringlevel',
            new lang_string('blockuiauthoringlevel', 'local_authoringcapability'),
            '', $defaultsetting, $defaultdescription, $blockuiitems, $levels, array('style' => 'width:auto')));

    // Items for visibility control in mod_form.
    $modformitems = $settingshelper->get_mod_form_items();

    foreach ($modformitems as $modname => $itemspermod) {

        $defaultsetting = $settingshelper->get_default_settings(array_keys($itemspermod));
        $defaultdescription = new lang_string('authoringleveldefaultdesc', 'local_authoringcapability', $modname);

        $settings->add(new admin_setting_configtablecheck('local_authoringcapability/modformauthoringlevel'.$modname,
            new lang_string('modformauthoringlevel'. $modname, 'local_authoringcapability'),
            '', $defaultsetting, $defaultdescription, $itemspermod, $levels, array('style' => 'width:auto')));
    }

    // Items for visibility control in settings navigation.
    $settingsnavitems = $settingshelper->get_settings_navigation_items();

    $defaultsetting = $settingshelper->get_default_settings(array_keys($settingsnavitems));
    $defaultdescription = new lang_string('authoringleveldefaultdesc', 'local_authoringcapability');

    $settings->add(new admin_setting_configtablecheck('local_authoringcapability/settingsnavauthoringlevel',
            new lang_string('settingsnavauthoringlevel', 'local_authoringcapability'),
            '', $defaultsetting, $defaultdescription, $settingsnavitems, $levels, array('style' => 'width:auto')));

    $ADMIN->add('localauthoringcapabilityfolder', $settings);

    // Export and Import pages.
    $ADMIN->add('localauthoringcapabilityfolder',
                new admin_externalpage('authoringcapabilityexportsettings',
                new lang_string('exportsettings', 'local_authoringcapability'),
                "$CFG->wwwroot/local/authoringcapability/admin/export.php", 'moodle/site:config'));

    $ADMIN->add('localauthoringcapabilityfolder',
                new admin_externalpage('authoringcapabilityimportsettings',
                new lang_string('importsettings', 'local_authoringcapability'),
                "$CFG->wwwroot/local/authoringcapability/admin/import.php", 'moodle/site:config'));


    $settings = null;
}