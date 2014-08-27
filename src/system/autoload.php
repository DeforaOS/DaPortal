<?php //$Id$
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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



//autoload
function autoload($class)
{
	if(strchr($class, '/') !== FALSE)
		return;
	if(($filename = _autoload_filename($class)) !== FALSE
			&& file_exists($filename))
		require_once($filename);
}

function _autoload_filename($class)
{
	//special cases
	switch($class)
	{
		case 'AuthCredentials':
			return './system/auth/credentials.php';
		case 'ConfigSection':
			return './system/config/section.php';
		case 'FormatElements':
			return './system/format/elements.php';
		case 'MultiContent':
			return './system/content/multi.php';
		case 'MultiContentModule':
			return './modules/content/multi.php';
		case 'PageElement':
			return './system/page/element.php';
		default:
			return _autoload_filename_default($class);
	}
}

function _autoload_filename_default($class)
{
	$len = strlen($class);
	//Auth sub-classes
	if($len > 4 && substr($class, -4) == 'Auth')
	{
		$auth = substr($class, 0, $len - 4);
		return './auth/'.strtolower($auth).'.php';
	}
	//Content sub-classes
	else if($len > 7 && substr($class, -7) == 'Content')
	{
		$module = substr($class, 0, $len - 7);
		return './modules/'.strtolower($module).'/content.php';
	}
	//Engines
	else if($len > 6 && substr($class, -6) == 'Engine')
	{
		$engine = substr($class, 0, $len - 6);
		return './engines/'.strtolower($engine).'.php';
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
	return './system/'.strtolower($class).'.php';
}

spl_autoload_register('autoload');

?>
