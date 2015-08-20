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
abstract class DownloadContent extends ContentMulti
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
	//accessors
	//DownloadContent::getRoot
	static public function getRoot($engine, $name = FALSE)
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


	//useful
	//DownloadContent::listAll
	static public function listAll($engine, $module, $order = FALSE,
			$limit = FALSE, $offset = FALSE, $user = FALSE)
	{
		return static::listFiles($engine, $module, $order, $limit,
				$offset, $user);
	}


	//DownloadContent::listFiles
	static protected function listFiles($engine, $module, $order = FALSE,
			$limit = FALSE, $offset = FALSE, $user = FALSE,
			$mask = FALSE, $parent = FALSE)
	{
		if(($res = static::_listFiles($engine, $module, $order, $limit,
				$offset, $user, $mask, $parent)) === FALSE)
			return FALSE;
		return static::listFromResults($engine, $module, $res);
	}

	static protected function _listFiles($engine, $module, $order, $limit,
			$offset, $user, $mask = FALSE, $parent = FALSE)
	{
		$vbox = new PageElement('vbox');
		$database = $engine->getDatabase();
		$query = static::$query_list;
		$args = array('module_id' => $module->getID());

		if($parent !== FALSE && ($id = $parent->get('download_id'))
				!== FALSE)
		{
			$query .= ' AND daportal_download.parent=:parent_id';
			$args['parent_id'] = $id;
		}
		else
			$query .= ' AND daportal_download.parent IS NULL';
		if($mask === FALSE)
			$mask = static::$S_IFDIR;
		if($mask != 0)
		{
			$query .= ' AND (mode & :mask)=:mode';
			$args['mask'] = $mask;
			$args['mode'] = static::$list_mask;
		}
		$order = static::getOrder($engine, $order);
		if(($res = static::query($engine, $query, $args, $order, $limit,
				$offset)) === FALSE)
			return FALSE;
		return $res;
	}


	//protected
	//properties
	static protected $S_IFDIR = 512;
	static protected $list_mask = 0;
	static protected $load_title = 'daportal_content_enabled.title';
	//queries
	//IN:	module_id
	static protected $query_list = 'SELECT
		daportal_content_public.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, enabled, public, mode
		FROM daportal_content_public, daportal_download
		WHERE daportal_content_public.content_id
		=daportal_download.content_id
		AND module_id=:module_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT
		daportal_content_public.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, enabled, public, mode
		FROM daportal_content_public, daportal_download
		WHERE daportal_content_public.content_id
		=daportal_download.content_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT
		daportal_content_enabled.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, enabled, public, mode
		FROM daportal_content_enabled, daportal_download
		WHERE daportal_content_enabled.content_id
		=daportal_download.content_id
		AND module_id=:module_id
		AND user_id=:user_id';


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
