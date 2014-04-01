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



require_once('./modules/download/content.php');
require_once('./modules/download/content/file.php');


//FolderDownloadContent
class FolderDownloadContent extends DownloadContent
{
	//public
	//methods
	//essential
	//FolderDownloadContent::FolderDownloadContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		//translations
		$this->stock_submit = 'folder-new';
		$this->text_content_by = _('Folder from');
		$this->text_content_list_title = _('Directory listing');
		$this->text_more_content = _('Browse...');
		$this->text_submit = _('New folder...');
		$this->text_submit_content = _('New folder');
	}


	//accessors
	//FolderDownloadContent::canPreview
	public function canPreview($engine, $request = FALSE, &$error = FALSE)
	{
		return FALSE;
	}


	//FolderDownloadContent::get
	public function get($property)
	{
		if($property == 'parent_id' && parent::get($property) === NULL)
			return FALSE;
		return parent::get($property);
	}


	//FolderDownloadContent::getTitle
	public function getTitle()
	{
		if($this->getID() === FALSE)
			return '/';
		return parent::getTitle();
	}


	//FolderDownloadContent::set
	public function set($property, $value)
	{
		//XXX can't use $this->class (constructor issues)
		$class = get_class();

		if($property == 'mode' && $value !== FALSE
				&& is_numeric($value))
			$value |= $class::$S_IFDIR;
		return parent::set($property, $value);
	}


	//useful
	//FolderDownloadContent::display
	public function display($engine, $request)
	{
		$class = $this->class;
		$title = $this->text_content_list_title.': '.$this->getTitle();
		$page = new Page(array('title' => $title));

		$page->append('title', array('stock' => $this->stock,
			'text' => $title));
		$page->append($this->displayToolbar($engine, $request));
		$class::$query_list = $class::$folder_query_list;
		if(($files = $class::_listFiles($engine, $this->getModule(),
				FALSE, FALSE, 'title ASC', FALSE, $class,
				$this)) === FALSE)
		{
			$page->append('dialog', array('type' => 'error',
					'text' => 'Could not list the files'));
			return $page;
		}
		$columns = array('icon' => '', 'title' => _('Filename'),
			'username' => _('Owner'), 'group' => _('Group'),
			'date' => _('Date'), 'mode' => _('Permissions'));
		$view = $page->append('treeview', array('columns' => $columns));
		while(($f = array_shift($files)) !== NULL)
		{
			$properties = $f->getProperties();
			$icon = $f->getIcon($engine);
			$properties['icon'] = new PageElement('image', array(
				'source' => $icon));
			$properties['title'] = new PageElement('link', array(
				'request' => $f->getRequest(),
				'text' => $f->getTitle()));
			if(($user_id = $f->getUserID()) != 0)
			{
				$r = new Request('user', FALSE, $user_id,
					$f->getUsername());
				$link = new PageElement('link', array(
					'request' => $r, 'stock' => 'user',
					'text' => $f->getUsername()));
				$properties['username'] = $link;
			}
			$properties['group'] = $f->getGroup();
			$properties['date'] = $f->getDate($engine);
			$properties['mode'] = $f->getPermissions();
			$view->append('row', $properties);
		}
		return $page;
	}


	//FolderDownloadContent::displayToolbar
	public function displayToolbar($engine, $request)
	{
		$credentials = $engine->getCredentials();
		$module = $this->getModule();
		$parent = $this->get('parent_id');
		$download_id = $this->get('download_id');

		$toolbar = new PageElement('toolbar');
		$parent = ($parent != NULL) ? $parent : FALSE;
		//parent folder
		//XXX would be nicer with the title too
		$r = new Request($module->getName(), FALSE, $parent);
		$toolbar->append('button', array('stock' => 'updir',
				'request' => $r, 'text' => _('Parent folder')));
		//refresh
		$r = $this->getRequest();
		$toolbar->append('button', array('stock' => 'refresh',
				'request' => $r, 'text' => _('Refresh')));
		if($module->canSubmit($engine, FALSE, $this))
		{
			//new directory
			$r = new Request($module->getName(), 'submit', FALSE,
				FALSE, array('parent' => $download_id));
			$toolbar->append('button', array('request' => $r,
					'stock' => $this->stock_submit,
					'text' => $this->text_submit_content));
			//upload file
			$r = new Request($module->getName(), 'submit', FALSE,
					FALSE, array('type' => 'file',
					'parent' => $download_id));
			$toolbar->append('button', array('request' => $r,
					'stock' => 'upload',
					'text' => _('Upload file')));
		}
		if($this->getID() !== FALSE
				&& $this->canUpdate($engine, FALSE, $this))
		{
			//rename
			$r = $this->getRequest('update');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'update',
					'text' => $this->text_update));
		}
		//administration
		if($credentials->isAdmin($engine))
		{
			$r = new Request($module->getName(), 'admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		return $toolbar;
	}


	//FolderDownloadContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Name: '),
				'value' => $request->getParameter('title')));
		return $vbox;
	}

	protected function _formUpdate($engine, $request)
	{
		$vbox = new PageElement('vbox');
		if(($value = $request->getParameter('title')) === FALSE)
			$value = $this->getTitle();
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Name: '),
				'value' => $value));
		return $vbox;
	}


	//FolderDownloadContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$class = $this->class;
		$db = $engine->getDatabase();
		$query = $this->folder_query_insert;
		$parent = $request->get('parent');
		$mode = $request->get('mode');

		if($parent === FALSE)
			$parent = NULL;
		if($mode !== FALSE)
			$mode |= $class::$S_IFDIR;
		else if(($mode = $this->get('mode')) === FALSE)
		{
			$mode = 0755 | $class::$S_IFDIR;
			$this->set('mode', FALSE);
		}
		$error = 'Invalid mode for directory';
		if(!$this->isDirectory($mode))
			return FALSE;
		//FIXME check for filename unicity in the current folder
		//set missing parameters
		$this->set('download_id', FALSE);
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$args = array('content_id' => $this->getID(),
			'parent' => $parent, 'mode' => $mode);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$error = _('Could not create the directory');
			return FALSE;
		}
		if(($did = $db->getLastID($engine, 'daportal_download',
				'download_id')) === FALSE)
			return FALSE;
		//reflect the new properties
		$this->set('download_id', $did);
		$this->set('mode', $mode);
		return TRUE;
	}


	//static
	//methods
	//FolderDownloadContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $order = FALSE, $user = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'title ASC';
				break;
		}
		$class::$query_list = $class::$folder_query_list;
		$class::$query_list .= ' AND daportal_download.parent IS NULL';
		return $class::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}

	static protected function _listAll($engine, $module, $limit, $offset,
			$order, $user, $class)
	{
		return $class::_listFiles($engine, $module, $limit, $offset,
				$order, $user, $class);
	}

	static protected function _listFiles($engine, $module, $limit, $offset,
			$order, $user, $class, $parent = FALSE)
	{
		$ret = array();
		$vbox = new PageElement('vbox');
		$database = $engine->getDatabase();
		$query = $class::$query_list;
		$args = array('module_id' => $module->getID());

		if($parent !== FALSE && ($id = $parent->get('download_id'))
				!== FALSE)
		{
			$query .= ' AND daportal_download.parent=:parent_id';
			$args['parent_id'] = $id;
		}
		else
			$query .= ' AND daportal_download.parent IS NULL';
		if($order !== FALSE)
			$query .= ' ORDER BY '.$order;
		if($limit !== FALSE || $offset !== FALSE)
			$query .= $database->offset($limit, $offset);
		if(($res = $database->query($engine, $query, $args)) === FALSE)
			return FALSE;
		while(($r = array_shift($res)) != NULL)
		{
			$classname = ($r['mode'] & $class::$S_IFDIR)
				? 'FolderDownloadContent'
				: 'FileDownloadContent';
			$ret[] = new $classname($engine, $module, $r);
		}
		return $ret;
	}


	//FolderDownloadContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		$class = get_class();

		$class::$query_load = $class::$folder_query_load;
		return $class::_load($engine, $module, $id, $title, $class);
	}


	//protected
	//properties
	//queries
	//IN:	module_id
	static protected $folder_query_list = 'SELECT
		daportal_content_public.content_id AS id,
		daportal_content_public.enabled AS enabled,
		daportal_content_public.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname AS "group",
		daportal_content_public.title AS title, mode
		FROM daportal_content_public, daportal_user_enabled,
		daportal_group, daportal_download
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_public.group_id=daportal_group.group_id
		AND daportal_content_public.content_id
		=daportal_download.content_id';
	//IN:	content_id
	//	parent
	//	mode
	protected $folder_query_insert = 'INSERT INTO daportal_download
		(content_id, parent, mode)
		VALUES (:content_id, :parent, :mode)';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $folder_query_load = "SELECT
		daportal_module.name AS module,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username,
		daportal_group.group_id AS group_id,
		daportal_group.groupname AS \"group\",
		daportal_content.content_id AS id,
		daportal_content.title AS title,
		daportal_content.content AS content,
		daportal_content.timestamp AS timestamp,
		daportal_content.enabled AS enabled,
		daportal_content.public AS public,
		download.download_id AS download_id,
		parent_download.content_id AS parent_id,
		parent_content.title AS parent_title,
		download.mode AS mode
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_group, daportal_download download
		LEFT JOIN daportal_download parent_download
		ON download.parent=parent_download.download_id
		LEFT JOIN daportal_content parent_content
		ON parent_download.content_id=parent_content.content_id
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id
		AND daportal_content.content_id=download.content_id
		AND daportal_content.enabled='1'
		AND (daportal_content.public='1'
		OR daportal_content.user_id=:user_id)
		AND (download.mode & 512) = 512
		AND daportal_content.content_id=:content_id";
}

?>
