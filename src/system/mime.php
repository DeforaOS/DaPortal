<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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
	static public function getIcon($engine, $filename, $size = 48)
	{
		if(Mime::init($engine) === FALSE)
			return 'icons/generic/'.$size.'x'.$size.'/'.Mime::$default;
		if(($type = Mime::getType($engine, $filename, FALSE)) !== FALSE)
			return Mime::getIconByType($engine, $type, $size);
		return 'icons/'.Mime::$iconpath.'/'.$size.'x'.$size.'/'
			.Mime::$default;
	}


	//Mime::getIconByType
	static public function getIconByType($engine, $type, $size = 48)
	{
		$from = array('application/', 'audio/', 'image/', 'text/',
			'video/');
		$to = array('application-', 'audio-', 'image-', 'text-',
			'video-');
		$icons = array('inode/directory' => 'places/folder');

		if(Mime::init($engine) === FALSE)
			return 'icons/generic/'.$size.'x'.$size.'/'.Mime::$default;
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
		$icon = 'icons/'.Mime::$iconpath.'/'.$size.'x'.$size.'/'
			.$icon.'.png';
		if(!is_readable('../data/'.$icon))
			return 'icons/'.Mime::$iconpath.'/'.$size.'x'.$size
				.'/'.Mime::$default;
		return $icon;
	}


	//Mime::getType
	static public function getType($engine, $filename,
			$default = 'application/octet-stream')
	{
		if(Mime::init($engine) === FALSE)
			return $default;
		//FIXME use lstat() if the filename is absolute or relative
		foreach(Mime::$types as $g)
			if(isset($g[1]) && fnmatch($g[1], $filename,
					FNM_CASEFOLD))
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
	static private function init($engine)
	{
		if(!defined(FNM_CASEFOLD))
			define(FNM_CASEFOLD, 0);
		if(Mime::$types === FALSE)
			Mime::_init_types($engine);
		if(Mime::$iconpath === FALSE)
			Mime::_init_iconpath($engine);
		return TRUE;
	}

	static private function _init_iconpath($engine)
	{
		global $config;

		$theme = $config->get(FALSE, 'icontheme');
		switch($theme)
		{
			case 'Tango':
				Mime::$iconpath = 'Tango/Tango';
				Mime::$default = 'mimetypes/unknown.png';
				break;
			case 'gnome':
			default:
				Mime::$iconpath = 'gnome/gnome-icon-theme';
				Mime::$default = 'mimetypes/gtk-file.png';
				break;
		}
		return TRUE;
	}

	static private function _init_types($engine)
	{
		global $config;

		Mime::$types = array();
		if(($globs = $config->get('mime', 'globs')) === FALSE)
		{
			$engine->log('LOG_WARNING',
					'The globs file is not defined');
			return FALSE;
		}
		if(($globs = file_get_contents($globs)) === FALSE)
		{
			$engine->log('LOG_WARNING',
					'Could not read the globs file');
			return FALSE;
		}
		$globs = explode("\n", $globs);
		foreach($globs as $line)
		{
			if(strlen($line) >= 1 && $line[0] == '#')
				continue;
			else
				Mime::$types[] = explode(':', $line);
		}
		return TRUE;
	}
}

?>
