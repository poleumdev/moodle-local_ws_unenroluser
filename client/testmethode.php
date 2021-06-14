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
 * Test only the web service method.
 *
 * @package    local_ws_unenrol
 * @copyright  2021 Pole de Ressource Numerique de l'Universite du Mans
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../config.php');

// Libraries imports.
require_once(dirname(__FILE__) .'/../externallib.php');

/*
 * Imports of class files.
 */
$toolpath = dirname(__FILE__);

$courseid = optional_param('courseid', -1, PARAM_INT);
$userid = optional_param('userid', '5', PARAM_INT);
$roleid = optional_param('roleid', '5', PARAM_INT);


$context = context_system::instance();
$PAGE->set_context($context);

require_login();

$urlact = new moodle_url($toolpath . '/testmethode.php', ['courseid' => $courseid, 'beg' => $debut]);
$PAGE->set_url($urlact);

$PAGE->set_title("TEST");

echo $OUTPUT->header();

echo ("Test suppression role dans un cours <br/>");
echo ("courseid = " . $courseid . "<br>");
echo ("userid = " . $userid . "<br>");
echo ("roleid = " . $roleid . "<br>");

if ($courseid != -1) {
    $enrolments = array();
    $userenrol = array();

    $userenrol['userid'] = $userid;
    $userenrol['courseid'] = $courseid;
    $userenrol['roleid'] = $roleid;

    $enrolments[] = $userenrol;

    $rs = local_unenrol_external::unenrolusers($enrolments);
    echo (json_encode($rs));
} else {
    echo ("<br/>Renseigner les parametres : userid, courseid, roleid");
}
echo $OUTPUT->footer();

