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
 * Test of the web service with the form settings.
 *
 * @package    local_ws_unenrol
 * @copyright  2021 marc.leconte@gmx.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// Main configuration importation (instanciate the $CFG global variable).
require_once(dirname(__FILE__) . '/../../../config.php');
// Define class curl.
require_once(dirname(__FILE__) . '/../../../lib/filelib.php');
// SETUP serveur.
$token = optional_param('token', 'none', PARAM_ALPHANUM);
$domainname = optional_param('serveur', 'none', PARAM_RAW);

// FUNCTION NAME.
$functionname = 'unenroluser';

// PARAMETERS.
$courseid = optional_param('courseid', -1, PARAM_INT);
$userid = optional_param('userid', '5', PARAM_INT);
$roleid = optional_param('roleid', '5', PARAM_INT);

// XML-RPC CALL.
if ($token != 'none') {
    $serverurl = $domainname . '/webservice/xmlrpc/server.php'. '?wstoken=' . $token;
    $curl = new curl;

    $enrolments = array();
    $params = array();
    $params['userid'] = $userid;
    $params['courseid'] = $courseid;
    $params['roleid'] = $roleid;
    $enrolments[] = $params;

    $post = xmlrpc_encode_request($functionname, array($enrolments));
    $resp = xmlrpc_decode($curl->post($serverurl, $post));
}

$toolpath = dirname(__FILE__);
$context = context_system::instance();
$PAGE->set_context($context);
require_login();
$urlact = new moodle_url($toolpath . '/testws.php', []);
$PAGE->set_url($urlact);
$PAGE->set_title("TEST");

echo $OUTPUT->header();

if ($token != 'none') {
    echo('Test de la suppression de role dans le cours id = '. $courseid .'<br><div id="resultat"></div>');

    if (!is_array($resp)) {
        echo ("<script type='text/javascript'>
            function output(inp) {
                var element = document.getElementById('resultat');
                element.appendChild(document.createElement('pre')).innerHTML = inp;
            }
            var obj = " . $resp . ";
            var str = JSON.stringify(obj, undefined, 4);
            output(str);
            </script>");
    } else {
        var_dump($resp);
    }
} else {
    echo ("<form method='post'>");
    echo ("TOKEN : <input type='text' name='token' size='50'/><br/>");
    echo ("SERVEUR : <input type='text' name='serveur' value=".$CFG->wwwroot."/><br/>");
    echo ("USERID : <input type='text' name='userid' value='58'/><br/>");
    echo ("COURSEID : <input type='text' name='courseid' value='487'/><br/>");
    echo ("ROLEID : <input type='text' name='roleid' value='3'/><br/>");

    echo ("<input type='submit' value='Tester'/><br/>");
    echo ("</form>");
}
echo $OUTPUT->footer();