@local_authoringcapability
Feature: Visiblility of forum headers.
  In order to use several levels
  As a user
  I need to be able to hide some forum form fields

  @javascript
  Scenario: Go to forum edit page as a Beginner
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following config values are set as admin:
      | mod_forum_mod_modstandardelshdr | 30 | local_authoringcapability |
    And I log in as "teacher1"
    And I set the field "Authoring capability" to "Beginner"
    And I click on "Update profile" "button"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Forum" to section "1"
    Then I should not see "Common module settings"
    When I click on "#action-menu-toggle-0" "css_element"
    And I click on "Profile" "link" in the ".usermenu" "css_element"
    And I click on "Edit profile" "link" in the "region-main" "region"
    And I set the field "Authoring capability" to "Advanced"
    And I click on "Update profile" "button"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Forum" to section "1"
    Then I should see "Common module settings"