<?php //$Id$
//Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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
function test($engine, $request, $internal = FALSE)
{
	$module = $request->getModule();
	$action = $request->getAction();

	print("Testing module $module");
	if($action !== FALSE)
		print(", action $action");
	if($internal !== FALSE)
		print(" (internal)");
	print("\n");
	if(($res = $engine->process($request, $internal)) !== FALSE)
	{
		if($internal && $action == 'actions' && !is_array($res))
			return FALSE;
		if($res instanceof PageElement)
			$engine->render($res);
	}
	return TRUE;
}

function tests($engine, $format = FALSE)
{
	global $config;

	if($format !== FALSE)
		$config->set('format', 'backend', $format);
	//admin module
	$module = 'admin';
	test($engine, new Request($module));
	//content modules
	$modules = array('article', 'blog', 'download', 'news', 'wiki');
	$internal = array('actions');
	$actions = array('admin', 'default', 'headline', 'list', 'preview',
		'publish', 'submit', 'update');
	foreach($modules as $module)
	{
		if(test($engine, new Request($module)) === FALSE)
			exit(2);
		foreach($internal as $a)
			if(test($engine, new Request($module, $a), TRUE)
					=== FALSE)
				exit(3);
		foreach($actions as $a)
			if(test($engine, new Request($module, $a)) === FALSE)
				exit(4);
	}
	//multi-content modules
	$modules = array(
		'project' => array(FALSE, 'project', 'bug', 'bugreply'));
	foreach($modules as $module => $types)
		foreach($types as $t)
		{
			if(test($engine, new Request($module, FALSE, FALSE,
					FALSE, array('type' => $t))) === FALSE)
				exit(5);
			foreach($internal as $a)
				if(test($engine, new Request($module, $a), TRUE)
						=== FALSE)
					exit(6);
			foreach($actions as $a)
				if(test($engine, new Request($module, $a, FALSE,
						FALSE, array('type' => $t)))
						=== FALSE)
					exit(7);
		}
}

//default formatting backend
tests($engine);

//AtomFormat
tests($engine, 'atom');

//CSVFormat
tests($engine, 'csv');

//HTMLFormat
tests($engine, 'html');

//HTML5Format
tests($engine, 'html5');

//FPDFFormat
tests($engine, 'fpdf');

//XHTML1Format
tests($engine, 'xhtml1');

//XHTML11Format
tests($engine, 'xhtml11');

//XMLFormat
tests($engine, 'xml');

?>
