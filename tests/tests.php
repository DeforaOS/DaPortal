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
$config->setVariable('database', 'backend', 'sqlite3');
$config->setVariable('database::sqlite3', 'filename', '../tests/sqlite.db3');
$engine = new CliEngine;

//functions
function test($engine, $request)
{
	if(($page = $engine->process($request)) !== FALSE
			&& $page instanceof PageElement)
		$engine->render($page);
}

function tests($engine)
{
	//content modules
	$modules = array('article', 'blog', 'download', 'news', 'project',
		'wiki');
	$actions = array(FALSE, 'actions', 'admin', 'default', 'headline',
		'list', 'preview', 'publish', 'submit', 'update');
	foreach($modules as $module)
	{
		test($engine, new Request($module));
		foreach($actions as $a)
			test($engine, new Request($module, $a));
	}
}

//default formatting backend
tests($engine);

//AtomFormat
$config->setVariable('format', 'backend', 'atom');
tests($engine);

//CSVFormat
$config->setVariable('format', 'backend', 'csv');
tests($engine);

//HTMLFormat
$config->setVariable('format', 'backend', 'html');
tests($engine);

//HTML5Format
$config->setVariable('format', 'backend', 'html5');
tests($engine);

//FPDFFormat
$config->setVariable('format', 'backend', 'fpdf');
tests($engine);

//XHTML1Format
$config->setVariable('format', 'backend', 'xhtml1');
tests($engine);

//XHTML11Format
$config->setVariable('format', 'backend', 'xhtml11');
tests($engine);

//XMLFormat
$config->setVariable('format', 'backend', 'xml');
tests($engine);

?>
