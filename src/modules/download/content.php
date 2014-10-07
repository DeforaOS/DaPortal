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



//DownloadContent
abstract class DownloadContent extends MultiContent
{
	//public
	//methods
	//essential
	//DownloadContent::DownloadContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields['download_id'] = 'Download ID';
		$this->fields['parent_id'] = 'Parent';
		$this->fields['mode'] = 'Permissions';
		$this->setPublic(TRUE);
		$this->set('parent_id', NULL);
		parent::__construct($engine, $module, $properties);
	}


	//accessors
	//DownloadContent::getParent
	public function getParent($engine, $request = FALSE)
	{
		return self::getContent($engine, $this->get('parent_id'),
				FALSE, $request);
	}


	//static
	//DownloadContent::getRoot
	static public function getRoot($name = FALSE)
	{
		global $config;
		$error = 'The download repository is not configured';

		if($name === FALSE)
			$name = 'download';
		if(($root = $config->get('module::'.$name, 'root'))
				=== FALSE)
		{
			$engine->log('LOG_WARNING', $error);
			$root = '/tmp';
		}
		return $root;
	}


	//protected
	//properties
	static protected $S_IFDIR = 512;


	//methods
	//accessors
	//DownloadContent::get
	public function get($property)
	{
		if($property == 'parent_id' && parent::get($property) === NULL)
			return FALSE;
		return parent::get($property);
	}


	//DownloadContent::getIcon
	protected function getIcon($engine, $size = 16)
	{
		if($this->isDirectory())
			return Mime::getIconByType($engine, 'inode/directory',
					$size);
		return Mime::getIcon($engine, $this->getTitle(), $size);
	}


	//DownloadContent::getPermissions
	protected function getPermissions($mode = FALSE)
	{
		if($mode === FALSE)
			$mode = $this->get('mode');
		return Common::getPermissions($mode, static::$S_IFDIR);
	}


	//DownloadContent::isDirectory
	protected function isDirectory($mode = FALSE)
	{
		if($mode === FALSE)
			$mode = $this->get('mode');
		return ($mode & static::$S_IFDIR) ? TRUE : FALSE;
	}
}

?>
