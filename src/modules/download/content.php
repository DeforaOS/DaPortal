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



require_once('./system/common.php');
require_once('./system/content.php');
require_once('./system/mime.php');


//DownloadContent
abstract class DownloadContent extends Content
{
	//public
	//methods
	//essential
	//DownloadContent::DownloadContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields[] = 'download_id';
		$this->fields[] = 'parent_id';
		$this->fields[] = 'mode';
		parent::__construct($engine, $module, $properties);
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
	//methods
	//accessors
	//DownloadContent::getIcon
	protected function getIcon($engine, $size = 16)
	{
		if($this->isDirectory())
			return Mime::getIconByType($engine,
				'inode/directory', $size);
		return Mime::getIcon($engine, $this->getTitle(), $size);
	}


	//DownloadContent::getPermissions
	protected function getPermissions($mode = FALSE)
	{
		if($mode === FALSE)
			$mode = $this->get('mode');
		return Common::getPermissions($mode, DownloadContent::$S_IFDIR);
	}


	//DownloadContent::isDirectory
	protected function isDirectory($mode = FALSE)
	{
		if($mode === FALSE)
			$mode = $this->get('mode');
		return ($mode & DownloadContent::$S_IFDIR) ? TRUE : FALSE;
	}


	//static
	//DownloadContent::_load
	static protected function _load($engine, $module, $id, $title, $class)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();
		$query = Content::$query_load;
		$args = array('module_id' => $module->getID(),
			'user_id' => $credentials->getUserID(),
			'content_id' => $id);

		if(is_string($title))
		{
			$query .= ' AND daportal_content.title '
				.$database->like(FALSE).' :title';
			$args['title'] = str_replace('-', '_', $title);
		}
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		$res = $res[0];
		return new $class($engine, $module, $res);
	}


	//properties
	static protected $S_IFDIR = 512;
}

?>
