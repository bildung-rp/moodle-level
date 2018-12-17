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

class settingshelper {

    // Known columns, that will be accepted for import, 1 means data is required.
    protected static $knowncolumns = array(
        "plugin" => 1, "level_10" => 0, "level_20" => 0, "level_30" => 0
    );
    protected static $installfilenames = array('settings-install.csv', 'settings-install.xml');
    protected $errors = array();
    protected $log = array();
    // Hold all plugintypes, for which settings may be imported.
    protected $importableplugintypes;
    protected $defaultsettings;

    /**
     * Get all the items (i. e. activities) that can be hidden in modchooser.
     *
     * @return array list of module names indexed by module type (= key for settings in plugin).
     */
    public function get_modchooser_items() {
        return authlevelhelper::get_modchooser_items();
    }

    public function get_add_block_ui_items() {
        return authlevelhelper::get_add_block_ui_items();
    }

    public function get_mod_form_items() {
        return authlevelhelper::get_mod_form_items();
    }

    /**
     * Get all config keys controlling the mod form visibility for export and import.
     *
     * @return array
     */
    public function get_mod_form_items_config_keys() {

        $modformitems = authlevelhelper::get_mod_form_items();

        $configkeys = [];
        foreach ($modformitems as $itemspertype) {
            $configkeys = array_merge($configkeys, array_keys($itemspertype));
        }

        return array_fill_keys($configkeys, 1);
    }

    public function get_settings_navigation_items() {
        return authlevelhelper::get_settings_navigation_items();
    }

