<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



if(chdir('../src') === FALSE)
	exit(2);
require_once('./system/config.php');
require_once('./engines/cli.php');
require_once('./system/module.php');

global $config;
$config = new Config;
$config->set('database', 'backend', 'sqlite3');
$config->set('database::sqlite3', 'filename', '../tests/sqlite.db3');
$engine = new CliEngine;

//functions
function test($engine, $request)
{
	if(($page = $engine->process($request)) !== FALSE
			&& $page instanceof PageElement)
		$engine->render($page);
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
	$actions = array(FALSE, 'actions', 'admin', 'default', 'headline',
		'list', 'preview', 'publish', 'submit', 'update');
	foreach($modules as $module)
	{
		test($engine, new Request($module));
		foreach($actions as $a)
			test($engine, new Request($module, $a));
	}
	//multi-content modules
	$modules = array(
		'project' => array(FALSE, 'project', 'bug', 'bugreply'));
	foreach($modules as $module => $types)
		foreach($types as $t)
		{
			test($engine, new Request($module, FALSE, FALSE, FALSE,
					array('type' => $t)));
			foreach($actions as $a)
				test($engine, new Request($module, $a, FALSE,
					FALSE, array('type' => $t)));
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
