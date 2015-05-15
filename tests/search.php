<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
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


//search
global $config;

if(($module = Module::load($engine, 'search')) === FALSE)
	exit(2);

//simple search
$request = new Request('search', FALSE, FALSE, FALSE, array('q' => 'test'));
if(($result = $module->call($engine, $request)) === FALSE
		|| !$result instanceof PageResponse)
	exit(3);

//advanced search
$request = new Request('search', 'advanced', FALSE, FALSE, array(
		'q' => 'test'));
if(($result = $module->call($engine, $request)) === FALSE
		|| !$result instanceof PageResponse)
	exit(4);
$how = array(0, 1);
$case = array(0, 1);
foreach($how as $h)
	foreach($case as $c)
	{
		$config->set('module::search', 'regexp', $h);
		$args = array('q' => 'test', 'case' => $c);
		$request = new Request('search', 'advanced', FALSE, FALSE,
			$args);
		if(($result = $module->call($engine, $request)) === FALSE
				|| !$result instanceof PageResponse)
			exit(5);
	}
exit(0);

?>
