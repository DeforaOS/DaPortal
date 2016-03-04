<?php //$Id$
//Copyright (c) 2012-2015 Pierre Pronchery <khorben@defora.org>
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



//Mime
class Mime
{
	//public
	//methods
	//static
	//Mime::getIcon
	static public function getIcon(Engine $engine, $filename, $size = 48)
	{
		if(static::init($engine) === FALSE)
			return 'icons/generic/'.$size.'x'.$size.'/'.static::$default;
		if(($type = static::getType($engine, $filename, FALSE)) !== FALSE)
			return static::getIconByType($engine, $type, $size);
		return 'icons/'.static::$iconpath.'/'.$size.'x'.$size.'/'
			.static::$default;
	}


	//Mime::getIconByType
	static public function getIconByType(Engine $engine, $type, $size = 48)
	{
		$from = array('application/', 'audio/', 'image/', 'text/',
			'video/');
		$to = array('application-', 'audio-', 'image-', 'text-',
			'video-');
		$icons = array('inode/directory' => 'places/folder');

		if(static::init($engine) === FALSE)
			return 'icons/generic/'.$size.'x'.$size.'/'.static::$default;
		//well-known
		if(isset($icons[$type]))
			$icon = $icons[$type];
		else
		{
			//substitutions
			//FIXME check if the substitution exists (and fallback)
			$icon = str_replace($from, $to, $type);
			$icon = 'mimetypes/gnome-mime-'.$icon;
		}
		$icon = 'icons/'.static::$iconpath.'/'.$size.'x'.$size.'/'
			.$icon.'.png';
		if(!is_readable('../data/'.$icon))
			return 'icons/'.static::$iconpath.'/'.$size.'x'.$size
				.'/'.static::$default;
		return $icon;
	}


	//Mime::getType
	static public function getType(Engine $engine, $filename,
			$default = 'application/octet-stream')
	{
		if(static::init($engine) === FALSE)
			return $default;
		//FIXME use lstat() if the filename is absolute or relative
		$filename = strtolower(rtrim($filename, "\0"));
		foreach(static::$types as $g)
			if(isset($g[1]) && fnmatch(strtolower($g[1]),
						$filename))
			{
				//XXX work-around an issue with *.tar.gz
				if($g[0] == 'application/x-gzip'
						|| $g[0] == 'application/gzip')
					continue;
				return $g[0];
			}
		return $default;
	}


	//private
	//static
	//properties
	static private $default = 'stock.png';
	static private $iconpath = FALSE;
	static private $types = FALSE;


	//methods
	//Mime::init
	static private function init(Engine $engine)
	{
		if(!defined(FNM_CASEFOLD))
			define(FNM_CASEFOLD, 0);
		if(static::$types === FALSE)
			static::_init_types($engine);
		if(static::$iconpath === FALSE)
			static::_init_iconpath($engine);
		return TRUE;
	}

	static private function _init_iconpath(Engine $engine)
	{
		global $config;

		$theme = $config->get(FALSE, 'icontheme');
		switch($theme)
		{
			case 'Tango':
				static::$iconpath = 'Tango/Tango';
				static::$default = 'mimetypes/unknown.png';
				break;
			case 'gnome':
			default:
				static::$iconpath = 'gnome/gnome-icon-theme';
				static::$default = 'mimetypes/gtk-file.png';
				break;
		}
		return TRUE;
	}

	static private function _init_types(Engine $engine)
	{
		global $config;

		static::$types = array();
		if(($globs = $config->get('mime', 'globs')) === FALSE)
		{
			$engine->log(LOG_WARNING,
					'The globs file is not defined');
			return FALSE;
		}
		if(($globs = file_get_contents($globs)) === FALSE)
		{
			$engine->log(LOG_WARNING,
					'Could not read the globs file');
			return FALSE;
		}
		$globs = explode("\n", $globs);
		foreach($globs as $line)
		{
			if(strlen($line) >= 1 && $line[0] == '#')
				continue;
			else
				static::$types[] = explode(':', $line);
		}
		return TRUE;
	}
}

?>
