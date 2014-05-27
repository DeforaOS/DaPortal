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
	$len = strlen($class);
	//Content sub-classes
	if($len > 7 && substr($class, -7) == 'Content')
	{
		$module = substr($class, 0, $len - 7);
		$filename = './modules/'.strtolower($module).'/content.php';
	}
	//Modules
	else if($len > 6 && substr($class, -6) == 'Module')
	{
		$module = substr($class, 0, $len - 6);
		$filename = './modules/'.strtolower($module).'/module.php';
	}
	//Responses
	else if($len > 8 && substr($class, -8) == 'Response')
		$filename = './system/response.php';
	else
		switch($class)
		{
			case 'AuthCredentials':
				$filename = './system/auth.php';
				break;
			default:
				$filename = './system/'.strtolower($class).'.php';
				break;
		}
	if(file_exists($filename))
		require_once($filename);
}

spl_autoload_register('autoload');

?>
