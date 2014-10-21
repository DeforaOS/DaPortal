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
		if($property == 'mode' && $value !== FALSE
				&& is_numeric($value))
			$value |= static::$S_IFDIR;
		return parent::set($property, $value);
	}


	//useful
	//FolderDownloadContent::display
	public function display($engine, $request)
	{
		$title = $this->text_content_list_title.': '.$this->getTitle();
		$page = new Page(array('title' => $title));

		$page->append('title', array('stock' => $this->stock,
			'text' => $title));
		$page->append($this->displayToolbar($engine, $request));
		if(($files = static::listFiles($engine, $this->getModule(),
				FALSE, FALSE, FALSE, FALSE, 0, $this))
				=== FALSE)
		{
			$page->append('dialog', array('type' => 'error',
					'text' => 'Could not list the files'));
			return $page;
		}
		$columns = array('icon' => '', 'title' => _('Filename'),
			'username' => _('Owner'), 'group' => _('Group'),
			'date' => _('Date'), 'mode' => _('Permissions'));
		$view = $page->append('treeview', array('columns' => $columns));
		foreach($files as $f)
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
	public function displayToolbar($engine, $request = FALSE)
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
		$db = $engine->getDatabase();
		$query = $this->folder_query_insert;
		$parent = $request->get('parent');
		$mode = $request->get('mode');

		if($parent === FALSE)
			$parent = NULL;
		if($mode !== FALSE)
			$mode |= static::$S_IFDIR;
		else if(($mode = $this->get('mode')) === FALSE)
		{
			$mode = 0755 | static::$S_IFDIR;
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
	//FolderDownloadContent::loadFromProperties
	static public function loadFromProperties($engine, $module, $properties)
	{
		$class = (isset($properties['mode'])
				&& ($properties['mode'] & static::$S_IFDIR))
			? 'FolderDownloadContent'
			: 'FileDownloadContent';

		return new $class($engine, $module, $properties);
	}


	//protected
	static protected $class = 'FolderDownloadContent';
	static protected $list_mask = 512;
	static protected $list_order = 'title ASC';
	//properties
	//queries
	//IN:	content_id
	//	parent
	//	mode
	protected $folder_query_insert = 'INSERT INTO daportal_download
		(content_id, parent, mode)
		VALUES (:content_id, :parent, :mode)';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT
		daportal_content_enabled.content_id AS id,
		daportal_content_enabled.timestamp AS timestamp,
		daportal_content_enabled.module_id AS module_id, module,
		daportal_content_enabled.user_id AS user_id, username,
		daportal_content_enabled.group_id AS group_id, groupname,
		daportal_content_enabled.title AS title,
		daportal_content_enabled.content AS content,
		daportal_content_enabled.enabled AS enabled,
		daportal_content_enabled.public AS public,
		download.download_id AS download_id,
		parent_download.content_id AS parent_id,
		parent_content.title AS parent_title,
		download.mode AS mode
		FROM daportal_content_enabled, daportal_download download
		LEFT JOIN daportal_download parent_download
		ON download.parent=parent_download.download_id
		LEFT JOIN daportal_content parent_content
		ON parent_download.content_id=parent_content.content_id
		WHERE daportal_content_enabled.content_id=download.content_id
		AND daportal_content_enabled.module_id=:module_id
		AND (daportal_content_enabled.public='1'
		OR daportal_content_enabled.user_id=:user_id)
		AND (download.mode & 512) = 512
		AND daportal_content_enabled.content_id=:content_id
		AND (parent_content.enabled IS NULL OR parent_content.enabled='1')
		AND (parent_content.public IS NULL OR parent_content.public='1'
		OR parent_content.user_id=:user_id)";
}

?>
