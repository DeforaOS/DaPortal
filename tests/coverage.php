<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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
require_once('./system/module.php');


//functions
function test(Engine $engine, Request $request, $internal = FALSE)
{
	$module = $request->getModule();
	$action = $request->getAction();

	print("Testing module $module");
	if($action !== FALSE)
		print(", action $action");
	if($internal !== FALSE)
		print(" (internal)");
	print("\n");
	if(($res = $engine->process($request, $internal)) === FALSE)
		return FALSE;
	else if($internal)
	{
		if($action == 'actions' && !is_array($res))
			return FALSE;
	}
	else if($res instanceof PageResponse)
		$engine->render($res);
	else if(!($res instanceof Response))
	{
		var_dump($request);
		var_dump($res);
		return FALSE;
	}
	return TRUE;
}

function tests(Engine $engine, $format = FALSE)
{
	global $config;
	$ret = 0;

	if($format !== FALSE)
		$config->set('format', 'backend', $format);
	//admin module
	$module = 'admin';
	test($engine, new Request($module));
	//content modules
	$modules = array('article', 'blog', 'download', 'news', 'wiki');
	$internal = array('actions');
	$actions = array(FALSE, 'admin', 'default', 'headline', 'list',
		'preview', 'publish', 'submit', 'update');
	foreach($modules as $module)
	{
		foreach($internal as $a)
			if(test($engine, new Request($module, $a), TRUE)
					=== FALSE)
				$ret |= 2;
		foreach($actions as $a)
			if(test($engine, new Request($module, $a)) === FALSE)
				$ret |= 4;
	}
	//multi-content modules
	$modules = array(
		'project' => array(FALSE, 'project', 'bug', 'bugreply'));
	foreach($modules as $module => $types)
		foreach($types as $t)
		{
			foreach($internal as $a)
				if(test($engine, new Request($module, $a), TRUE)
						=== FALSE)
					$ret |= 8;
			foreach($actions as $a)
				if(test($engine, new Request($module, $a, FALSE,
						FALSE, array('type' => $t)))
						=== FALSE)
					$ret |= 16;
		}
	return $ret;
}

$ret = 0;

//default formatting backend
$ret |= tests($engine);

//AtomFormat
$ret |= tests($engine, 'atom');

//CSVFormat
$ret |= tests($engine, 'csv');

//HTMLFormat
$ret |= tests($engine, 'html');

//HTML5Format
$ret |= tests($engine, 'html5');

//FPDFFormat
$ret |= tests($engine, 'fpdf');

//XHTML1Format
$ret |= tests($engine, 'xhtml1');

//XHTML11Format
$ret |= tests($engine, 'xhtml11');

//XMLFormat
$ret |= tests($engine, 'xml');

exit($ret);

?>
