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
		$this->text_content_by = _('Folder from');
		$this->text_content_list_title = _('Directory listing');
		$this->text_more_content = _('Browse...');
		$this->text_submit = _('New folder...');
	}


	//accessors
	//FolderDownloadContent::getTitle
	public function getTitle()
	{
		$title = parent::getTitle();
		if($title === FALSE)
			$title = '/';
		return $this->text_content_list_title.': '.$title;
	}


	//useful
	//FolderDownloadContent::display
	public function display($engine, $request)
	{
		$class = get_class();
		$page = new Page(array('title' => $this->getTitle()));

		$page->append('title', array('stock' => $this->stock,
			'text' => $this->getTitle()));
		if(($files = $class::listAll($engine, $this->getModule()))
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
		while(($f = array_shift($files)) !== NULL)
		{
			$properties = $f->getProperties();
			$properties['date'] = $f->getDate($engine);
			$properties['mode'] = $this->getPermissions(
					$properties['mode']);
			$view->append('row', $properties);
		}
		return $page;
	}


	//static
	//methods
	//FolderDownloadContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $order = FALSE)
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
		return FolderDownloadContent::_listAll($engine, $module, $limit, $offset,
				$order, $class);
	}


	//FolderDownloadContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		return Content::_load($engine, $module, $id, $title,
				get_class());
	}


	//protected
	//properties
	//queries
	static protected $folder_query_list = 'SELECT
		daportal_content_public.content_id AS id,
		daportal_content_public.enabled AS enabled,
		daportal_content_public.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname AS "group", title,
		mode
		FROM daportal_content_public, daportal_user_enabled,
		daportal_group, daportal_download
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id=daportal_user_enabled.user_id
		AND daportal_content_public.group_id=daportal_group.group_id
		AND daportal_content_public.content_id=daportal_download.content_id
		AND daportal_download.parent IS NULL';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $folder_query_load = "SELECT daportal_module.name AS module,
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