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

//widget
$request = $module->getRequest('widget');
if(($result = $module->call($engine, $request)) === FALSE
		|| !$result instanceof PageResponse)
	exit(3);

//simple search
$request = $module->getRequest(FALSE, array('q' => 'test'));
if(($result = $module->call($engine, $request)) === FALSE
		|| !$result instanceof PageResponse)
	exit(4);

//advanced search
$request = $module->getRequest('advanced', array('q' => 'test'));
if(($result = $module->call($engine, $request)) === FALSE
		|| !$result instanceof PageResponse)
	exit(5);

//advanced search (module)
$request = $module->getRequest('advanced', array('q' => 'test',
		'module' => 'news'));
if(($result = $module->call($engine, $request)) === FALSE
		|| !$result instanceof PageResponse)
	exit(6);

$how = array(0, 1);
$case = array(0, 1);
foreach($how as $h)
{
	$config->set('module::search', 'regexp', $h);
	foreach($case as $c)
	{
		$args = array('q' => 'test', 'case' => $c);
		$request = $module->getRequest('advanced', $args);
		if(($result = $module->call($engine, $request)) === FALSE
				|| !$result instanceof PageResponse)
			exit(7);
	}
}
exit(0);

?>
