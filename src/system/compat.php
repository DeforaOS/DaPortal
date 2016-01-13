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



//Compatibility fixes
//http_build_url()
if(!function_exists('http_build_url'))
{
	function http_build_url($url, $parts = FALSE)
	{
		if(!is_array($url))
			if(($url = parse_url($url)) === FALSE)
				return FALSE;
		if(is_array($parts))
			foreach($parts as $k => $v)
				$url[$k] = $v;
		//protocol
		if(!isset($url['scheme']))
			return FALSE;
		$ret = $url['scheme'].'://';
		//credentials
		if(isset($url['user']))
		{
			$ret .= $url['user'];
			if(isset($url['pass']))
				$ret .= ':'.$url['pass'];
		}
		//host
		if(!isset($url['host']))
		{
			if($url['scheme'] != 'file')
				return FALSE;
		}
		else
		{
			$ret .= $url['host'];
			//port
			if(isset($url['port']) && is_numeric($url['port']))
				if(($url['scheme'] == 'http'
							&& $url['port'] != 80)
						|| ($url['scheme'] == 'https'
							&& $url['port'] != 443)
						|| ($url['scheme'] != 'http'
							&& $url['scheme'] != 'https'))
					$ret .= ':'.$url['port'];
			$ret .= '/';
		}
		//path
		if(isset($url['path']))
			$ret .= ltrim($url['path'], '/');
		else
			$ret .= '/';
		//query
		if(isset($url['query']))
			$ret .= '?'.$url['query'];
		if(isset($url['fragment']))
			$ret .= '#'.$url['fragment'];
		return $ret;
	}
}


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
			case 'Windows NT':
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
