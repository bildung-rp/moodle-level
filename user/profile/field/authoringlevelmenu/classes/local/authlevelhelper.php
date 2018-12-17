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
 * authoringlevelmenu helper class.
 *
 * @package    profilefield_authoringlevelmenu
 * @copyright  2016 Andreas Wagner, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace profilefield_authoringlevelmenu\local;

defined('MOODLE_INTERNAL') || die();

class authlevelhelper {

    /**
     * @var array list available option per modnametype (similar to pagetype),
     * core items are available for configuration of mod form visibility.
     *
     * Index is named like pagetype but admin settings do not allow "-"
     * so we must use "_" here.
     */
    protected static $modtypes = [
        'core' => ['availability', 'completion', 'tags', 'competencies'],
        'mod_assign_mod' => ['availability', 'feedbacktypes', 'submissionsettings', 'groupsubmissionsettings',
            'notifications', 'modstandardgrade', 'modstandardelshdr'],
        'mod_forum_mod' => ['attachmentswordcounthdr', 'subscriptionandtrackinghdr', 'discussionlocking',
            'blockafterheader', 'modstandardgrade', 'modstandardratings', 'modstandardelshdr'],
        'mod_label_mod' => ['modstandardelshdr'],
        'mod_page_mod' => ['appearancehdr', 'modstandardelshdr'],
        'mod_resource_mod' => ['optionssection', 'modstandardelshdr'],
        'mod_url_mod' => ['optionssection', 'parameterssection', 'modstandardelshdr'],
    ];

    /**
     * Get the available items (activities) to control visiblity in modchooser.
     *
     * @return array list of items indexed by itemtype.
     */
    public static function get_modchooser_items() {
        return get_module_types_names();
    }

    /**
     * Get the available items (blocks)to control visiblity in add block ui.
     *
     * @return array list of items indexed by itemtype.
     */
    public static function get_add_block_ui_items() {

        $blocktypes = array_keys(\core_component::get_plugin_list('block'));

        $rows = array();
        foreach ($blocktypes as $blockname) {
            $rows['block_' . $blockname] = get_string('pluginname', 'block_' . $blockname);
        }
        return $rows;
    }

    /**
     * Get the available items to control visiblity in mod edit form.
     *
     * @return array list of items indexed by modnametype.
     */
    public static function get_mod_form_items() {

        $items = [];
        foreach (self::$modtypes as $modname => $modtypes) {
            $items[$modname] = [];
            foreach ($modtypes as $modtype) {
                $items[$modname][$modname . '_' . $modtype] = get_string($modname . '_' . $modtype, 'profilefield_authoringlevelmenu');
            }
        }
        return $items;
    }

    /**
     * Get the available items to control visiblity in mod edit form for given page type.
     *
     * @return array list of items indexed by modnametype.
     */
    public static function get_mod_form_items_for_pagetype($pagetype) {

        // Admin settings do not allow "-" so we must convert here.
        $settingkey = str_replace('-', '_', $pagetype);

        // Filter modtypes per page type. Always get core items.
        $pagetypemods = array_intersect_key(self::$modtypes, ['core' => 1, $settingkey => 1]);

        $items = [];
        foreach ($pagetypemods as $modname => $modtypes) {
            $items[$modname] = [];
            foreach ($modtypes as $modtype) {
                $items[$modname][$modname . '_' . $modtype] = get_string($modname . '_' . $modtype, 'profilefield_authoringlevelmenu');
            }
        }
        return $items;
    }

    /**
     * Get a menu for the items, that can be visibility controlled by this plugin.
     *
     * @return array list of items indexed by itemtype.
     */
    public static function get_settings_navigation_items() {

        $plugintypes = \core_component::get_plugin_list_with_file('local', '/db/authoringleveltypes.php');

        $leveltypes = array();
        foreach ($plugintypes as $plugintype => $file) {
            include($file);
            $leveltypes = array_merge_recursive($leveltypes, $authoringleveltypes);
        }

        if (!empty($leveltypes['settings'])) {
            return $leveltypes['settings'];
        }
        return array();
    }

    /**
     * Get the current settings for the custom level.
     *
     * @param int $userid
     * @return array moduletypes (assign, quiz...), that user can author.
     */
    public static function get_custom_configuration($userid) {
        global $DB;

        $cache = \cache::make('profilefield_authoringlevelmenu', 'customlevelsettings');

        $authmodules = $cache->get($userid);
        if ($authmodules !== false) {
            return $authmodules;
        }

        $authmodulesrecords = $DB->get_records('profilefield_authlevelmenu', array('userid' => $userid, 'value' => 1), '', 'module');
        $authmodules = array_keys($authmodulesrecords);

        $cache->set($userid, $authmodules);

        return $authmodules;
    }

    /**
     * Saves the data submitted by the settings page.
     *
     * @param object $submitteddata the submitted data of the settings page.
     * @return array result of the saving process
     */
    public static function save_custom_configuration($submitteddata) {
        global $DB;

        // Get submitted moduletypes.
        $submittedcaps = array();
        if (isset($submitteddata->authlevel)) {
            $submittedcaps = array_keys($submitteddata->authlevel);
        }

        // Get all database objects for possible update.
        $alluserdata = array();
        $caps = $DB->get_records('profilefield_authlevelmenu', array('userid' => $submitteddata->userid));
        foreach ($caps as $cap) {
            $alluserdata[$cap->module] = $cap;
        }

        // Get modulenames with cap enalbed.
        $existingcaps = self::get_custom_configuration($submitteddata->userid);

        // Add modules or set value to 1, if necessary.
        $modulestoadd = array_diff($submittedcaps, $existingcaps);
        if (!empty($modulestoadd)) {

            foreach ($modulestoadd as $modulename) {

                if (isset($alluserdata[$modulename])) {

                    $update = $alluserdata[$modulename];
                    $update->value = 1;
                    $DB->update_record('profilefield_authlevelmenu', $update);
                } else {

                    $insert = new \stdClass();
                    $insert->userid = $submitteddata->userid;
                    $insert->module = $modulename;
                    $insert->value = 1;
                    $DB->insert_record('profilefield_authlevelmenu', $insert);
                }
            }
        }

        $modulestodelete = array_diff($existingcaps, $submittedcaps);
        if (!empty($modulestodelete)) {

            foreach ($modulestodelete as $modulename) {

                if (isset($alluserdata[$modulename])) {

                    $update = $alluserdata[$modulename];
                    $update->value = 0;
                    $DB->update_record('profilefield_authlevelmenu', $update);
                }
            }
        }

        // Delete current cache, to refresh the cache next time it is needed.
        $cache = \cache::make('profilefield_authoringlevelmenu', 'customlevelsettings');
        $cache->purge_current_user();

        return array('error' => 0, 'message' => get_string('customlevelsaved', 'profilefield_authoringlevelmenu'));
    }

}
