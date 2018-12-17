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
 * Settings page for the custom level.
 *
 * @package    profilefield_authoringlevelmenu
 * @copyright  2016 Andreas Wagner, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '../../../../../config.php');

use profilefield_authoringlevelmenu\local\authlevelhelper as authlevelhelper;

$PAGE->set_url(new moodle_url('/user/profile/field/authoringlevelmenu/customleveledit.php'));

$userid = optional_param('id', $USER->id, PARAM_INT);    // User id.

require_login();

// Guest can not edit.
if (isguestuser()) {
    print_error('guestnoeditprofile');
}

// The user profile we are editing.
if (!$user = $DB->get_record('user', array('id' => $userid))) {
    print_error('invaliduserid');
}

// Guest can not be edited.
if (isguestuser($user)) {
    print_error('guestnoeditprofile');
}

// Load the appropriate auth plugin.
$userauth = get_auth_plugin($user->auth);

if (!$userauth->can_edit_profile()) {
    print_error('noprofileedit', 'auth');
}

if ($editurl = $userauth->edit_profile_url()) {
    // This internal script not used.
    redirect($editurl);
}

$systemcontext   = context_system::instance();
$personalcontext = context_user::instance($user->id);

// Check access control.
if ($user->id == $USER->id) {
    // Editing own profile - require_login() MUST NOT be used here, it would result in infinite loop!
    if (!has_capability('moodle/user:editownprofile', $systemcontext)) {
        print_error('cannotedityourprofile');
    }

} else {
    // Teachers, parents, etc.
    require_capability('moodle/user:editprofile', $personalcontext);
    // No editing of guest user account.
    if (isguestuser($user->id)) {
        print_error('guestnoeditprofileother');
    }
    // No editing of primary admin!
    if (is_siteadmin($user) and !is_siteadmin($USER)) {  // Only admins may edit other admins.
        print_error('useradmineditadmin');
    }
}

if ($user->deleted) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('userdeleted'));
    echo $OUTPUT->footer();
    die;
}

$PAGE->set_pagelayout('admin');
$PAGE->set_context($personalcontext);

if ($USER->id != $user->id) {
    $PAGE->navigation->extend_for_user($user);
} else {
    if ($node = $PAGE->navigation->find('myprofile', navigation_node::TYPE_ROOTNODE)) {
        $node->force_open();
    }
}

$customleveleditform = new \profilefield_authoringlevelmenu\local\customleveledit_form(null, array('userid' => $userid));

if ($customleveleditform->is_cancelled()) {
    redirect(new moodle_url('/user/edit.php' , array('id' => $userid, 'course' => SITEID)));
}

if ($data = $customleveleditform->get_data()) {

    $result = authlevelhelper::save_custom_configuration($data);

    if ($result['error'] == 0) {

        $redirect = new moodle_url('/user/edit.php' , array('id' => $userid, 'course' => SITEID));
        redirect($redirect, $result['message']);

    } else {
        $msg = get_string('errorsavecustomlevel', 'profilefield_authoringlevelmenu');
    }
}

echo $OUTPUT->header();

$customleveleditform->display();

echo $OUTPUT->footer();