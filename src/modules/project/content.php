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



//ProjectContent
class ProjectContent extends MultiContent
{
	//public
	//methods
	//essential
	//ProjectContent::ProjectContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields['synopsis'] = 'Synopsis';
		$this->fields['scm'] = 'SCM';
		$this->fields['cvsroot'] = 'CVS root';
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		//translations
		$this->text_content_by = _('Project from');
		$this->text_content_list_title = _('Project list');
		$this->text_more_content = _('More projects...');
		$this->text_submit = _('Submit');
		$this->text_submit_content = _('New project');
	}


	//accessors
	//ProjectContent::canUpload
	public function canUpload($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		//FIXME really implement
		return $credentials->isAdmin();
	}


	//useful
	//ProjectContent::display
	public function display($engine, $request)
	{
		switch(($action = $request->getAction()))
		{
			case 'browse':
			case 'download':
			case 'gallery':
			case 'timeline':
				$title = _('Project: ').$this->getTitle();
				$page = new Page(array('title' => $title));
				$page->append($this->displayTitle($engine,
						$request));
				$page->append($this->displayToolbar($engine,
						$request));
				$method = '_display'.$action;
				return $this->$method($engine, $request, $page);
			case 'homepage':
			default:
				return parent::display($engine, $request);
		}
	}

	protected function _displayBrowse($engine, $request, $page)
	{
		if(($scm = ProjectModule::attachSCM($engine,
				$this->get('scm'))) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('An error occurred')));
		$browse = $scm->browse($engine, $this, $request);
		if(!($browse instanceof PageElement))
			//FIXME set the proper filename
			return $browse;
		$vbox = $page->append('vbox');
		$page->append($browse);
		return $page;
	}

	protected function _displayDownload($engine, $request, $page)
	{
		$db = $engine->getDatabase();
		$query = $this->project_query_list_downloads;

		$vbox = $page->append('vbox');
		//source code
		if(($scm = ProjectModule::attachSCM($engine,
				$this->get('scm'))) !== FALSE
				&& ($download = $scm->download($engine,
				$this, $request)) !== FALSE)
			$page->append($download);
		//downloads
		$error = 'Could not list downloads';
		$args = array('project_id' => $this->getID());
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		$vbox->append('title', array('text' => _('Releases')));
		$columns = array('icon' => '', 'filename' => _('Filename'),
				'owner' => _('Owner'), 'group' => _('Group'),
				'date' => _('Date'),
				'permissions' => _('Permissions'));
		$view = $vbox->append('treeview', array(
				'columns' => $columns));
		if($this->canUpload($engine, $request))
		{
			$toolbar = $view->append('toolbar');
			$req = $this->getRequest('submit', array(
				'type' => 'release'));
			$link = $toolbar->append('button', array(
					'stock' => 'new',
					'request' => $req,
					'text' => _('New release')));
		}
		foreach($res as $r)
		{
			$row = $view->append('row');
			$req = new Request('download', FALSE, $r['id'],
				$r['title']);
			$icon = Mime::getIcon($engine, $r['title'], 16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->setProperty('icon', $icon);
			$filename = new PageElement('link', array(
					'request' => $req,
					'text' => $r['title']));
			$row->setProperty('filename', $filename);
			$req = new Request('user', FALSE, $r['user_id'],
				$r['username']);
			$username = new PageElement('link', array(
					'stock' => 'user',
					'request' => $req,
					'text' => $r['username']));
			$row->setProperty('owner', $username);
			$row->setProperty('group', $r['groupname']);
			$date = $db->formatDate($engine, $r['timestamp']);
			$row->setProperty('date', $date);
			$permissions = Common::getPermissions($r['mode'], 512);
			$permissions = new PageElement('label', array(
					'class' => 'preformatted',
					'text' => $permissions));
			$row->setProperty('permissions', $permissions);
		}
		return $page;
	}

	protected function _displayGallery($engine, $request, $page)
	{
		$db = $engine->getDatabase();
		$query = $this->project_query_list_screenshots;

		$vbox = $page->append('vbox');
		//screenshots
		$error = _('Could not list screenshots');
		$args = array('project_id' => $this->getID());
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		$vbox = $page->append('vbox');
		$vbox->append('title', array('text' => _('Gallery')));
		$view = $vbox->append('treeview', array(
				'view' => 'thumbnails'));
		foreach($res as $r)
		{
			$row = $view->append('row');
			$req = new Request('download', 'download', $r['id'],
				$r['title']);
			$thumbnail = new PageElement('image', array(
					'request' => $req));
			$row->setProperty('thumbnail', $thumbnail);
			$label = new PageElement('link', array(
					'request' => $req,
					'text' => $r['title']));
			$row->setProperty('label', $label);
		}
		return $page;
	}

	protected function _displayTimeline($engine, $request, $page)
	{
		$vbox = $page->append('vbox');
		if(($scm = ProjectModule::attachSCM($engine,
				$this->get('scm'))) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('An error occurred')));
		$timeline = $scm->timeline($engine, $this, $request);
		$vbox->append($timeline);
		return $page;
	}


	//ProjectContent::displayContent
	public function displayContent($engine, $request)
	{
		$vbox = new PageElement('vbox');

		if(($text = $this->get('synopsis')) !== FALSE
				&& strlen($text))
			$vbox->append('label', array('class' => 'bold',
					'text' => $text));
		$vbox->append('htmlview', array(
			'text' => HTML::format($engine,
				$this->getContent($engine))));
		return $vbox;
	}


	//ProjectContent::displayRow
	public function displayRow($engine, $request = FALSE)
	{
		$ret = parent::displayRow($engine, $request);
		$ret->setProperty('synopsis', $this->get('synopsis'));
		return $ret;
	}


	//ProjectContent::displayToolbar
	public function displayToolbar($engine, $request)
	{
		$actions = array('bug_list' => _('Bug reports'),
			'download' => _('Download'),
			'gallery' => _('Gallery'),
			'timeline' => _('Timeline'),
			'browse' => _('Browse'),
			'homepage' => _('Homepage'));

		$toolbar = parent::displayToolbar($engine, $request);
		if($this->getID() === FALSE)
			return $toolbar;
		foreach($actions as $k => $v)
		{
			$r = ($k == 'homepage') ? $this->getRequest()
				: $this->getRequest($k);
			$button = new PageElement('button', array(
				'stock' => $k, 'request' => $r,
				'text' => $v));
			$toolbar->prepend($button);
		}
		return $toolbar;
	}


	//ProjectContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->getParameter('title')));
		$vbox->append('entry', array('name' => 'synopsis',
				'text' => _('Synopsis: '),
				'value' => $request->getParameter('synopsis')));
		$vbox->append('textview', array('name' => 'content',
				'text' => _('Description: '),
				'value' => $request->getParameter('content')));
		$combobox = $vbox->append('combobox', array('name' => 'scm',
				'text' => _('SCM: ')));
		$combobox->append('label', array('value' => '',
				'text' => _('(none)')));
		//FIXME list the SCMs available
		$vbox->append('entry', array('name' => 'cvsroot',
				'text' => _('SCM root: '),
				'value' => $request->getParameter('cvsroot')));
		return $vbox;
	}

	protected function _formUpdate($engine, $request)
	{
		$vbox = new PageElement('vbox');
		//title
		if(($value = $request->getParameter('title')) === FALSE)
			$value = $this->getTitle();
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $value));
		//synopsis
		if(($value = $request->getParameter('synopsis')) === FALSE)
			$value = $this->get('synopsis');
		$vbox->append('entry', array('name' => 'synopsis',
				'text' => _('Synopsis: '),
				'value' => $value));
		//description
		$label = $vbox->append('label', array(
				'text' => _('Description: ')));
		if(($value = $request->getParameter('content')) === FALSE)
			$value = $this->getContent($engine);
		$label->append('textview', array('name' => 'content',
				'value' => $value));
		//SCM
		if(($value = $request->getParameter('scm')) === FALSE)
			$value = $this->get('scm');
		$vbox->append('entry', array('name' => 'scm',
				'text' => _('SCM: '),
				'value' => $value));
		if(($value = $request->getParameter('cvsroot')) === FALSE)
			$value = $this->get('cvsroot');
		$vbox->append('entry', array('name' => 'cvsroot',
				'text' => _('Repository: '),
				'value' => $value));
		return $vbox;
	}


	//ProjectContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = $this->project_query_insert;

		$this->set('synopsis', $request->getParameter('synopsis'));
		$this->set('scm', $request->getParameter('scm'));
		$this->set('cvsroot', $request->getParameter('cvsroot'));
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$error = _('Could not insert the project');
		$args = array('project_id' => $this->getID(),
			'synopsis' => $this->get('synopsis'),
			'scm' => $this->get('scm'),
			'cvsroot' => $this->get('cvsroot'));
		if($database->query($engine, $query, $args)
				=== FALSE)
			return FALSE;
		return TRUE;
	}

	protected function _saveUpdate($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = $this->project_query_update;
		$args = array('project_id' => $this->getID());
		$fields = array('synopsis', 'scm', 'cvsroot');

		if(($ret = parent::_saveUpdate($engine, $request, $error))
				=== FALSE)
			return FALSE;
		$error = _('Could not update the project');
		foreach($fields as $f)
		{
			$args[$f] = $this->get($f);
			if($request === FALSE)
				continue;
			if(($v = $request->getParameter($f)) !== FALSE)
				$args[$f] = $v;
		}
		if($database->query($engine, $query, $args)
				=== FALSE)
			return FALSE;
		foreach($fields as $f)
			$this->$f = $args[$f];
		return $ret;
	}


	//static
	//methods
	//ProjectContent::countAll
	static public function countAll($engine, $module, $user = FALSE)
	{
		$class = get_class();
		$class::$query_list_count = $class::$project_query_list_count;
		$class::$query_list_user_count
			= $class::$project_query_list_user_count;

		return $class::_countAll($engine, $module, $user, $class);
	}


	//ProjectContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $user = FALSE, $order = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'title ASC';
				break;
		}
		$class::$query_list = $class::$project_query_list;
		$class::$query_list_user = $class::$project_query_list_user;
		return $class::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}


	//ProjectContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		$class = get_class();

		$class::$query_load = $class::$project_query_load;
		return $class::_load($engine, $module, $id, $title, $class);
	}


	//protected
	//properties
	//queries
	//IN:	project_id
	//	synopsis
	//	cvsroot
	protected $project_query_insert = 'INSERT INTO
		daportal_project(project_id, synopsis, scm, cvsroot)
		VALUES (:project_id, :synopsis, :scm, :cvsroot)';
	//IN:	module_id
	static protected $project_query_list = 'SELECT content_id AS id,
		daportal_content_public.enabled AS enabled, timestamp,
		name AS module, daportal_user_enabled.user_id AS user_id,
		username, title, synopsis, scm, cvsroot
		FROM daportal_content_public, daportal_module,
		daportal_user_enabled, daportal_project
		WHERE daportal_content_public.module_id
		=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_public.content_id
		=daportal_project.project_id';
	//IN:	module_id
	static protected $project_query_list_count = 'SELECT COUNT(*) AS count
		FROM daportal_content_public, daportal_module,
		daportal_user_enabled, daportal_project
		WHERE daportal_content_public.module_id
		=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_public.content_id
		=daportal_project.project_id';
	//IN:	project_id
	protected $project_query_list_downloads = "SELECT
		daportal_download.content_id AS id, download.title AS title,
		download.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username, groupname,
		mode
		FROM daportal_project_download, daportal_content project,
		daportal_download, daportal_content download,
		daportal_user_enabled, daportal_group
		WHERE daportal_project_download.project_id=project.content_id
		AND daportal_project_download.download_id=daportal_download.content_id
		AND daportal_download.content_id=download.content_id
		AND download.user_id=daportal_user_enabled.user_id
		AND download.group_id=daportal_group.group_id
		AND project.public='1' AND project.enabled='1'
		AND download.public='1' AND download.enabled='1'
		AND project_id=:project_id
		ORDER BY download.timestamp DESC";
	//IN:	project_id
	protected $project_query_list_screenshots = "SELECT
		daportal_download.content_id AS id, download.title title
		FROM daportal_project_screenshot, daportal_content project,
		daportal_download, daportal_content download
		WHERE daportal_project_screenshot.project_id=project.content_id
		AND daportal_project_screenshot.download_id
		=daportal_download.content_id
		AND daportal_download.content_id=download.content_id
		AND project.public='1' AND project.enabled='1'
		AND download.public='1' AND download.enabled='1'
		AND project_id=:project_id
		ORDER BY download.timestamp DESC";
	//IN:	module_id
	//	user_id
	static protected $project_query_list_user = 'SELECT content_id AS id,
		daportal_content_public.enabled AS enabled, timestamp,
		name AS module, daportal_user_enabled.user_id AS user_id,
		username, title, synopsis, scm, cvsroot
		FROM daportal_content_public, daportal_module,
		daportal_user_enabled, daportal_project
		WHERE daportal_content_public.module_id
		=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_public.content_id
		=daportal_project.project_id
		AND daportal_content_public.user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $project_query_list_user_count = 'SELECT
		COUNT(*) AS count
		FROM daportal_content_public, daportal_module,
		daportal_user_enabled, daportal_project
		WHERE daportal_content_public.module_id
		=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_public.content_id
		=daportal_project.project_id
		AND daportal_content_public.user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $project_query_load = "SELECT project_id AS id,
		timestamp, title, daportal_module.module_id AS module_id,
		daportal_module.name AS module,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username, content, synopsis,
		scm, cvsroot, daportal_content.enabled AS enabled, public
		FROM daportal_content, daportal_module, daportal_project,
		daportal_user_enabled
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.content_id=daportal_project.project_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.enabled='1'
		AND (daportal_content.public='1'
		OR daportal_content.user_id=:user_id)
		AND project_id=:content_id";
	//IN:	project_id
	//	synopsis
	//	scm
	//	cvsroot
	protected $project_query_update = 'UPDATE daportal_project
		SET synopsis=:synopsis, scm=:scm, cvsroot=:cvsroot
		WHERE project_id=:project_id';
}

?>
