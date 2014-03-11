<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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
require_once('./system/html.php');
require_once('./system/user.php');
require_once('./modules/content/multi.php');
require_once('./modules/project/content.php');
require_once('./modules/project/content/bug.php');
require_once('./modules/project/content/bugreply.php');


//ProjectModule
class ProjectModule extends MultiContentModule
{
	//public
	//methods
	//essential
	//ProjectModule::call
	public function call($engine, $request, $internal = 0)
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
			case 'download':
			case 'gallery':
			case 'homepage':
			case 'timeline':
				return $this->callDisplay($engine, $request);
			case 'bugList':
			case 'bugReply':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return parent::call($engine, $request, $internal);
	}


	//static
	//useful
	//ProjectModule::attachSCM
	static public function attachSCM($engine, $name)
	{
		global $config;

		if(strchr($name, '/') !== FALSE)
			return FALSE;
		$filename = './modules/project/scm/'.$name.'.php';
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
	//properties
	//queries
	//FIXME use daportal_user_enabled and daportal_content_public
	protected $project_query_bug_by_id = "SELECT
		daportal_bug.content_id AS id,
		title, content, timestamp, daportal_user.user_id AS user_id,
		daportal_user.username AS username, bug_id,
		project_id, state, type, priority, assigned
		FROM daportal_content_public, daportal_bug, daportal_user
		WHERE daportal_content_public.content_id=daportal_bug.content_id
		AND daportal_content_public.user_id=daportal_user.user_id
		AND daportal_bug.bug_id=:bug_id";
	protected $project_query_list_admin_bugs = "SELECT
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
	protected $project_query_list_admin_bugs_count = "SELECT
		COUNT(*) AS count
		FROM daportal_content, daportal_module, daportal_user,
		daportal_bug
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.user_id=daportal_user.user_id
		AND daportal_user.enabled='1'
		AND daportal_content.content_id=daportal_bug.content_id";
	protected $project_query_list_admin_projects = "SELECT content_id AS id,
		daportal_content.enabled AS enabled,
		daportal_content.public AS public,
		timestamp, name AS module,
		daportal_user_enabled.user_id AS user_id, username, title,
		synopsis
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_project
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.content_id=daportal_project.project_id";
	protected $project_query_list_admin_projects_count = "SELECT
		COUNT(*) AS count
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_project
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.content_id=daportal_project.project_id";
	protected $project_query_list_bugs = "SELECT bug_id,
		bug.content_id AS id, bug.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		bug.title AS title, bug.enabled AS enabled, state, type,
		priority, daportal_project.project_id AS project_id,
		project.title AS project
		FROM daportal_content_public bug, daportal_module,
		daportal_user_enabled, daportal_bug,
		daportal_content_public project, daportal_project
		WHERE bug.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND bug.user_id=daportal_user_enabled.user_id
		AND bug.content_id=daportal_bug.content_id
		AND daportal_bug.project_id=daportal_project.project_id
		AND project.content_id=daportal_project.project_id";
	protected $project_query_members = 'SELECT
		daportal_user_enabled.user_id AS user_id, username,
		daportal_project_user.admin AS admin
		FROM daportal_project_user, daportal_user_enabled
		WHERE daportal_project_user.user_id
		=daportal_user_enabled.user_id
		AND project_id=:project_id
		ORDER BY username ASC';
	protected $project_query_project = "SELECT
		daportal_module.name AS module, project_id AS id, title,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username, content, synopsis,
		scm, cvsroot, daportal_content.enabled AS enabled
		FROM daportal_content, daportal_module, daportal_project,
		daportal_user_enabled
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.content_id=daportal_project.project_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.enabled='1'
		AND daportal_content.public='1'
		AND project_id=:content_id";
	protected $project_query_project_by_name = "SELECT
		daportal_module.name AS module, project_id AS id,
		title, daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username, content, synopsis,
		scm, cvsroot, daportal_content.enabled AS enabled
		FROM daportal_content, daportal_module, daportal_project,
		daportal_user_enabled
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.content_id=daportal_project.project_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.enabled='1'
		AND daportal_content.public='1'
		AND daportal_content.title=:title";
	protected $project_query_project_insert = 'INSERT INTO
		daportal_project (project_id, synopsis, cvsroot)
		VALUES (:project_id, :synopsis, :cvsroot)';
	protected $project_query_project_release_insert = 'INSERT INTO
		daportal_project_download (project_id, download_id)
		VALUES (:project_id, :download_id)';
	protected $project_query_get = "SELECT daportal_module.name AS module,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username,
		daportal_content.content_id AS id, title, content, synopsis,
		scm, cvsroot, timestamp,
		daportal_bug.bug_id AS bug_id,
		daportal_bug.project_id AS project_id,
		daportal_bug.state AS state, daportal_bug.type AS type,
		daportal_bug.priority AS priority,
		daportal_bug.assigned AS assigned,
		bug_reply_id, daportal_bug_reply.bug_id AS bug_reply_bug_id,
		daportal_bug_reply.state AS bug_reply_state,
		daportal_bug_reply.type AS bug_reply_type,
		daportal_bug_reply.priority AS bug_reply_priority,
		daportal_bug_reply.assigned AS bug_reply_assigned
		FROM daportal_module, daportal_user_enabled, daportal_content
		LEFT JOIN daportal_project
		ON daportal_content.content_id=daportal_project.project_id
		LEFT JOIN daportal_bug
		ON daportal_content.content_id=daportal_bug.content_id
		LEFT JOIN daportal_bug_reply
		ON daportal_content.content_id=daportal_bug_reply.content_id
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.enabled='1'
		AND (daportal_content.public='1' OR daportal_content.user_id=:user_id)
		AND daportal_content.content_id=:content_id";


	//methods
	//essential
	//ProjectModule::ProjectModule
	protected function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Projects') : $title;
		$this->content_classes = array('project' => 'ProjectContent',
			'bug' => 'BugProjectContent',
			'bugreply' => 'BugReplyProjectContent');
		$this->content_list_count = 20;
		parent::__construct($id, $name, $title);
	}


	//accessors
	//ProjectModule::canUpload
	protected function canUpload($engine, $request = FALSE,
			$project = FALSE)
	{
		if($project == FALSE)
			$project = new ProjectContent($engine, $this);
		return $project->canUpload($engine, $request);
	}


	//ProjectModule::getBugByID
	protected function getBugByID($engine, $id)
	{
		$db = $engine->getDatabase();
		$query = $this->project_query_bug_by_id;
		$args = array('bug_id' => $id);

		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		$res = $res[0];
		$res['date'] = $db->formatDate($engine, $res['timestamp']);
		return $res;
	}


	//ProjectModule::getFilter
	protected function getFilter($engine, $request)
	{
		$r = new Request($this->name, 'bug_list');
		$form = new PageElement('form', array('request' => $r,
				'idempotent' => TRUE));
		$hbox = $form->append('hbox');
		$vbox1 = $hbox->append('vbox');
		$vbox2 = $hbox->append('vbox');
		//FIXME fetch the project name in additional cases
		$vbox1->append('entry', array('name' => 'project',
			'value' => $request->getParameter('project'),
			'text' => _('Project: ')));
		$vbox2->append('entry', array('name' => 'username',
			'value' => $request->getParameter('username'),
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
	protected function getMembers($engine, $project)
	{
		$db = $engine->getDatabase();
		$query = $this->project_query_members;
		$args = array('project_id' => $project->getID());

		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return FALSE;
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
			$res[$i]['admin'] = ($db->isTrue($res[$i]['admin']))
				? TRUE : FALSE;
		return $res;
	}


	//ProjectModule::_getProjectByName
	protected function _getProjectByName($engine, $name)
	{
		$db = $engine->getDatabase();
		$query = $this->project_query_project_by_name;
		$args = array('module_id' => $this->id, 'title' => $name);

		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return $res[0];
	}


	//ProjectModule::isManager
	protected function isManager($engine, $project)
	{
		$cred = $engine->getCredentials();

		if($cred->isAdmin()
				|| $project->getUserID() == $cred->getUserID())
			return TRUE;
		return FALSE;
	}


	//ProjectModule::isMember
	protected function isMember($engine, $project)
	{
		$cred = $engine->getCredentials();

		if(($members = $this->getMembers($engine, $project)) === FALSE)
			return FALSE;
		$uid = $cred->getUserID();
		if($project->getUserID() == $uid)
			return TRUE;
		foreach($members as $m)
			if($m['user_id'] == $uid)
				return TRUE;
		return FALSE;
	}


	//ProjectModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
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
	protected function callAdmin($engine, $request = FALSE)
	{
		if($request === FALSE)
			return parent::callAdmin($engine, $request);
		switch($request->getParameter('type'))
		{
			case 'bug':
				return $this->_adminBugs($engine, $request);
			case 'project':
			default:
				return parent::callAdmin($engine, $request);
		}
	}

	private function _adminBugs($engine, $request)
	{
		//FIXME also set the columns
		$this->text_content_admin = _('Bugs administration');
		$this->query_list_admin
			= $this->project_query_list_admin_bugs;
		$this->query_list_admin_count
			= $this->project_query_list_admin_bugs_count;
		return parent::callAdmin($engine, $request);
	}


	//ProjectModule::callBugList
	protected function callBugList($engine, $request)
	{
		$db = $engine->getDatabase();
		$title = _('Bug reports');
		$error = FALSE;
		$query = $this->project_query_list_bugs;
		$project = FALSE;

		//XXX unlike ProjectModule::list() here getID() is the project
		//determine the current project
		if(($id = $request->getID()) !== FALSE
				&& ($project = $this->_get($engine, $id,
					$request->getTitle())) === FALSE)
			$error = _('Unknown project');
		else if(($name = $request->getParameter('project')) !== FALSE
				&& strlen($name))
		{
			if(($project = $this->_getProjectByName($engine,
					$name)) !== FALSE)
				$id = $project['id'];
			else
				$error = _('Unknown project');
		}
		$args = array('module_id' => $this->id);
		if($project !== FALSE)
		{
			$title = _('Bug reports for ').$project->getTitle();
			$query .= ' AND daportal_project.project_id=:project_id';
			$args['project_id'] = $id;
		}
		$filter = $this->getFilter($engine, $request);
		//filter by user_id
		if(($uid = $request->getParameter('user_id')) !== FALSE)
		{
			$title .= _(' by ').$uid; //XXX
			$query .= ' AND bug.user_id=:user_id';
			$args['user_id'] = $uid;
		}
		//sorting out
		switch(($order = $request->getParameter('sort')))
		{
			case 'date':	$order = 'bug.timestamp DESC';	break;
			case 'title':	$order = 'bug.title ASC';	break;
			default:	$order = 'bug_id DESC';		break;
		}
		$query .= ' ORDER BY '.$order;
		//obtain the corresponding bug reports
		if($error !== FALSE)
			$res = array();
		else if(($res = $db->query($engine, $query, $args)) === FALSE)
		{
			$res = array();
			$error = _('Unable to list bugs');
		}
		//build the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if($error !== FALSE)
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		if($filter !== FALSE)
			$page->append($filter);
		$treeview = $page->append('treeview');
		$treeview->setProperty('columns', array('title' => _('Title'),
			'bug_id' => _('ID'), 'project' => _('Project'),
			'date' => _('Date'), 'state' => _('State'),
			'type' => _('Type'), 'priority' => _('Priority')));
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$row = $treeview->append('row');
			$r = new Request($this->name, FALSE, $res[$i]['id'],
				$res[$i]['title']);
			$link = new PageElement('link', array('request' => $r,
					'text' => $res[$i]['title'],
					'title' => $res[$i]['title']));
			$row->setProperty('title', $link);
			$link = new PageElement('link', array('request' => $r,
					'text' => '#'.$res[$i]['bug_id'],
					'title' => $res[$i]['title']));
			$row->setProperty('bug_id', $link);
			$row->setProperty('id', 'bug_id:'.$res[$i]['id']);
			$r = new Request($this->name, FALSE,
					$res[$i]['project_id'],
					$res[$i]['project']);
			$link = new PageElement('link', array('request' => $r,
					'text' => $res[$i]['project'],
					'title' => $res[$i]['project']));
			$row->setProperty('project', $link);
			$date = $db->formatDate($engine, $res[$i]['timestamp']);
			$row->setProperty('date', $date);
			$row->setProperty('state', $res[$i]['state']);
			$row->setProperty('type', $res[$i]['type']);
			$row->setProperty('priority', $res[$i]['priority']);
		}
		return $page;
	}


	//ProjectModule::callBugReply
	protected function callBugReply($engine, $request)
	{
		$cred = $engine->getCredentials();
		$user = new User($engine, $cred->getUserID());

		if(($bug = BugProjectContent::load($engine, $this,
				$request->getID(), $request->getTitle()))
				=== FALSE)
			return $this->callDefault($engine);
		$project = $this->_get($engine, $bug->get('project_id'));
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
		if($request->getParameter('preview') !== FALSE)
		{
			$title = $request->getParameter('title');
			$content = $request->getParameter('content');
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
		return $page;
	}


	//ProjectModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		return $this->callList($engine, $request);
	}


	//ProjectModule::callSubmitRelease
	protected function callSubmitRelease($engine, $request)
	{
		$project = $this->_get($engine, $request->getID(),
				$request->getTitle());

		$error = _('Invalid project');
		if($project === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$error = _('Permission denied');
		if(!$this->canUpload($engine, $request, $project))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$title = _('New release for project ').$project->getTitle();
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
		return $page;
	}

	protected function _submitProcessRelease($engine, $request, $project,
			&$content)
	{
		$db = $engine->getDatabase();
		$query = $this->project_query_project_release_insert;

		//verify the request
		if($request === FALSE
				|| $request->getParameter('submit') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
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
		$did = $res[0]['id'];
		$args = array('project_id' => $project['id'],
			'download_id' => $did);
		if($db->query($engine, $query, $args) === FALSE)
			return _('Internal server error');
		$content = Content::get($engine, $this->id, $project['id'],
				$project['title']);
		return FALSE;
	}

	protected function _submitSuccessRelease($engine, $request, $page,
			$content)
	{
		$r = new Request($this->name, 'download', $content->getID(),
			$content->getTitle());
		$this->helperRedirect($engine, $r, $page,
				$this->text_content_submit_progress); //XXX
		return $page;
	}


	//forms
	//ProjectModule::formBugReply
	protected function formBugReply($engine, $request, $bug, $project)
	{
		$r = new Request($this->name, 'bugReply', $request->getID(),
			$request->getTitle());
		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		$title = $request->getParameter('title');
		$vbox->append('entry', array('text' => _('Title: '),
				'name' => 'title', 'value' => $title));
		$vbox->append('textview', array('text' => _('Content: '),
				'name' => 'content',
				'value' => $request->getParameter('content')));
		//FIXME really implement
		$r = $bug->getRequest();
		$box = $vbox->append('buttonbox');
		$box->append('button', array('request' => $r,
				'stock' => 'cancel', 'text' => _('Cancel')));
		$box->append('button', array('type' => 'submit',
				'stock' => 'preview', 'name' => 'action',
				'value' => 'preview', 'text' => _('Preview')));
		//FIXME add missing buttons
		return $form;
	}


	//ProjectModule::formSubmitRelease
	protected function formSubmitRelease($engine, $request, $project)
	{
		$r = new Request($this->name, 'submit', $project->getID(),
			$project->getTitle(), array('type' => 'release'));
		$form = new PageElement('form', array('request' => $r));
		$form->append('filechooser', array('text' => _('File: '),
				'name' => 'files[]'));
		$value = $request->getParameter('directory');
		$form->append('entry', array('text' => _('Directory: '),
				'name' => 'directory', 'value' => $value));
		$r = new Request($this->name, 'download', $project->getID(),
			$project->getTitle());
		$form->append('button', array('stock' => 'cancel',
				'request' => $r, 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'text' => _('Submit'),
				'name' => 'submit', 'value' => 'submit'));
		return $form;
	}


	//helpers
	//ProjectModule::helperDisplayBug
	protected function helperDisplayBug($engine, $request, $page, $content)
	{
		$project = $this->_get($engine, $content['project_id']);
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
	protected function helperDisplayBugMetadata($engine, $request, $page,
			$bug, $project)
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
	protected function helperDisplayBugReply($engine, $request, $page,
			$content)
	{
		$bug = $this->getBugByID($engine, $content['bug_id']);
		$project = ($bug !== FALSE)
			? $this->_get($engine, $bug['project_id']) : FALSE;

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


	//ProjectModule::helperDisplayMembers
	protected function helperDisplayMembers($engine, $request, $page,
			$content)
	{
		$user = new User($engine, $content['user_id']);

		if(($members = $this->getMembers($engine, $content)) === FALSE)
			return;
		$vbox = $page->append('vbox');
		$vbox->append('title', array('text' => _('Members')));
		$columns = array('title' => _('Name'),
				'admin' => _('Administrator'));
		$view = $vbox->append('treeview', array('columns' => $columns));
		$no = new PageElement('image', array('stock' => 'no',
			'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
			'size' => 16, 'title' => _('Enabled')));
		//project owner
		$r = new Request('user', FALSE, $user->getUserID(),
				$user->getUsername());
		$link = new PageElement('link', array('request' => $r,
				'stock' => 'user',
				'text' => $user->getUsername()));
		$view->append('row', array('title' => $link, 'admin' => $yes));
		//project members
		foreach($members as $m)
		{
			$row = $view->append('row');
			$r = new Request('user', FALSE, $m['user_id'],
				$m['username']);
			$link = new PageElement('link', array('request' => $r,
				'stock' => 'user', 'text' => $m['username']));
			$row->setProperty('title', $link);
			$row->setProperty('admin', $m['admin'] ? $yes : $no);
		}
	}


	//ProjectModule::helperListButtons
	protected function helperListButtons($engine, $page, $request = FALSE)
	{
	}


	//ProjectModule::helperListView
	protected function helperListView($engine, $request = FALSE)
	{
		if($this->content_class == 'BugProjectContent')
			return parent::helperListView($engine, $request);
		$view = parent::helperListView($engine, $request);
		if(($columns = $view->getProperty('columns')) !== FALSE)
		{
			unset($columns['date']);
			$columns['username'] = _('Manager');
			$columns['synopsis'] = _('Description');
			$view->setProperty('columns', $columns);
		}
		return $view;
	}


	//ProjectModule::helperPreviewMetadata
	protected function helperPreviewMetadata($engine, $preview, $request,
			$content = FALSE)
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
