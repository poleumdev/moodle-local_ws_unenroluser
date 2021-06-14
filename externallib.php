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
 * External course un enrol api.
 *
 * @package    local_ws_unenrol
 * @category   external
 * @copyright  2021 marc.leconte@gmx.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * Web service unenrolment external functions on roleid.
 *
 * @package    local_ws_unenrol
 * @category   external
 * @copyright  2021 marc.leconte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 3.6
 */
class local_unenrol_external extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function unenrolusers_parameters() {
        return new external_function_parameters(array(
            'enrolments' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'userid' => new external_value(PARAM_INT, 'The user that is going to be unenrolled'),
                        'courseid' => new external_value(PARAM_INT, 'The course to unenrol the user from'),
                        'roleid' => new external_value(PARAM_INT, 'The user role', VALUE_OPTIONAL),
                    )
                )
            )
        ));
    }

    /**
     * Unenrolment of users.
     *
     * @param array $enrolments an array of course user and role ids
     * @throws coding_exception
     * @throws dml_transaction_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function unenrolusers($enrolments) {
        global $CFG, $DB;
        $params = self::validate_parameters(self::unenrolusers_parameters(), array('enrolments' => $enrolments));
        require_once($CFG->libdir . '/enrollib.php');
        $ret = new stdClass();

        $transaction = $DB->start_delegated_transaction(); // Rollback all enrolment if an error occurs.
        $enrol = enrol_get_plugin('manual');
        if (empty($enrol)) {
            $ret->result = 'enrol vide !!';
            throw new moodle_exception('manualpluginnotinstalled', 'enrol_manual');
        }

        $ret->nb = 0;
        $ret->tot = count($params['enrolments']);

        foreach ($params['enrolments'] as $enrolment) {
            $ret->nb++;
            $context = context_course::instance($enrolment['courseid']);

            require_capability('enrol/manual:unenrol', $context);
            $instance = $DB->get_record('enrol', array('courseid' => $enrolment['courseid'], 'enrol' => 'manual'));
            if (!$instance) {
                throw new moodle_exception('wsnoinstance', 'enrol_manual', $enrolment);
            }
            $user = $DB->get_record('user', array('id' => $enrolment['userid']));
            if (!$user) {
                throw new invalid_parameter_exception('User id not exist: '.$enrolment['userid']);
            }
            if (!$enrol->allow_unenrol($instance)) {
                throw new moodle_exception('wscannotunenrol', 'enrol_manual', '', $enrolment);
            }
            if (! empty($enrolment['roleid'])) {
                role_unassign($enrolment['roleid'], $enrolment['userid'], $context->id);
                // Check whether user has any remaining roles assigned in this context, if not then perform complete unenrol.
                $usersroles = self::enrol_get_course_users_roles($enrolment['courseid']);
                if (empty($usersroles[$enrolment['userid']])) {
                    $enrol->unenrol_user($instance, $enrolment['userid']);
                }
            } else {
                $enrol->unenrol_user($instance, $enrolment['userid']);
            }
        }
        $transaction->allow_commit();
        return json_encode($ret);
    }

    /**
     * Returns description of method result value.
     *
     * @return null
     */
    public static function unenrolusers_returns() {
        $returntxt = "chaine json exposant le resultat de la tentative de suppression de role";
        return new external_value ( PARAM_TEXT, $returntxt);
    }

    // Reprise de moodle/lib/enrollib.php cf https://github.com/moodle/moodle/blob/master/lib/enrollib.php
    public static function enrol_get_course_users_roles(int $courseid) : array {
        global $DB;

        $context = context_course::instance($courseid);

        $roles = array();

        $records = $DB->get_recordset('role_assignments', array('contextid' => $context->id));
        foreach ($records as $record) {
            if (isset($roles[$record->userid]) === false) {
                $roles[$record->userid] = array();
            }
            $roles[$record->userid][$record->roleid] = $record;
        }
        $records->close();
        return $roles;
    }
}
