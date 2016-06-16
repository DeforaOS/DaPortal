<?php //$Id$
//Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org>
//This file is part of DeforaOS Web DaPortal
//
//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, version 3 of the License.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with this program.  If not, see <http://www.gnu.org/licenses/>.



require_once('./tests.php');


//functions
//load the test database
if(($database = $engine->getDatabase()) === FALSE)
	exit(2);

//issue a query
$query = 'SELECT user_id, username
	FROM daportal_user
	WHERE enabled=:enabled
	ORDER BY user_id ASC';
$args = array('enabled' => TRUE);
if(($res = $database->query($engine, $query, $args)) === FALSE)
	exit(3);

//check the results
if(count($res) != 3)
	exit(4);
$r = $res->current();
if($r['user_id'] != 0)
	exit(5);
$res->next();
$r = $res->current();
if($r['user_id'] != 1)
	exit(6);

//enable profiling
global $config;
$config->set('database', 'profile', 1);

//issue a query (profiling)
if(($res = $database->query($engine, $query, $args)) === FALSE)
	exit(7);

exit(0);

?>
