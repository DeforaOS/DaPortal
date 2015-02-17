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



$classes = array(
	'AuthCredentials' => './system/auth/credentials.php',
	'ConfigSection' => './system/config/section.php',
	'DatabaseResult' => './system/database/result.php',
	'FormatElements' => './system/format/elements.php',
	'MultiContentModule' => './modules/content/multi.php',
	'PageElement' => './system/page/element.php'
);


//autoload
function autoload($class)
{
	global $classes;
	$res = FALSE;

	if(strchr($class, '/') !== FALSE)
		return;
	if(isset($classes[$class]))
		$res = include_once($classes[$class]);
	else if(($filename = _autoload_filename($class)) !== FALSE)
		$res = include_once($filename);
	if($res === FALSE)
		error_log($class.': Could not autoload class');
}

function _autoload_filename($class)
{
	$len = strlen($class);
	//Auth sub-classes
	if($len > 4 && substr($class, -4) == 'Auth')
	{
		$auth = substr($class, 0, $len - 4);
		return './auth/'.strtolower($auth).'.php';
	}
	//Content sub-classes (in modules)
	else if($len > 7 && substr($class, -7) == 'Content')
	{
		$module = substr($class, 0, $len - 7);
		return './modules/'.strtolower($module).'/content.php';
	}
	//Databases
	else if($len > 8 && substr($class, -8) == 'Database')
	{
		$database = substr($class, 0, $len - 8);
		return './database/'.strtolower($database).'.php';
	}
	//Database results
	else if($len > 14 && substr($class, -14) == 'DatabaseResult')
	{
		$database = substr($class, 0, $len - 14);
		return './database/'.strtolower($database).'/result.php';
	}
	//Engines
	else if($len > 6 && substr($class, -6) == 'Engine')
	{
		$engine = substr($class, 0, $len - 6);
		return './engines/'.strtolower($engine).'.php';
	}
	//Formats
	else if($len > 6 && substr($class, -6) == 'Format')
	{
		$format = substr($class, 0, $len - 6);
		return './formats/'.strtolower($format).'.php';
	}
	//Modules
	else if($len > 6 && substr($class, -6) == 'Module')
	{
		$module = substr($class, 0, $len - 6);
		return './modules/'.strtolower($module).'/module.php';
	}
	//Responses
	else if($len > 8 && substr($class, -8) == 'Response')
	{
		$response = substr($class, 0, $len - 8);
		return './system/response/'.strtolower($response).'.php';
	}
	//Users
	else if($len > 4 && substr($class, -4) == 'User')
	{
		$module = substr($class, 0, $len - 4);
		return './modules/'.strtolower($module).'/user.php';
	}
	//Templates
	else if($len > 8 && substr($class, -8) == 'Template')
	{
		$template = substr($class, 0, $len - 8);
		if(realpath('./templates/'.$template.'.php') !== FALSE)
			return './templates/'.$template.'.php';
		return './templates/'.strtolower($template).'.php';
	}
	//Content sub-classes (in system)
	//XXX has to be after Modules
	else if($len > 7 && substr($class, 0, 7) == 'Content')
	{
		$content = substr($class, 7);
		return './system/content/'.strtolower($content).'.php';
	}
	return './system/'.strtolower($class).'.php';
}


//autoload_register
function autoload_register($class, $filename)
{
	global $classes;

	$classes[$class] = $filename;
}

spl_autoload_register('autoload');

?>
