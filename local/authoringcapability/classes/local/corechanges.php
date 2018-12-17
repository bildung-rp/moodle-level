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
 * Class, that handles export and import of the settings.
 *
 * @package   local_authoringcapability
 * @copyright 2016 Andreas Wagner, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authoringcapability\local;

defined('MOODLE_INTERNAL') || die();

use \profilefield_authoringlevelmenu\local\authlevelhelper;

require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/field.class.php');

class corechanges {

    /**
     * Get the authoring settings for all levels.
     *
     * @return type
     */
    private static function get_authoringsettings() {

        $settings = get_config('local_authoringcapability');
        $parsedsettings = array();

        foreach ($settings as $key => $setting) {
            $parsedsettings[$key] = explode(',', $setting);
        }

        return $parsedsettings;
    }

    /**
     * Get the authoring level of the current user
     *
     * @return int one of the levels defined in profie_field_authoringlevenmenu.
     */
    private static function get_authoringlevel() {
        global $USER;

        if (isset($USER->profile['authoringcapability'])) {
            return $USER->profile['authoringcapability'];
        } else {
            return \profile_field_authoringlevelmenu::AUTHORING_LEVEL_ADVANCED;
        }
    }

    /**
     * Remove plugins depending on users level configuration.
     *
     * @param array plugins
     * @return array filtered plugins array
     */
    protected static function filter_plugins($plugins, $prefix = '') {
        global $USER;

        $authoringlevel = self::get_authoringlevel();

        $allowedplugins = array();
        if ($authoringlevel == \profile_field_authoringlevelmenu::AUTHORING_LEVEL_CUSTOM) {

            // TODO: cache custom configuration for user in session cache.
            $settings = array_flip(authlevelhelper::get_custom_configuration($USER->id));

            foreach ($plugins as $key => $pluginname) {
                if (isset($settings[$prefix . $key])) {
                    $allowedplugins[$key] = $pluginname;
                }
            }
        } else {

            $settings = self::get_authoringsettings();

            foreach ($plugins as $key => $activity) {
                if (!empty($settings[$prefix . $key]) && in_array($authoringlevel, $settings[$prefix . $key])) {
                    $allowedplugins[$key] = $activity;
                }
            }
        }
        return $allowedplugins;
    }

    /**
     * Remove the non visible activities from mod chooser.
     *
     * @param type $activities
     * @return type
     */
    public static function hide_mod_chooser_items($activities) {
        return self::filter_plugins($activities);
    }

    /**
     * Generate the content of block_admin (mainly taken from block_add_block_ui)
     *
     * @param moodle_page $page
     * @param block_content $bc
     * @return string the content of the block_ui block.
     */
    public static function hide_block_ui_items($page, $bc) {
        global $OUTPUT;

        $missingblocks = $page->blocks->get_addable_blocks();
        if (empty($missingblocks)) {
            $bc->content = get_string('noblockstoaddhere');
            return $bc->content;
        }

        $menu = array();
        foreach ($missingblocks as $block) {
            $blockobject = \block_instance($block->name);
            if ($blockobject !== false && $blockobject->user_can_addto($page)) {
                $menu[$block->name] = $blockobject->get_title();
            }
        }

        // SYNERGY-LEARNING: filtering the blocks depending on settings.
        $menu = self::filter_plugins($menu, 'block_');
        // SYNERGY-LEARNING: filtering the blocks depending on settings.

        if (empty($menu)) {
            return get_string('noblockstoaddhere');
        }

        \core_collator::asort($menu);

        $actionurl = new \moodle_url($page->url, array('sesskey' => sesskey()));
        $select = new \single_select($actionurl, 'bui_addblock', $menu, null, array('' => get_string('adddots')), 'add_block');
        $select->set_label(get_string('addblock'), array('class' => 'accesshide'));
        $bc->content = $OUTPUT->render($select);

        return $bc->content;
    }

    /**
     * Set additional body clases to hide elements via css (see styles.css)
     *
     * @param moodle_page $page
     */
    public static function hide_mod_form_items($page) {

        // Get all form items available for this page.
        $modformitems = authlevelhelper::get_mod_form_items_for_pagetype($page->pagetype);

        foreach ($modformitems as $modformtypes) {

            // Get allowed section types (whitelist);
            $showtypes = self::filter_plugins($modformtypes);
            // Get type that should be hidden (=all available type except type to show).
            $hidetypes = array_diff_key($modformtypes, $showtypes);

            foreach ($hidetypes as $hidetype => $unused) {
                $page->add_body_class('hide-' . $hidetype);
            }
        }
    }

    /**
     * Remove the non visible navigationitem from settings navigation, if possible.
     *
     * @param settings_navigation $navigation
     */
    public static function hide_settings_navigation_items(\settings_navigation $navigation) {

        $node = $navigation->get('courseadmin');

        if ($node) {

            $settingkeys = authlevelhelper::get_settings_navigation_items();
            $showsettingkeys = self::filter_plugins($settingkeys);
            $hidesettingkeys = array_diff_key($settingkeys, $showsettingkeys);

            foreach ($hidesettingkeys as $hidesettingkey => $unused) {

                $aclnode = $node->get($hidesettingkey);

                if ($aclnode) {
                    $aclnode->remove();
                }
            }
        }
    }

    /**
     * Render the instruction text including the info of local_authoringcapability settings
     * for the modchooser.
     *
     * @return \lang_string
     */
    public static function render_modchooser_informationtext() {
        global $USER;

        $url = new \moodle_url('/user/edit.php', ['id' => $USER->id]);
        $link = \html_writer::link($url, get_string('editprofile', 'local_authoringcapability'));

        $a = (object) [
                'orginfo' => get_string('selectmoduletoviewhelp'),
                'link' => $link
        ];

        return new \lang_string('modchooserauthoringlevelinfo', 'local_authoringcapability', $a);
    }

}
