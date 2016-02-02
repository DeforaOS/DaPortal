<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//ProjectModule
class ProjectModule extends MultiContentModule
{
	//public
	//methods
	//essential
	//ProjectModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		if($internal)
			return parent::call($engine, $request, $internal);
		switch(($action = $request->getAction()))
		{
			case 'bug_list':
				return $this->callBugList($engine, $request);
			case 'bug_reply':
				return $this->callBugReply($engine, $request);
			case 'browse':
			case 'gallery':
			case 'members':
			case 'homepage':
			case 'timeline':
				return $this->callDisplay($engine, $request);
			case 'bugList':
			case 'bugReply':
			case 'download':
			case 'latest':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return parent::call($engine, $request, $internal);
	}


	//static
	//useful
	//ProjectModule::attachSCM
	static public function attachSCM(Engine $engine, $name, &$error = FALSE)
	{
		//XXX use $module->getName()
		$module = 'project';
		$error = _('No SCM configured for this project');

		if(strpos($name, '/') !== FALSE || strlen($name) == 0)
			return FALSE;
		$error = _('Internal server error');
		$filename = "./modules/$module/scm/$name.php";
		$res = include_once($filename);
		if($res === FALSE)
			return FALSE;
		$name = $name.'SCMProject';
		$ret = new $name();
		$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret));
		$ret->attach($engine);
		return $ret;
	}


	//protected
	static protected $content_classes = array('project' => 'ProjectContent',
		'bug' => 'BugProjectContent',
		'bugreply' => 'BugReplyProjectContent',
		'download' => 'DownloadProjectContent',
		'screenshot' => 'ScreenshotProjectContent');
	//properties
	//queries
	//FIXME use daportal_user_enabled and daportal_content_public
	static protected $query_list_admin_bugs = "SELECT
		daportal_content.content_id AS id, bug_id,
		daportal_content.enabled AS enabled,
		daportal_content.public AS public,
		timestamp, name AS module,
		daportal_user.user_id AS user_id, username, title
		FROM daportal_content, daportal_module, daportal_user,
		daportal_bug
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.user_id=daportal_user.user_id
		AND daportal_user.enabled='1'
		AND daportal_content.content_id=daportal_bug.content_id";
	static protected $query_list_admin_bugs_count = "SELECT
		COUNT(*) AS count
		FROM daportal_content, daportal_module, daportal_user,
		daportal_bug
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.user_id=daportal_user.user_id
		AND daportal_user.enabled='1'
		AND daportal_content.content_id=daportal_bug.content_id";
	//IN:	module_id
	static protected $query_list_bugs = 'SELECT bug.content_id AS id,
		bug.timestamp AS timestamp,
		bug.module_id AS module_id, bug.module AS module,
		bug.user_id AS user_id, bug.username AS username,
		bug.group_id AS group_id, bug.groupname AS groupname,
		bug.title AS title, bug.content AS content,
		bug.enabled AS enabled, bug.public AS public,
		bug_id, type, state, priority, assigned,
		daportal_bug.project_id AS project_id,
		project.title AS project
		FROM daportal_content_public bug, daportal_bug,
		daportal_content_public project, daportal_project
		WHERE bug.content_id=daportal_bug.content_id
		AND project.content_id=daportal_bug.project_id
		AND daportal_project.project_id=project.content_id
		AND bug.module_id=:module_id';
	//IN:	project_id
	//	download_id
	static protected $project_query_project_download_insert = 'INSERT INTO
		daportal_project_download (project_id, download_id)
		VALUES (:project_id, :download_id)';


	//methods
	//essential
	//ProjectModule::ProjectModule
	protected function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Projects') : $title;
		$this->content_list_count = 20;
		parent::__construct($id, $name, $title);
	}


	//accessors
	//ProjectModule::canDownload
	protected function canDownload(Engine $engine, Request $request = NULL,
			Content $content = NULL)
	{
		if($content === NULL)
			$content = new ProjectContent($engine, $this);
		return $content->canDownload($engine, $request);
	}


	//ProjectModule::canUpload
	protected function canUpload(Engine $engine, Request $request = NULL,
			Content $content = NULL)
	{
		if($content === NULL)
			$content = new ProjectContent($engine, $this);
		return $content->canUpload($engine, $request);
	}


	//ProjectModule::getBugByID
	protected function getBugByID(Engine $engine, $id)
	{
		return BugProjectContent::loadFromBugID($engine, $this, $id);
	}


	//ProjectModule::getFilter
	protected function getFilter(Engine $engine, Request $request)
	{
		$r = new Request($this->name, 'bug_list');
		$form = new PageElement('form', array('request' => $r,
				'idempotent' => TRUE));
		$hbox = $form->append('hbox');
		$vbox1 = $hbox->append('vbox');
		$vbox2 = $hbox->append('vbox');
		//FIXME fetch the project name in additional cases
		$vbox1->append('entry', array('name' => 'project',
			'value' => $request->get('project'),
			'text' => _('Project: ')));
		$vbox2->append('entry', array('name' => 'username',
			'value' => $request->get('username'),
			'text' => _('Submitted by: ')));
		//FIXME implement the rest
		$bbox = $vbox2->append('hbox');
		$bbox->append('button', array('stock' => 'reset',
				'type' => 'reset',
				'text' => _('Reset')));
		$bbox->append('button', array('stock' => 'submit',
				'type' => 'submit',
				'text' => _('Filter')));
		return $form;
	}


	//ProjectModule::getMembers
	protected function getMembers(Engine $engine, ProjectContent $project)
	{
		return $project->getMembers($engine);
	}


	//ProjectModule::_getProjectByName
	protected function _getProjectByName(Engine $engine, $name)
	{
		return ProjectContent::loadFromName($engine, $this, $name);
	}


	//ProjectModule::isManager
	protected function isManager(Engine $engine, ProjectContent $project)
	{
		$cred = $engine->getCredentials();

		if($cred->isAdmin()
				|| $project->getUserID() == $cred->getUserID())
			return TRUE;
		return FALSE;
	}


	//ProjectModule::isMember
	protected function isMember(Engine $engine, ProjectContent $project)
	{
		$cred = $engine->getCredentials();

		if(($members = $project->getMembers($engine)) === FALSE)
			return FALSE;
		$uid = $cred->getUserID();
		if($project->getUserID() == $uid)
			return TRUE;
		foreach($members as $m)
			if($m->getUserID() == $uid)
				return TRUE;
		return FALSE;
	}


	//ProjectModule::setContext
	protected function setContext(Engine $engine = NULL,
			Request $request = NULL, Content $content = NULL)
	{
		parent::setContext($engine, $request, $content);
		switch($this->content_class)
		{
			case 'BugProjectContent':
				$this->text_content_admin
					= _('Bugs administration');
				$this->text_content_list_title
					= _('Bug reports');
				$this->text_content_list_title_by
					= _('Bugs reported by');
				$this->text_content_list_title_by_group
					= _('Bugs reported by group');
				$this->text_content_submit_content
					= _('Report bug');
				break;
			case 'BugReplyProjectContent':
				$this->text_content_admin
					= _('Bug replies administration');
				$this->text_content_list_title
					= _('Bug replies');
				$this->text_content_list_title_by
					= _('Bug replies by');
				$this->text_content_list_title_by_group
					= _('Bug replies by group');
				$this->text_content_submit_content
					= _('Reply to a bug');
				break;
			case 'DownloadProjectContent':
				$this->text_content_admin
					= _('Project downloads administration');
				$this->text_content_list_title
					= _('Project download list');
				$this->text_content_list_title_by
					= _('Project downloads from');
				$this->text_content_list_title_by_group
					= _('Project downloads from group');
				$this->text_content_submit_content
					= _('New download');
				break;
			case 'ScreenshotProjectContent':
				$this->text_content_admin
					= _('Project screenshots administration');
				$this->text_content_list_title
					= _('Project screenshot list');
				$this->text_content_list_title_by
					= _('Project screenshots from');
				$this->text_content_list_title_by_group
					= _('Project screenshots from group');
				$this->text_content_submit_content
					= _('New screenshot');
				break;
			default:
			case 'ProjectContent':
				$this->text_content_admin
					= _('Projects administration');
				$this->text_content_list_title
					= _('Project list');
				$this->text_content_list_title_by
					= _('Projects from');
				$this->text_content_list_title_by_group
					= _('Projects from group');
				$this->text_content_submit_content
					= _('New project');
				break;
		}
	}


	//calls
	//ProjectModule::callAdmin
	protected function callAdmin(Engine $engine, Request $request)
	{
		switch($request->get('type'))
		{
			case 'bug':
				return $this->_adminBugs($engine, $request);
			case 'project':
			default:
				return parent::callAdmin($engine, $request);
		}
	}

	private function _adminBugs(Engine $engine, Request $request)
	{
		//FIXME also set the columns
		$this->text_content_admin = _('Bugs administration');
		static::$query_list_admin = static::$query_list_admin_bugs;
		static::$query_list_admin_count
			= static::$query_list_admin_bugs_count;
		return parent::callAdmin($engine, $request);
	}


	//ProjectModule::callBugList
	protected function callBugList(Engine $engine, Request $request)
	{
		$db = $engine->getDatabase();
		$title = _('Bug reports');
		$error = FALSE;
		//FIXME use BugProjectContent::listAll() instead
		$query = static::$query_list_bugs;
		$project = FALSE;

		//XXX unlike ProjectModule::list() here getID() is the project
		//determine the current project
		if(($id = $request->getID()) !== FALSE
				&& ($project = $this->getContent($engine, $id,
					$request->getTitle(), $request))
					=== FALSE)
			$error = _('Unknown project');
		else if(($name = $request->get('project')) !== FALSE
				&& strlen($name) > 0)
		{
			if(($project = $this->_getProjectByName($engine,
					$name)) !== FALSE)
				$id = $project->getID();
			else
				$error = _('Unknown project');
		}
		$args = array('module_id' => $this->id);
		if($project !== FALSE)
		{
			$title = _('Bug reports for ').$project->getTitle();
			$query .= ' AND daportal_bug.project_id=:project_id';
			$args['project_id'] = $id;
		}
		$filter = $this->getFilter($engine, $request);
		//filter by user_id
		if(($uid = $request->get('user_id')) !== FALSE)
		{
			$title .= _(' by ').$uid; //XXX
			$query .= ' AND bug.user_id=:user_id';
			$args['user_id'] = $uid;
		}
		//sorting out
		switch(($order = $request->get('sort')))
		{
			case 'date':	$order = 'bug.timestamp DESC';	break;
			case 'title':	$order = 'bug.title ASC';	break;
			default:	$order = 'bug_id DESC';		break;
		}
		$query .= ' ORDER BY '.$order;
		//obtain the corresponding bug reports
		if($error !== FALSE)
			$res = FALSE;
		else if(($res = $db->query($engine, $query, $args)) === FALSE)
		{
			$res = FALSE;
			$error = _('Unable to list bugs');
		}
		else
			$res = new ContentResult($engine, $this,
				'BugProjectContent', $res);
		//build the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if($project !== FALSE)
			$page->append($project->displayToolbar($engine,
					$request));
		if($error !== FALSE)
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		if($filter !== FALSE)
			$page->append($filter);
		$view = $page->append('treeview');
		$view->set('columns', BugProjectContent::getColumns());
		if($res === FALSE)
			return new PageResponse($page);
		foreach($res as $r)
			$view->append($r->displayRow($engine, $request));
		return new PageResponse($page);
	}


	//ProjectModule::callBugReply
	protected function callBugReply(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();
		$user = new User($engine, $cred->getUserID());

		if(($bug = BugProjectContent::load($engine, $this,
				$request->getID(), $request->getTitle()))
				=== FALSE)
			return $this->callDefault($engine, new Request());
		$project = $this->getContent($engine, $bug->get('project_id'));
		$title = sprintf(_('Reply to #%u/%s: %s'), $bug->get('bug_id'),
				$project->getTitle(), $bug->getTitle());
		$page = new Page(array('title' => $title));
		//title
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//FIXME process the request
		//bug
		$vbox = $page->append('vbox'); //XXX for the title level
		$vbox->append($bug->display($engine, $request));
		//preview
		if($request->get('preview') !== FALSE)
		{
			$title = $request->get('title');
			$content = $request->get('content');
			$reply = array('title' => _('Preview: ').$title,
					'user_id' => $user->getUserID(),
					'username' => $user->getUsername(),
					'content' => $content);
			$reply = new BugReplyProjectContent($engine, $this,
					$reply);
			$vbox->append($reply->display($engine, $request));
		}
		//form
		$form = $this->formBugReply($engine, $request, $bug, $project);
		$vbox->append($form);
		return new PageResponse($page);
	}


	//ProjectModule::callDefault
	protected function callDefault(Engine $engine, Request $request)
	{
		$title = _('Projects');
		$latest = $this->canDownload($engine, $request)
			? array('project', 'download', 'bug', 'screenshot')
			: array('project', 'bug');

		if($request !== NULL && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->getName(),
			'text' => $title));
		$hbox = $page->append('hbox');
		foreach($latest as $l)
		{
			$request = $this->getRequest('latest', array(
				'type' => $l));
			$response = $this->call($engine, $request);
			if($response instanceof PageResponse)
				$hbox->append($response->getContent());
		}
		return new PageResponse($page);
	}


	//ProjectModule::callDownload
	protected function callDownload(Engine $engine, Request $request)
	{
		if($request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$request = $this->getRequest('latest', array(
				'type' => 'download'));
		return $this->callLatest($engine, $request);
	}


	//ProjectModule::callLatest
	protected function callLatest(Engine $engine, Request $request)
	{
		$type = $request->get('type');

		switch($type)
		{
			case 'bug':
				return $this->_latestBugReports($engine,
						$request);
			case 'download':
				return $this->_latestDownloads($engine,
						$request);
			case 'screenshot':
				return $this->_latestScreenshots($engine,
						$request);
			case 'project':
			default:
				return $this->_latestProjects($engine,
						$request);
		}
	}

	private function _latestBugReports(Engine $engine, Request $request)
	{
		$title = _('Latest bug reports');

		//list the latest bug reports
		$page = new Page($title);
		$page->append('title', array('stock' => $this->getName(),
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($bugs = BugProjectContent::listAll($engine, $this,
				'timestamp', $this->content_headline_count))
				=== FALSE)
		{
			$error = _('Could not list bug reports');
			return new ErrorResponse($error);
		}
		$columns = BugProjectContent::getColumns();
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($bugs as $b)
			$view->append($b->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More bug reports...'),
			'request' => $this->getRequest('list', array(
				'type' => 'bug'))));
		return new PageResponse($page);
	}

	private function _latestProjects(Engine $engine, Request $request)
	{
		$title = _('Latest projects');

		//list the latest projects
		$page = new Page($title);
		$page->append('title', array('stock' => $this->getName(),
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($projects = ProjectContent::listAll($engine, $this,
				'timestamp', $this->content_headline_count))
				=== FALSE)
		{
			$error = _('Could not list projects');
			return new ErrorResponse($error);
		}
		$columns = ProjectContent::getColumns();
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($projects as $p)
			$view->append($p->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More projects...'),
			'request' => $this->getRequest('list', array(
				'type' => 'project'))));
		return new PageResponse($page);
	}

	private function _latestDownloads(Engine $engine, Request $request)
	{
		$title = _('Latest downloads');

		//list the latest downloads
		$page = new Page($title);
		$page->append('title', array('stock' => 'download',
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($downloads = DownloadProjectContent::listAll($engine, $this,
				'timestamp', $this->content_headline_count))
				=== FALSE)
		{
			$error = _('Could not list downloads');
			return new ErrorResponse($error);
		}
		$columns = DownloadProjectContent::getColumns();
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($downloads as $d)
			$view->append($d->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More downloads...'),
			'request' => $this->getRequest('list', array(
				'type' => 'download'))));
		return new PageResponse($page);
	}

	private function _latestScreenshots(Engine $engine, Request $request)
	{
		$title = _('Latest screenshots');

		//list the latest screenshots
		$page = new Page($title);
		$page->append('title', array('stock' => 'gallery',
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($screenshots = ScreenshotProjectContent::listAll($engine,
				$this, 'timestamp', $this->content_headline_count))
				=== FALSE)
		{
			$error = _('Could not list screenshots');
			return new ErrorResponse($error);
		}
		$columns = ScreenshotProjectContent::getColumns();
		$view = $vbox->append('treeview', array(
			'view' => 'thumbnails'));
		foreach($screenshots as $s)
			$view->append($s->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More screenshots...'),
			'request' => $this->getRequest('list', array(
				'type' => 'screenshot'))));
		return new PageResponse($page);
	}


	//ProjectModule::callSubmitRelease
	protected function callSubmitRelease(Engine $engine, Request $request)
	{
		$project = $this->getContent($engine, $request->getID(),
				$request->getTitle(), $request);

		$error = _('Invalid project');
		if($project === FALSE)
			return new ErrorResponse($error,
				Response::$CODE_ENOENT);
		$error = _('Permission denied');
		if(!$this->canUpload($engine, $request, $project))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		$title = _('New download for project ').$project->getTitle();
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//process the request
		if(($error = $this->_submitProcessRelease($engine, $request,
				$project, $content)) === FALSE)
			return $this->_submitSuccessRelease($engine, $request,
					$page, $content);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//form
		$form = $this->formSubmitRelease($engine, $request, $project);
		$page->append($form);
		return new PageResponse($page);
	}

	protected function _submitProcessRelease(Engine $engine,
			Request $request, $project, &$content)
	{
		$db = $engine->getDatabase();
		$query = static::$project_query_project_download_insert;

		//verify the request
		if($request->isIdempotent())
			return TRUE;
		//FIXME obtain the download path
		//XXX this assumes the file was just being uploaded
		$r = new Request('download', 'submit', FALSE, FALSE,
			array('submit' => 'submit'));
		$r->setIdempotent(FALSE);
		if($engine->process($r, TRUE) === FALSE)
			return _('Internal server error');
		//XXX ugly (and race condition)
		//XXX using download_id to workaround a bug in getLastID()
		if(($did = $db->getLastID($engine, 'daportal_download',
				'download_id')) === FALSE)
			return _('Internal server error');
		$q = 'SELECT content_id AS id FROM daportal_download'
			.' WHERE download_id=:download_id';
		$args = array('download_id' => $did);
		if(($res = $db->query($engine, $q, $args)) === FALSE
				|| count($res) != 1)
			return _('Internal server error');
		$res = $res->current();
		$did = $res['id'];
		$args = array('project_id' => $project['id'],
			'download_id' => $did);
		if($db->query($engine, $query, $args) === FALSE)
			return _('Internal server error');
		$content = Content::load($engine, $this, $project['id'],
				$project['title']);
		return FALSE;
	}

	protected function _submitSuccessRelease(Engine $engine,
			Request $request, $page, Content $content)
	{
		$r = new Request($this->name, 'download', $content->getID(),
			$content->getTitle());
		return $this->helperRedirect($engine, $r, $page,
				$this->text_content_submit_progress); //XXX
	}


	//forms
	//ProjectModule::formBugReply
	protected function formBugReply(Engine $engine, Request $request,
			BugProjectContent $bug, $project)
	{
		$r = new Request($this->name, 'bugReply', $request->getID(),
			$request->getTitle());
		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		$title = $request->get('title');
		$vbox->append('entry', array('text' => _('Title: '),
				'name' => 'title', 'value' => $title));
		$vbox->append('textview', array('text' => _('Content: '),
				'name' => 'content',
				'value' => $request->get('content')));
		//FIXME really implement
		$r = $bug->getRequest();
		$box = $vbox->append('buttonbox');
		$box->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel', 'text' => _('Cancel')));
		$box->append('button', array('type' => 'submit',
				'stock' => 'preview', 'name' => 'action',
				'value' => 'preview', 'text' => _('Preview')));
		//FIXME add missing buttons
		return $form;
	}


	//ProjectModule::formSubmitRelease
	protected function formSubmitRelease(Engine $engine, Request $request,
			ProjectContent $project)
	{
		$r = new Request($this->name, 'submit', $project->getID(),
			$project->getTitle(), array('type' => 'download'));
		$form = new PageElement('form', array('request' => $r));
		$form->append('filechooser', array('text' => _('File: '),
				'name' => 'files[]'));
		$value = $request->get('directory');
		$form->append('entry', array('text' => _('Directory: '),
				'name' => 'directory', 'value' => $value));
		$r = new Request($this->name, 'download', $project->getID(),
			$project->getTitle());
		$form->append('button', array('stock' => 'cancel',
				'request' => $r,
				'target' => '_cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'text' => _('Submit'),
				'name' => 'submit', 'value' => 'submit'));
		return $form;
	}


	//helpers
	//ProjectModule::helperDisplayBug
	protected function helperDisplayBug(Engine $engine, Request $request,
			PageElement $page, $content)
	{
		$project = $this->getContent($engine, $content['project_id']);
		$c = $content;

		//title
		$c['title'] = sprintf(_('#%u/%s: %s'),
				$c['bug_id'], $project['title'], $c['title']);
		$this->helperDisplayTitle($engine, $request, $page, $c);
		//toolbar
		//FIXME pages should render as vbox by default
		$vbox = $page->append('vbox');
		$this->helperDisplayToolbar($engine, $request, $vbox, $project);
		$this->helperDisplayBugMetadata($engine, $request, $vbox,
				$content, $project);
		//content
		$this->helperDisplayText($engine, $request, $vbox, $content);
		//buttons
		$r = new Request($this->name, 'bugReply', $content['id'],
			$content['title']);
		$vbox->append('button', array('request' => $r,
				'stock' => 'reply', 'text' => _('Reply')));
		$this->helperDisplayButtons($engine, $request, $vbox, $content);
	}


	//ProjectModule::helperDisplayBugMetadata
	protected function helperDisplayBugMetadata(Engine $engine,
			Request $request, PageElement $page, $bug,
			ProjectContent $project)
	{
		global $config;
		$encoding = $config->get('defaults', 'charset');
		$sep = ' ';

		if($encoding !== FALSE && function_exists('iconv')
				&& ($s = iconv('utf-8', $encoding, $sep))
				!== FALSE)
			$sep = $s;
		$r = new Request($this->name, FALSE, $project->getID(),
			$project->getTitle());
		$u = new Request($this->name, 'list', $bug['user_id'],
			$bug['username']);
		$user = is_numeric($bug['assigned'])
			? new User($engine, $bug['assigned']) : FALSE;
		$a = ($user !== FALSE)
			? new Request($this->name, 'list', $user->getUserID(),
				$user->getUsername()) : FALSE;

		$page = $page->append('hbox');
		$col1 = $page->append('vbox');
		$col2 = $page->append('vbox');
		$col3 = $page->append('vbox');
		$col4 = $page->append('vbox');
		//project
		$col1->append('label', array('class' => 'bold',
				'text' => _('Project: ')));
		$col2->append('link', array('class' => 'bold', 'request' => $r,
				'text' => $project->getTitle()));
		//submitter
		$col3->append('label', array('class' => 'bold',
				'text' => _('Submitter: ')));
		$col4->append('link', array('request' => $u,
				'text' => $bug['username']));
		//date
		$col1->append('label', array('class' => 'bold',
				'text' => _('Date: ')));
		//XXX should span across columns instead
		$col2->append('label', array('text' => $bug['date']));
		$col3->append('label', array('text' => $sep));
		$col4->append('label', array('text' => $sep));
		//state
		$col1->append('label', array('class' => 'bold',
				'text' => _('State: ')));
		$col2->append('label', array('text' => $bug['state']));
		//type
		$col3->append('label', array('class' => 'bold',
				'text' => _('Type: ')));
		$col4->append('label', array('text' => $bug['type']));
		//priority
		$col1->append('label', array('class' => 'bold',
				'text' => _('Priority: ')));
		$col2->append('label', array('text' => $bug['priority']));
		//assigned
		$col3->append('label', array('class' => 'bold',
				'text' => _('Assigned to: ')));
		if($a !== FALSE)
			$col4->append('link', array('request' => $a,
				'text' => $user->getUsername()));
		else
			$col4->append('label', array('text' => $sep));
	}


	//ProjectModule::helperDisplayBugReply
	protected function helperDisplayBugReply(Engine $engine,
			Request $request, PageElement $page, $content)
	{
		$bug = $this->getBugByID($engine, $content['bug_id']);
		$project = ($bug !== FALSE)
			? $this->getContent($engine, $bug['project_id'])
			: FALSE;

		if($bug === FALSE || $project === FALSE)
		{
			$page->append('dialog', array('type' => 'error',
					'text' => _('An error occurred')));
			return;
		}
		//bug
		$this->helperDisplayBug($engine, $request, $page, $bug);
		$this->helperDisplayBugMetadata($engine, $request, $page,
				$content, $project);
		//content
		$this->helperDisplayText($engine, $request, $page, $content);
	}


	//ProjectModule::helperListButtons
	protected function helperListButtons(Engine $engine, PageElement $page,
			Request $request = NULL)
	{
	}


	//ProjectModule::helperListView
	protected function helperListView(Engine $engine,
			Request $request = NULL)
	{
		$view = parent::helperListView($engine, $request);
		if($request->get('type') == 'screenshot')
		{
			$view->set('columns', FALSE);
			$view->set('view', 'thumbnails');
		}
		return $view;
	}


	//ProjectModule::helperPreviewMetadata
	protected function helperPreviewMetadata(Engine $engine, $preview,
			Request $request, $content = FALSE)
	{
		if($this->content_class == 'BugProjectContent')
			return parent::helperPreviewMetadata($engine, $preview,
					$request, $content);
		parent::helperPreviewMetadata($engine, $preview, $request,
				$content);
		if(isset($content['synopsis']))
			$preview->append('label', array('class' => 'bold',
					'text' => $content->get('synopsis')));
	}
}

?>
