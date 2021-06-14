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
 * Web service local external services definitions.
 *
 * @package    local_ws_chgt_date
 * @copyright  2020 marc leconte
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$descript      = "Suppression d'un role dans un cours";
$functions = array(
        'unenroluser' => array(
                'classname'   => 'local_unenrol_external',
                'methodname'  => 'unenrolusers',
                'classpath'   => 'local/ws_unenrol/externallib.php',
                'description' => $descript,
                'type'        => 'write',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Suppression role service' => array('functions' => array ('unenroluser'),
                                                'restrictedusers' => 0,
                                                'enabled' => 1 )
        );
