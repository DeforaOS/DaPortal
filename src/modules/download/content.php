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
	//DownloadContent::canSubmit
	public function canSubmit($engine, $request = FALSE, &$error = FALSE)
	{
		if(parent::canSubmit($engine, $request, $error) === FALSE)
			return FALSE;
		if($request === FALSE)
			return TRUE;
		//forbid empty filenames
		$filename = $this->getFilenameSubmitted($request);
		if(!is_string($filename) || strlen($filename) == 0)
		{
			$error = _('The filename must be specified');
			return FALSE;
		}
		//check for filename unicity
		$module = $this->getModule();
		if(($parent = $this->getParentSubmitted($request)) !== FALSE)
		{
			$class = $module::getContentClass('folder');
			if(($parent = $class::loadByDownloadID($engine,
					$module, $parent)) === FALSE)
			{
				$error = _('Could not load the parent');
				return FALSE;
			}
		}
		if(($files = static::_listFiles($engine, $module, FALSE, FALSE,
				FALSE, FALSE, 0, $parent)) === FALSE)
		{
			$error = _('Could not obtain the file list');
			return FALSE;
		}
		foreach($files as $f)
			if($f['title'] == $filename)
			{
				$error = _('This file already exists');
				return FALSE;
			}
		return TRUE;
	}


	//DownloadContent::getParent
	public function getParent($engine, $request = FALSE)
	{
		return static::getContent($engine, $this->get('parent_id'),
				FALSE, $request);
	}


	//static
	//accessors
	//DownloadContent::getRoot
	static public function getRoot(Engine $engine, $name = FALSE)
	{
		global $config;
		$error = 'The download repository is not configured';

		if($name === FALSE)
			$name = 'download';
		if(($root = $config->get('module::'.$name, 'root'))
				=== FALSE)
			return $engine->log('LOG_ERR', $error);
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


	//protected
	//properties
	static protected $list_order = 'isdir DESC, title ASC';

	static protected $S_IFDIR = 512;
	static protected $list_mask = 0;
	static protected $load_title = 'daportal_content_enabled.title';

	//queries
	//IN:	module_id
	static protected $query_list = 'SELECT
		daportal_content.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, enabled, public, download_id, mode, mode & 512 AS isdir
		FROM daportal_content_public AS daportal_content,
		daportal_download
		WHERE daportal_content.content_id=daportal_download.content_id
		AND module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, module_id, module,
		daportal_content.user_id AS user_id, username,
		daportal_content.group_id AS group_id,
		daportal_content.groupname AS groupname, title, content,
		daportal_content.enabled AS enabled, public,
		download_id, mode, mode & 512 AS isdir
		FROM daportal_content_public AS daportal_content,
		daportal_download, daportal_user_group, daportal_group_enabled
		WHERE daportal_content.content_id=daportal_download.content_id
		AND module_id=:module_id
		AND daportal_content.user_id=daportal_user_group.user_id
		AND daportal_user_group.group_id=daportal_group_enabled.group_id
		AND (daportal_user_group.group_id=:group_id
		OR daportal_content.group_id=:group_id)';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT
		daportal_content.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, enabled, public, download_id, mode, mode & 512 AS isdir
		FROM daportal_content_public AS daportal_content,
		daportal_download
		WHERE daportal_content.content_id=daportal_download.content_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT
		daportal_content.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, enabled, public, download_id, mode, mode & 512 AS isdir
		FROM daportal_content_enabled AS daportal_content,
		daportal_download
		WHERE daportal_content.content_id=daportal_download.content_id
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


	//DownloadContent::getFilenameSubmitted
	abstract protected function getFilenameSubmitted(
			Request $request = NULL);


	//DownloadContent::getParentSubmitted
	abstract protected function getParentSubmitted(Request $request = NULL);


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


	//static
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
			$query .= ' AND (mode & :mask) > 0';
			$args['mask'] = $mask;
		}
		$order = static::getOrder($engine, $order);
		if(($res = static::query($engine, $query, $args, $order, $limit,
				$offset)) === FALSE)
			return FALSE;
		return $res;
	}
}

?>
