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



//Compatibility fixes
//strptime()
if(!function_exists('strptime'))
{
	function strptime($date, $format)
	{
		//FIXME really implement
		return FALSE;
	}
}


//sys_get_temp_dir()
if(!function_exists('sys_get_temp_dir'))
{
	function sys_get_temp_dir()
	{
		switch(php_uname('s'))
		{
			case 'Windows':
				if(($tmp = getenv('TEMP')) === FALSE);
					$tmp = getenv('TMP');
				break;
			default:
				$tmp = getenv('TMPDIR');
				break;
		}
		if($tmp !== FALSE)
			return $tmp;
		return '/tmp';
	}
}

?>