    /**
     * Read settings for given plugin types and required levelkeys (constants of profile_field_authoringlevelmenu).
     *
     * The values of the plugin configuration is converted into an array, for example:
     *
     * levelkeys: (array(10,20,30))
     * Plugin-Config: name => "assign", value = "10,20"
     *
     * Result: (array('plugin' => "assign", '10' => 1, '20' => 1, '30' => 0))
     *
     * @param array $plugintypes list of plugin types (assign, choice, ... ,block_admin, ....)
     * @param array $levelkeys list of level constants
     * @param string $prefix frankenstyle prefix of plugin (i. e. "block_")
     * @return array list of settings
     */
    private function read_plugin_settings($plugintypes, $levelkeys, $prefix = '') {

        $rows = array();
        $config = get_config('local_authoringcapability');

        foreach ($plugintypes as $plugintype) {

            $pluginname = $prefix . $plugintype;

            $row = array();
            $row['plugin'] = $pluginname;

            $levelvalues = array();
            if (!empty($config->$pluginname)) {
                $levelvalues = explode(',', $config->$pluginname);
            }

            foreach ($levelkeys as $levelkey) {
                $row[$levelkey] = (int) (in_array($levelkey, $levelvalues));
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Read all the level settings of this plugin for required level keys
     * (i. e. constants of profile_field_authoringlevelmenu)
     *
     * @param array $levelkeys the level keys to retrieve the settings for
     *
     * @return array list of settings records.
     */
    private function read_settings($levelkeys) {

        $rows = array();

        // Get first line.
        $rows['header'] = array('plugin');
        foreach ($levelkeys as $levelkey) {
            $rows['header'][] = 'level_' . $levelkey;
        }

        // Read the current settings for all plugintypes.
        $exportableplugintypes = $this->get_importable_plugin_types();

        $settings = $this->read_plugin_settings($exportableplugintypes, $levelkeys);
        $rows = array_merge($rows, $settings);

        return $rows;
    }

    /**
     * Export the settings as CSV - File
     *
     * @param object $data data submitted by export form contains the level codes to export.
     * @param boolean $download download the file
     * @return string content of the file for unit test.
     */
    private function export_csv($data, $download) {
        global $CFG;

        require_once($CFG->libdir . '/csvlib.class.php');

        $csvwriter = new \csv_export_writer();
        $csvwriter->set_filename('settings-authoringcapability');

        $levelkeys = array_keys($data->level);
        $rows = $this->read_settings($levelkeys);

        foreach ($rows as $row) {
            $csvwriter->add_data($row);
        }

        if ($download) {
            $csvwriter->download_file();
            die;
        }

        return $csvwriter->print_csv_data(true);
    }

    /**
     * Export settings as XML File.
     *
     * @param object $data data submitted by export form contains the level codes to export.
     */
    private function export_xml($data, $download) {

        $levelkeys = array_keys($data->level);
        $rows = $this->read_settings($levelkeys);

        $xml = new \DOMDocument();

        // Remove the header line.
        array_shift($rows);

        $plugins = $xml->createElement('plugins');
        $xml->appendchild($plugins);

        foreach ($rows as $row) {

            $plugin = $xml->createElement($row['plugin']);

            foreach ($levelkeys as $levelkey) {
                $level = $xml->createElement('level_' . $levelkey, $row[$levelkey]);
                $plugin->appendchild($level);
            }

            $plugins->appendChild($plugin);
        }

        if (!$download) {
            return $xml->saveXML();
        }

        ob_clean();

        if (is_https()) { // HTTPS sites - watch out for IE! KB812935 and KB316431.
            header('Cache-Control: max-age=10');
            header('Pragma: ');
        } else {
            header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
        }
        header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header("Content-Type: text/xml\n");
        header("Content-Disposition: attachment; filename=\"settings-authoringcapability-" . time() . ".xml\"");

        echo $xml->saveXML();
        die;
    }

    /**
     * Export the settings according to submitted data from export form.
     *
     * @param object $data submitted data from export form.
     */
    public function export($data, $download = true) {

        switch ($data->filetype) {

            case 'csv' :
                return $this->export_csv($data, $download);

            case 'xml' :
                return $this->export_xml($data, $download);

            default :
                print_error('unknown filetype');
        }
    }

    /**
     * Collect all the plugins of this instance to import capability settings.
     *
     * @return array list of plugintype names (assign, choice, ... ,block_admin, ....)
     */
    private function get_importable_plugin_types() {

        if (!isset($this->importableplugintypes)) {

            // Get modchooser items.
            $importableitems = $this->get_modchooser_items();

            // Get blocks ui items.
            $importableitems = array_merge($importableitems, $this->get_add_block_ui_items());

            // Get mod form items.
            $importableitems = array_merge($importableitems, $this->get_mod_form_items_config_keys());

            // Get settings nav items.
            $importableitems = array_merge($importableitems, $this->get_settings_navigation_items());

            if (!empty($importableitems)) {
                $this->importableplugintypes = array_keys($importableitems);
            }
        }

        return $this->importableplugintypes;
    }

    /**
     * Write the setting for one plugintype.
     *
     * Note that only the levelkeys given in $record are overwritten.
     * It is therefore possible to import only one level.
     *
     * @param object $record records that contains the plugintype name and
     * the settings to write.
     * @return boolean true, when setting is written.
     */
    private function write_setting($record) {

        $importplugintypes = $this->get_importable_plugin_types();

        if (!in_array($record->plugin, $importplugintypes)) {
            return false;
        }

        // Get the setting in an assoziative array.
        $oldsettings = array();
        if ($settingsstr = get_config('local_authoringcapability', $record->plugin)) {

            $levelkeys = explode(',', $settingsstr);

            foreach ($levelkeys as $levelkey) {
                $oldsettings[$levelkey] = 1;
            }
        }

        // Get new settings from read record.
        $newsettings = array();
        foreach ($record as $key => $value) {

            $matches = array();
            if (preg_match('/level_([0-9]*)/', $key, $matches)) {
                $newsettings[$matches[1]] = $value;
            }
        }

        // Write settings.
        foreach ($newsettings as $levelkey => $newsetting) {
            if ($newsetting == 0) {
                unset($oldsettings[$levelkey]);
            } else {
                $oldsettings[$levelkey] = $newsetting;
            }
        }

        set_config($record->plugin, implode(',', array_keys($oldsettings)), 'local_authoringcapability');
        return true;
    }

    /**
     * Report an error.
     *
     * @param type $error
     */
    private function report_error($error) {
        $this->errors[] = $error;
    }

    /**
     * Log an event.
     *
     * @param type $msg
     */
    private function log($msg) {
        $this->log[] = $msg;
    }

    /**
     * Validate the columns of the given csv.
     *
     * @param array $columns the columnames in lowercase.
     * @return boolean
     */
    private function csv_column_validation($columns) {

        $unknowncolumns = array();
        $knowncolumkeys = array_keys(self::$knowncolumns);

        foreach ($columns as $column) {

            if (!in_array(strtolower($column), $knowncolumkeys)) {
                $unknowncolumns[] = $column;
            }
        }

        if (!empty($unknowncolumns)) {
            $error = 'unknown columns detected: ' . implode(',', $unknowncolumns);
            $this->report_error($error);
            return false;
        }

        return true;
    }

    /**
     * Get the line error using external helper class. Unfortunately we must parse
     * the whole csv again.
     *
     * @param string $content
     * @param string $encoding
     * @param string $delimitername
     * @return array result array.
     */
    protected function get_line_error($content, $encoding, $delimitername) {

        $iid = csv_analyzer::get_new_iid('importsettings');
        $cir = new csv_analyzer($iid, 'importsettings');

        if (!$result = $cir->load_csv_content($content, $encoding, $delimitername)) {

            $result = $cir->get_error();

            if (isset($result['error'])) {
                return $result['line'] . "/ data: " . $result['data'];
            }
        }
        return $result;
    }

    /**
     * Import the settings from xml file.
     *
     * @param string $content content of submitted file.
     * @return boolean true, if import was successful
     */
    private function read_xml($content) {

        $xml = new \DOMDocument();

        // Do some cleaning: trim trailing commas.
        $content = preg_replace('!\r\n?!', "\n", $content);
        $content = trim($content) . "\n";

        $xml->loadXML($content);

        $plugins = $xml->getElementsByTagName('plugins');

        $records = array();
        foreach ($plugins as $plugin) {

            foreach ($plugin->childNodes as $plugin) {

                $record = array('plugin' => $plugin->nodeName);

                foreach ($plugin->childNodes as $level) {
                    $record[$level->nodeName] = $level->textContent;
                }

                $records[] = (object) $record;
            }
        }

        return $records;
    }

    /**
     * Import the settings from csv file.
     *
     * @param string $content content of submitted file.
     * @param string $encoding encoding of submitted file.
     * @param string $delimitername delimiter of file.
     * @return boolean true, if import was successful
     */
    private function read_csv($content, $encoding = 'utf-8', $delimitername = 'comma') {
        global $CFG;

        require_once($CFG->libdir . '/csvlib.class.php');

        // Do some cleaning: trim trailing commas.
        $content = preg_replace('!\r\n?!', "\n", $content);
        $content = trim($content) . "\n";

        $iid = \csv_import_reader::get_new_iid('importsettings');
        $cir = new \csv_import_reader($iid, 'importsettings');

        $total = $cir->load_csv_content($content, $encoding, $delimitername) - 1;

        $csvloaderror = $cir->get_error();

        if (!is_null($csvloaderror)) {

            // Sorry, but csv_import_reader does not report line number, so we use a modified copy of importer.
            $this->report_error(self::get_line_error($content, $encoding, $delimitername));
            $cir->close();
            $cir->cleanup(true);
            return false;
        }

        unset($content);

        // Get columns in lower case and validate them.
        $lowercolumns = array();
        foreach ($cir->get_columns() as $column) {
            $lowercolumns[] = strtolower($column);
        }

        if (!$this->csv_column_validation($lowercolumns)) {
            $cir->close();
            $cir->cleanup(true);
            return false;
        }

        // Do the import from temp csv file.
        $cir->init();

        $records = array();
        while ($line = $cir->next()) {
            $records[] = (object) array_combine($lowercolumns, $line);
        }

        $cir->close();
        $cir->cleanup(true);

        return $records;
    }

    /**
     * Read the level settings from file content string.
     *
     * Example structure of a level settings records:
     *
     * array('plugin' => pluginname, 'level_10' => 1, 'level_20' => 0)
     *
     * Note that a level records may not contain values for all level contants.
     *
     * @param string $content
     * @return array list of settings record
     */
    private function get_settings_from_filecontent($content) {

        if (strpos($content, '<?xml version="1.0"?>') !== false) {
            $data = $this->read_xml($content);
        } else {
            $data = $this->read_csv($content);
        }

        return $data;
    }

    /**
     * Try to import the submitted file in csv or xml format.
     *
     * @param object $data the submitted data.
     * @param object $mform the import form
     * @return array result informations.
     */
    public function import($content) {

        $success = false;
        $total = 0;
        $countimported = 0;

        $newsettings = $this->get_settings_from_filecontent($content);
        if ($newsettings) {

            foreach ($newsettings as $newsetting) {
                if ($this->write_setting($newsetting)) {
                    $countimported++;
                }
                $total++;
            }
            $this->log("Passed - read total: {$total}, imported: {$countimported}");
            $success = true;
        }

        return array('success' => $success, 'errors' => $this->errors, 'log' => $this->log);
    }

    /**
     * Get a instance of a settingshelper.
     *
     * @staticvar settingshelper $settingshelper
     * @return settingshelper
     */
    public static function get_instance() {
        static $settingshelper;

        if (isset($settingshelper)) {
            return $settingshelper;
        }

        $settingshelper = new settingshelper();
        return $settingshelper;
    }

    /**
     * Get default settings when settings files are available.
     *
     * @return array list of settings for each itemtype (array(itemtype => array(10 => 1));
     */
    private function get_default_settings_from_files() {
        global $CFG;

        if (isset($this->defaultsettings)) {
            return $this->defaultsettings;
        }

        $settings = array();

        // Check settings-files for install.
        foreach (self::$installfilenames as $filename) {

            if (file_exists($CFG->dirroot . '/local/authoringcapability/db/installsettings/' . $filename)) {
                $content = file_get_contents($CFG->dirroot . '/local/authoringcapability/db/installsettings/' . $filename);
                if ($filesetting = $this->get_settings_from_filecontent($content)) {
                    $settings = array_merge($settings, $filesetting);
                }
            }
        }

        $this->defaultsettings = array();
        foreach ($settings as $setting) {

            if (!isset($this->defaultsettings[$setting->plugin])) {
                $this->defaultsettings[$setting->plugin] = array();
            }

            // Get new settings from read record.
            foreach ($setting as $key => $value) {

                $matches = array();
                if (preg_match('/level_([0-9]*)/', $key, $matches)) {
                    $this->defaultsettings[$setting->plugin][$matches[1]] = $value;
                }
            }
        }

        return $this->defaultsettings;
    }

    /**
     * Get default settings during installation of plugin.
     *
     * @param array $pluginnames names of setting keys.
     * @return int
     */
    public function get_default_settings($pluginnames) {
        global $CFG;

        require_once($CFG->dirroot . '/user/profile/field/authoringlevelmenu/field.class.php');

        // Try to get settings from file.
        $filedefaultsettings = $this->get_default_settings_from_files();

        $defaultsettings = array();
        foreach ($pluginnames as $pluginname) {

            if (isset($filedefaultsettings[$pluginname])) {
                $defaultsettings[$pluginname] = $filedefaultsettings[$pluginname];
                continue;
            }

            // Set default settings, when level settings are missing in files.
            $defaultsettings[$pluginname] = array(
                \profile_field_authoringlevelmenu::AUTHORING_LEVEL_BEGINNER => 1,
                \profile_field_authoringlevelmenu::AUTHORING_LEVEL_INTERMEDIATE => 1,
                \profile_field_authoringlevelmenu::AUTHORING_LEVEL_ADVANCED => 1
            );
        }

        return $defaultsettings;
    }

}
