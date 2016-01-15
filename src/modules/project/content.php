<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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



require_once('./modules/project/scm.php');


//ProjectContent
class ProjectContent extends ContentMulti
{
	//public
	//methods
	//essential
	//ProjectContent::ProjectContent
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		$this->fields['synopsis'] = 'Synopsis';
		$this->fields['scm'] = 'SCM';
		$this->fields['cvsroot'] = 'Repository';
		parent::__construct($engine, $module, $properties);
		//translations
		$this->text_content_by = _('Project from');
		$this->text_content_list_title = _('Project list');
		$this->text_more_content = _('More projects...');
		$this->text_submit = _('Submit');
		$this->text_submit_content = _('New project');
	}


	//accessors
	//ProjectContent::canBrowse
	public function canBrowse(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$error = _('No SCM configured for this project');

		if(($scm = $this->get('scm')) === FALSE || strlen($scm) == 0)
			return FALSE;
		return TRUE;
	}


	//ProjectContent::canDownload
	public function canDownload(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(Module::load($engine, 'download') === FALSE)
		{
			$error = _('The download module is not available');
			return FALSE;
		}
		return TRUE;
	}


	//ProjectContent::canUpload
	public function canUpload(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if($this->canDownload($engine, $request, $error) === FALSE)
			return FALSE;
		//FIXME really implement
		return $credentials->isAdmin();
	}


	//ProjectContent::getColumns
	static public function getColumns()
	{
		return array('icon' => '', 'title' => _('Title'),
			'username' => _('Manager'), 'date' => _('Date'),
			'synopsis' => _('Description'));
	}


	//ProjectContent::getMembers
	public function getMembers(Engine $engine, $order = FALSE,
			$limit = FALSE, $offset = FALSE)
	{
		$db = $engine->getDatabase();
		$query = static::$project_query_list_members;
		$args = array('project_id' => $this->getID());

		if(($user = $this->getOwner($engine)) === FALSE)
			return FALSE;
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return FALSE;
		$ret = array($user);
		foreach($res as $r)
		{
			$user = new ProjectUser($engine, $r['user_id'],
				$r['username']);
			$user->setProjectAdmin($this, ($db->isTrue($r['admin']))
					? TRUE : FALSE);
			$ret[] = $user;
		}
		return $ret;
	}


	//ProjectUser::getOwner
	public function getOwner(Engine $engine)
	{
		$db = $engine->getDatabase();
		$query = static::$project_query_get_user;
		$args = array('project_id' => $this->getID());

		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| $res->count() != 1)
			return FALSE;
		$res = $res->current();
		$user = new ProjectUser($engine, $res['user_id'],
				$res['username']);
		$user->setProjectAdmin($this, TRUE);
		return $user;
	}


	//useful
	//ProjectContent::display
	public function display(Engine $engine, Request $request = NULL)
	{
		switch(($action = $request->getAction()))
		{
			case 'download':
			case 'gallery':
				if($this->canDownload($engine, $request)
						=== FALSE)
					return parent::display($engine, $request);
				//fallback
			case 'browse':
			case 'members':
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

	protected function _displayBrowse(Engine $engine, Request $request,
			PageElement $page)
	{
		$class = get_class($this->getModule());
		$error = _('Unknown error');

		if($this->canBrowse($engine, $request, $error) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$error = _('Could not browse the project');
		if(($scm = $class::attachSCM($engine, $this->get('scm')))
				=== FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$browse = $scm->browse($engine, $this, $request);
		if(!($browse instanceof PageElement))
			//FIXME set the proper filename
			return $browse;
		$vbox = $page->append('vbox');
		$page->append($browse);
		return $page;
	}

	protected function _displayDownload(Engine $engine, Request $request,
			PageElement $page)
	{
		$class = get_class($this->getModule());
		$db = $engine->getDatabase();
		$query = static::$project_query_list_downloads;

		$vbox = $page->append('vbox');
		//source code
		if(($scm = $class::attachSCM($engine, $this->get('scm')))
				!== FALSE
				&& ($download = $scm->download($engine,
				$this, $request)) !== FALSE)
			$page->append($download);
		//downloads
		$error = 'Could not list downloads';
		$args = array('project_id' => $this->getID());
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		$vbox->append('title', array('text' => _('Downloads')));
		$columns = DownloadProjectContent::getColumns();
		unset($columns['project']);
		$columns['permissions'] = _('Permissions');
		$view = $vbox->append('treeview', array(
				'columns' => $columns));
		if($this->canUpload($engine, $request))
		{
			$toolbar = $view->append('toolbar');
			$req = $this->getRequest('submit', array(
				'type' => 'download'));
			$link = $toolbar->append('button', array(
					'stock' => 'new',
					'request' => $req,
					'text' => _('New download')));
		}
		$module = $this->getModule();
		foreach($res as $r)
		{
			$row = $view->append('row');
			$req = new Request($module->getName(), FALSE, $r['id'],
				$r['title']);
			$icon = Mime::getIcon($engine, $r['title'], 16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->set('icon', $icon);
			$filename = new PageElement('link', array(
					'request' => $req,
					'text' => $r['title']));
			$row->set('title', $filename);
			$req = new Request('user', FALSE, $r['user_id'],
				$r['username']);
			$username = new PageElement('link', array(
					'stock' => 'user',
					'request' => $req,
					'text' => $r['username']));
			$row->set('username', $username);
			$row->set('group', $r['groupname']);
			$date = $db->formatDate($r['timestamp']);
			$row->set('date', $date);
			$permissions = Common::getPermissions($r['mode'],
					static::$S_IFDIR);
			$permissions = new PageElement('label', array(
					'class' => 'preformatted',
					'text' => $permissions));
			$row->set('permissions', $permissions);
		}
		return $page;
	}

	protected function _displayGallery(Engine $engine, Request $request,
			PageElement $page)
	{
		$db = $engine->getDatabase();
		$query = static::$project_query_list_screenshots;

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
			$row->set('thumbnail', $thumbnail);
			$label = new PageElement('link', array(
					'request' => $req,
					'text' => $r['title']));
			$row->set('label', $label);
		}
		return $page;
	}

	protected function _displayMembers(Engine $engine, Request $request,
			PageElement $page)
	{
		$vbox = $page->append('vbox');
		//members
		$error = _('Could not list members');
		if(($res = $this->getMembers($engine)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		$vbox = $page->append('vbox');
		$vbox->append('title', array('text' => _('Members')));
		$columns = array('title' => _('Name'),
				'admin' => _('Administrator'));
		$view = $vbox->append('treeview', array('columns' => $columns,
				'view' => 'details'));
		$no = new PageElement('image', array('stock' => 'no',
			'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
			'size' => 16, 'title' => _('Enabled')));
		foreach($res as $user)
		{
			$r = new Request('user', FALSE, $user->getUserID(),
				$user->getUsername());
			$link = new PageElement('link', array('request' => $r,
				'stock' => 'user',
				'text' => $user->getUsername()));
			$row = $view->append('row', array('title' => $link));
			$row->set('admin', $user->isProjectAdmin($engine, $this)
					? $yes : $no);
		}
		return $page;
	}

	protected function _displayTimeline(Engine $engine, Request $request,
			PageElement $page)
	{
		$class = get_class($this->getModule());

		if($this->canBrowse($engine, $request, $error) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$vbox = $page->append('vbox');
		if(($scm = $class::attachSCM($engine, $this->get('scm')))
				=== FALSE)
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('An error occurred')));
		$timeline = $scm->timeline($engine, $this, $request);
		$vbox->append($timeline);
		return $page;
	}


	//ProjectContent::displayContent
	public function displayContent(Engine $engine, Request $request)
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
	public function displayRow(Engine $engine, Request $request = NULL)
	{
		$ret = parent::displayRow($engine, $request);
		$ret->set('synopsis', $this->get('synopsis'));
		return $ret;
	}


	//ProjectContent::displayToolbar
	public function displayToolbar(Engine $engine, Request $request = NULL)
	{
		$action = ($request !== NULL) ? $request->getAction() : FALSE;
		$actions = array('bug_list' => _('Bug reports'));

		if($this->canDownload($engine, $request))
		{
			$actions['download'] = _('Download');
			$actions['gallery'] = _('Gallery');
		}
		$actions['timeline'] = _('Timeline');
		$actions['browse'] = _('Browse');
		$actions['members'] = _('Members');
		$actions['homepage'] = _('Homepage');
		$toolbar = parent::displayToolbar($engine, $request);
		if($this->getID() === FALSE)
			return $toolbar;
		$browse = $this->canBrowse($engine, $request);
		foreach($actions as $k => $v)
		{
			if($k != 'homepage' && $action == $k)
				continue;
			if(!$browse && ($k == 'browse' || $k == 'timeline'))
				continue;
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
	public function form(Engine $engine, Request $request = NULL)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit(Engine $engine, Request $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->get('title')));
		$vbox->append('entry', array('name' => 'synopsis',
				'text' => _('Synopsis: '),
				'value' => $request->get('synopsis')));
		$vbox->append('textview', array('name' => 'content',
				'text' => _('Description: '),
				'value' => $request->get('content')));
		//SCM
		$combobox = $vbox->append('combobox', array('name' => 'scm',
				'text' => _('SCM: '),
				'value' => $request->get('scm')));
		$combobox->append('label', array('value' => '',
				'text' => _('(none)')));
		if(($scms = SCMProject::listAll($engine, $this->getModule()))
				!== FALSE)
			foreach($scms as $scm)
				$combobox->append('label', array(
						'value' => $scm,
						'text' => $scm));
		$vbox->append('entry', array('name' => 'cvsroot',
				'text' => _('Repository: '),
				'value' => $request->get('cvsroot')));
		return $vbox;
	}

	protected function _formUpdate(Engine $engine, Request $request)
	{
		$vbox = new PageElement('vbox');
		//title
		if(($value = $request->get('title')) === FALSE)
			$value = $this->getTitle();
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $value));
		//synopsis
		if(($value = $request->get('synopsis')) === FALSE)
			$value = $this->get('synopsis');
		$vbox->append('entry', array('name' => 'synopsis',
				'text' => _('Synopsis: '),
				'value' => $value));
		//description
		$label = $vbox->append('label', array(
				'text' => _('Description: ')));
		if(($value = $request->get('content')) === FALSE)
			$value = $this->getContent($engine);
		$label->append('textview', array('name' => 'content',
				'value' => $value));
		//SCM
		if(($value = $request->get('scm')) === FALSE)
			$value = $this->get('scm');
		$combobox = $vbox->append('combobox', array('name' => 'scm',
				'text' => _('SCM: '), 'value' => $value));
		$combobox->append('label', array('value' => '',
				'text' => _('(none)')));
		if(($scms = SCMProject::listAll($engine, $this->getModule()))
				!== FALSE)
			foreach($scms as $scm)
				$combobox->append('label', array(
						'value' => $scm,
						'text' => $scm));
		if(($value = $request->get('cvsroot')) === FALSE)
			$value = $this->get('cvsroot');
		$vbox->append('entry', array('name' => 'cvsroot',
				'text' => _('Repository: '),
				'value' => $value));
		return $vbox;
	}


	//ProjectContent::loadFromName
	static public function loadFromName(Engine $engine, Module $module,
			$name)
	{
		$query = static::$query_load_by_title;
		$args = array('module_id' => $module->getID(),
			'title' => $name);

		if(($res = static::query($engine, $query, $args)) === FALSE
				|| $res->count() != 1)
			return FALSE;
		return static::loadFromResult($engine, $module, $res);
	}


	//ProjectContent::save
	public function save(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert(Engine $engine, Request $request = NULL,
			&$error)
	{
		$database = $engine->getDatabase();
		$query = static::$project_query_insert;

		$this->set('synopsis', $request->get('synopsis'));
		$this->set('scm', $request->get('scm'));
		$this->set('cvsroot', $request->get('cvsroot'));
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

	protected function _saveUpdate(Engine $engine, Request $request = NULL,
			&$error)
	{
		$database = $engine->getDatabase();
		$query = static::$project_query_update;
		$args = array('project_id' => $this->getID());
		$fields = array('synopsis', 'scm', 'cvsroot');

		if(($ret = parent::_saveUpdate($engine, $request, $error))
				=== FALSE)
			return FALSE;
		$error = _('Could not update the project');
		foreach($fields as $f)
		{
			$args[$f] = $this->get($f);
			if($request === NULL)
				continue;
			if(($v = $request->get($f)) !== FALSE)
				$args[$f] = $v;
		}
		if($database->query($engine, $query, $args) === FALSE)
			return FALSE;
		foreach($fields as $f)
			$this->$f = $args[$f];
		return $ret;
	}


	//protected
	//properties
	static protected $S_IFDIR = 01000;
	static protected $class = 'ProjectContent';
	static protected $list_order = 'title ASC';
	//queries
	//IN:	project_id
	static protected $project_query_get_user = 'SELECT
		user_id, username
		FROM daportal_project, daportal_content_enabled
		WHERE daportal_project.project_id
		=daportal_content_enabled.content_id
		AND project_id=:project_id';
	//IN:	project_id
	//	synopsis
	//	cvsroot
	static protected $project_query_insert = 'INSERT INTO
		daportal_project(project_id, synopsis, scm, cvsroot)
		VALUES (:project_id, :synopsis, :scm, :cvsroot)';
	//IN:	project_id
	static protected $project_query_list_downloads = "SELECT
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
	static protected $project_query_list_members = 'SELECT
		daportal_user_enabled.user_id AS user_id, username,
		daportal_project_user.admin AS admin
		FROM daportal_project_user, daportal_user_enabled
		WHERE daportal_project_user.user_id
		=daportal_user_enabled.user_id
		AND project_id=:project_id
		ORDER BY username ASC';
	//IN:	project_id
	static protected $project_query_list_screenshots = "SELECT
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
	//IN:	project_id
	//	synopsis
	//	scm
	//	cvsroot
	static protected $project_query_update = 'UPDATE daportal_project
		SET synopsis=:synopsis, scm=:scm, cvsroot=:cvsroot
		WHERE project_id=:project_id';
	//IN:	module_id
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, synopsis, scm, cvsroot
		FROM daportal_content_public, daportal_project
		WHERE daportal_content_public.content_id=daportal_project.project_id
		AND module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, module_id, module,
		daportal_content_public.user_id AS user_id, username,
		daportal_content_public.group_id AS group_id,
		daportal_group_enabled.groupname AS groupname, title, content,
		daportal_content_public.enabled AS enabled, public, synopsis,
		scm, cvsroot
		FROM daportal_content_public, daportal_user_group,
		daportal_group_enabled, daportal_project
		WHERE daportal_content_public.content_id=daportal_project.project_id
		AND module_id=:module_id
		AND daportal_content_public.user_id=daportal_user_group.user_id
		AND daportal_user_group.group_id=daportal_group_enabled.group_id
		AND (daportal_user_group.group_id=:group_id
		OR daportal_content_public.group_id=:group_id)';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, synopsis, scm, cvsroot
		FROM daportal_content_public, daportal_project
		WHERE daportal_content_public.content_id=daportal_project.project_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public, synopsis,
		scm, cvsroot
		FROM daportal_content_enabled, daportal_project
		WHERE daportal_content_enabled.content_id=daportal_project.project_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, synopsis, scm, cvsroot
		FROM daportal_content_enabled, daportal_project
		WHERE daportal_content_enabled.content_id=daportal_project.project_id
		AND daportal_content_enabled.module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND content_id=:content_id";
	//IN:	module_id
	//	title
	static protected $query_load_by_title = 'SELECT content_id AS id,
		timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, synopsis, scm, cvsroot
		FROM daportal_content_public, daportal_project
		WHERE daportal_content_public.content_id=daportal_project.project_id
		AND daportal_content_public.module_id=:module_id
		AND title=:title';
}

?>
