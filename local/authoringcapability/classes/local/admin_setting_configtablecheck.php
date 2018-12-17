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
 * Admin table for capability configuration
 *
 * @package   local_authoringcapability
 * @copyright 2015 Andreas Wagner, Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_authoringcapability\local;

defined('MOODLE_INTERNAL') || die();

/**
 * This class provides a checkable matrix for the settings this plugin
 */
class admin_setting_configtablecheck extends \admin_setting {

    protected $defaultdescription;
    protected $attributes;

    /**
     * Constructor
     * @param string $name unique ascii name, either 'mysetting' for settings that in config,
     *                     or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised name
     * @param string $description localised long description
     * @param mixed $defaultsetting string or array depending on implementation
     */
    public function __construct($name, $visiblename, $description,
                                $defaultsetting, $defaultdescription, $rows,
                                $columns, $tableattributes) {

        $this->rows = $rows;
        $this->columns = $columns;
        $this->tableattributes = $tableattributes;
        $this->defaultdescription = $defaultdescription;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Returns current value of this setting
     * @return mixed array or string depending on instance, NULL means not set yet
     */
    public function get_setting() {

        $result = $this->config_read($this->name);

        // Ensure, that flag for this config is set, needed to avoid save error.
        if (is_null($result)) {
            return null;
        }

        $setting = array();

        foreach ($this->rows as $rowid => $displayname) {

            $setting[$rowid] = array();

            $result = $this->config_read($rowid);

            if (!isset($result)) {
                $enabled = (isset($this->defaultsetting[$rowid])) ? $this->defaultsetting[$rowid] : array();
            } else {
                $enabled = explode(',', $result);
            }

            foreach ($enabled as $option) {
                $setting[$rowid][$option] = 1;
            }
        }

        return $setting;
    }

    /**
     * Store new setting
     *
     * @param mixed $data string or array, must not be NULL
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($matrix) {

        foreach ($this->rows as $rowid => $displayname) {

            $result = array();
            if (isset($matrix[$rowid])) {
                $data = $matrix[$rowid];
                foreach ($data as $key => $value) {
                    if ($value) {
                        $result[] = $key;
                    }
                }
            }
            $this->config_write($rowid, implode(',', $result)) ? '' : get_string('errorsetting', 'admin');
        }

        // ... set this setting to 1 to avoid save error.
        return $this->config_write($this->name, "1") ? '' : get_string('errorsetting', 'admin');
    }

    /**
     * Return part of form with setting
     * This function should always be overwritten
     *
     * @param mixed $data array or string depending on setting
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {

        $table = new \html_table();
        $table->attributes = $this->tableattributes;

        // ... do table header.
        $datarow = array();
        $datarow[] = '';

        foreach ($this->columns as $column) {
            $cell = new \html_table_cell();
            $cell->style = "text-align:center";
            $cell->text = $column;
            $datarow[] = $cell;
        }
        $table->data[] = $datarow;

        foreach ($this->rows as $rowid => $row) {

            $datarow = array();
            $datarow[] = $row;

            // ... do each column.
            foreach ($this->columns as $colid => $column) {

                $cell = new \html_table_cell();
                $cell->style = "text-align:center";

                $inputparams = array(
                    'type' => 'checkbox',
                    'name' => $this->get_full_name() . '[' . $rowid . '][' . $colid . ']',
                    'value' => 1);

                if (!empty($data[$rowid][$colid])) {
                    $inputparams['checked'] = 'checked';
                }

                $cell->text = \html_writer::empty_tag('input', $inputparams);
                $datarow[] = $cell;
            }
            $table->data[] = $datarow;
        }

        // Hidden element is necessary to call write settings even, if no checkbox is checked.
        $params = array('type' => 'hidden', 'name' => $this->get_full_name().'[installed]', 'value' => 1);
        $hidden = \html_writer::empty_tag('input', $params);

        return format_admin_setting(
                $this, $this->visiblename, \html_writer::table($table).$hidden,
                $this->description, false, '', $this->defaultdescription, $query);
    }

}
