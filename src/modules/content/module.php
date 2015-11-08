<?php //$Id$
//Copyright (c) 2012-2015 Pierre Pronchery <khorben@defora.org>
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
//TODO:
//- mention when a content is not public
//- list contents pending moderation (if relevant)



//ContentModule
abstract class ContentModule extends Module
{
	//public
	//methods
	//calls
	//ContentModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->$action($engine,
							$request);
				default:
					return FALSE;
			}
		switch($action)
		{
			case 'admin':
			case 'default':
			case 'display':
			case 'group':
			case 'headline':
			case 'list':
			case 'preview':
			case 'publish':
			case 'submit':
			case 'update':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//accessors
	//FIXME make these more generic: can($action)
	//ContentModule::canAdmin
	public function canAdmin($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$credentials->isAdmin())
			return FALSE;
		if($content === FALSE)
			return TRUE;
		return $content->canAdmin($engine, FALSE, $error);
	}


	//ContentModule::canDelete
	public function canDelete($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $content, $error);
	}


	//ContentModule::canDisable
	public function canDisable($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $content, $error);
	}


	//ContentModule::canEnable
	public function canEnable($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $content, $error);
	}


	//ContentModule::canPreview
	public function canPreview($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canPreview($engine, FALSE, $error);
	}


	//ContentModule::canPublish
	public function canPublish($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if($credentials->getUserID() == 0)
			if(!$this->configGet('anonymous'))
			{
				$error = _('You need to be logged in to publish content');
				return FALSE;
			}
		if(!$credentials->isAdmin())
			if($this->configGet('moderate'))
			{
				$error = _('Only moderators can publish content');
				return FALSE;
			}
		if($content === FALSE)
			return TRUE;
		return $content->canPublish($engine, FALSE, $error);
	}


	//ContentModule::canSubmit
	public function canSubmit($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();
		$anonymous = _('Anonymous submissions are not allowed');

		if($credentials->getUserID() == 0)
			if(!$this->configGet('anonymous'))
			{
				$error = $anonymous;
				return FALSE;
			}
		if($content === FALSE)
			return TRUE;
		return $content->canSubmit($engine, FALSE, $error);
	}


	//ContentModule::canUnpublish
	public function canUnpublish($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Permission denied');
		if($credentials->getUserID() == 0)
			if(!$this->configGet('anonymous'))
				return FALSE;
		$error = _('Only moderators can unpublish content');
		if(!$credentials->isAdmin())
			if($this->configGet('moderate'))
				return FALSE;
		if($content === FALSE)
			return TRUE;
		return $content->canUnpublish($engine, FALSE, $error);
	}


	//ContentModule::canUpdate
	public function canUpdate($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Anonymous updates are not allowed');
		if($credentials->getUserID() == 0)
			if(!$this->configGet('anonymous'))
				return FALSE;
		if($content === FALSE)
			return TRUE;
		return $content->canUpdate($engine, FALSE, $error);
	}


	//ContentModule::getContent
	public function getContent($engine, $id, $title = FALSE,
			$request = FALSE)
	{
		$class = $this->content_class;

		return $class::load($engine, $this, $id, $title);
	}


	//protected
	//properties
	protected $content_class = 'Content';
	protected $content_headline_count = 6;
	protected $content_list_count = 10;
	protected $content_list_admin_count = 20;
	protected $content_list_admin_order = 'timestamp DESC';
	protected $content_preview_length = 150;
	protected $stock_back = 'back';
	protected $stock_content_new = 'new';
	protected $stock_content_submit = 'submit';
	protected $text_content_admin = 'Content administration';
	protected $text_content_headline_title = 'Content headlines';
	protected $text_content_list_title = 'Content list';
	protected $text_content_list_title_group = 'Content list for group';
	protected $text_content_list_title_by = 'Content by';
	protected $text_content_list_title_by_group = 'Content by group';
	protected $text_content_publish = 'Publish';
	protected $text_content_publish_progress
		= 'Publication in progress, please wait...';
	protected $text_content_redirect_progress
		= 'Redirection in progress, please wait...';
	protected $text_content_submit = 'Submit';
	protected $text_content_submit_content = 'Submit content';
	protected $text_content_submit_progress
		= 'Submission in progress, please wait...';
	protected $text_content_title = 'Content';
	protected $text_content_update_progress
			= 'Update in progress, please wait...';

	//queries
	//IN:	module_id
	//	content_id
	static protected $query_admin_delete = 'DELETE FROM daportal_content
		WHERE module_id=:module_id
		AND content_id=:content_id';
	//IN:	module_id
	//	content_id
	static protected $query_admin_disable = "UPDATE daportal_content
		SET enabled='0'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	static protected $query_admin_enable = "UPDATE daportal_content
		SET enabled='1'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	static protected $query_admin_publish = "UPDATE daportal_content
		SET public='1'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	static protected $query_admin_unpublish = "UPDATE daportal_content
		SET public='0'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	//	user_id
	static protected $query_delete = 'DELETE FROM daportal_content
		WHERE module_id=:module_id
		AND content_id=:content_id
		AND user_id=:user_id';
	//IN:	module_id
	//	content_id
	//	user_id
	static protected $query_disable = "UPDATE daportal_content
		SET enabled='0'
		WHERE module_id=:module_id
		AND content_id=:content_id AND user_id=:user_id";
	//IN:	module_id
	//	content_id
	//	user_id
	static protected $query_enable = "UPDATE daportal_content
		SET enabled='1'
		WHERE module_id=:module_id
		AND content_id=:content_id AND user_id=:user_id";
	//IN:	module_id
	static protected $query_list_admin = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname,
		title, daportal_content.enabled AS enabled,
		daportal_content.public AS public
		FROM daportal_content, daportal_user_enabled, daportal_group
		WHERE daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id';
	//IN:	module_id
	static protected $query_list_admin_count = 'SELECT COUNT(*) AS count
		FROM daportal_content, daportal_user_enabled, daportal_group
		WHERE daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_publish = "UPDATE daportal_content
		SET public='1'
		WHERE module_id=:module_id
		AND user_id=:user_id
		AND content_id=:content_id";
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_unpublish = "UPDATE daportal_content
		SET public='0'
		WHERE module_id=:module_id
		AND user_id=:user_id
		AND content_id=:content_id";


	//methods
	//essential
	//ContentModule::ContentModule
	protected function __construct($id, $name, $title = FALSE)
	{
		parent::__construct($id, $name, $title);
		//variables
		//translations
		$this->text_content_admin = _('Content administration');
		$this->text_content_headline_title = _('Content headlines');
		$this->text_content_list_title = _('Content list');
		$this->text_content_list_title_by = _('Content by');
		$this->text_content_publish = _('Publish');
		$this->text_content_publish_progress
			= _('Publication in progress, please wait...');
		$this->text_content_redirect_progress
			= _('Redirection in progress, please wait...');
		$this->text_content_submit = _('Submit');
		$this->text_content_submit_content = _('Submit content');
		$this->text_content_submit_progress
			= _('Submission in progress, please wait...');
		$this->text_content_title = _('Content');
		$this->text_content_update_progress
			= _('Update in progress, please wait...');
	}


	//forms
	//ContentModule::formSubmit
	protected function formSubmit($engine, $request)
	{
		$r = $this->getRequest('submit');

		$form = new PageElement('form', array('request' => $r));
		//content
		$this->helperSubmitContent($engine, $request, $form);
		//buttons
		$this->helperSubmitButtons($engine, $request, $form);
		return $form;
	}


	//ContentModule::formUpdate
	protected function formUpdate($engine, $request, $content)
	{
		//XXX the title may be wrong
		$r = $content->getRequest('update');

		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		//content
		$this->helperUpdateContent($engine, $request, $content, $vbox);
		//buttons
		$this->helperUpdateButtons($engine, $request, $content, $vbox);
		return $form;
	}


	//actions
	//ContentModule::actions
	protected function actions($engine, $request)
	{
		//FIXME review
		$cred = $engine->getCredentials();

		if(($user = $request->get('user')) !== FALSE)
			return $this->helperActionsUser($engine, $request,
					$user);
		if(($group = $request->get('group')) !== FALSE)
			return $this->helperActionsGroup($engine, $request,
					$group);
		$ret = array();
		if($cred->isAdmin())
		{
			$r = $this->helperActionsAdmin($engine, $request);
			if(is_array($r))
				$ret = array_merge($ret, $r);
		}
		if($request->get('admin') !== FALSE)
			return $ret;
		if($this->canSubmit($engine, $request))
		{
			$r = $this->helperActionsSubmit($engine, $request);
			$ret = array_merge($ret, $r);
		}
		if(($r = $this->helperActions($engine, $request)) !== FALSE)
			$ret = array_merge($ret, $r);
		return $ret;
	}


	//calls
	//ContentModule::callAdmin
	protected function callAdmin($engine, $request = FALSE)
	{
		$db = $engine->getDatabase();
		$query = static::$query_list_admin;
		$args = array('module_id' => $this->id);
		$p = ($request !== FALSE) ? $request->get('page') : 0;
		$pcnt = FALSE;
		$error = FALSE;
		$dialog = FALSE;

		//check credentials
		if(!$this->canAdmin($engine, $request, FALSE, $error))
		{
			$r = new Request('user', 'login');
			$dialog = new PageElement('dialog', array(
						'type' => 'error',
						'text' => $error));
			$dialog->append('button', array('stock' => 'login',
						'text' => _('Login'),
						'request' => $r));
			return new PageResponse($dialog, Response::$CODE_EPERM);
		}
		//perform actions if necessary
		if($request !== FALSE)
			$dialog = $this->helperAdminActions($engine, $request);
		//administrative page
		$title = $this->text_content_admin;
		$page = new Page(array('title' => $title));
		$element = $page->append('title', array('stock' => 'admin',
				'text' => $title));
		if($dialog !== FALSE)
			$page->append($dialog);
		if(is_string(($order = $this->content_list_admin_order)))
			$query .= ' ORDER BY '.$order;
		//paging
		if(($limit = $this->content_list_admin_count) > 0)
		{
			//obtain the total number of records available
			$q = static::$query_list_admin_count;
			if(($res = $db->query($engine, $q, $args)) !== FALSE
					&& count($res) == 1)
			{
				$res = $res->current();
				$pcnt = $res['count'];
			}
			if($pcnt !== FALSE)
			{
				$offset = FALSE;
				if(is_numeric($p) && $p > 1)
				{
					$offset = $limit * ($p - 1);
					if($offset >= $pcnt)
						$offset = 0;
				}
				$query .= $db->offset($limit, $offset);
			}
		}
		$error = _('Unable to list contents');
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return new ErrorResponse($error);
		$r = $this->getRequest('admin');
		if($request !== FALSE
				&& ($type = $request->get('type')) !== FALSE)
			$r->set('type', $type);
		$columns = array('icon' => '', 'title' => _('Title'),
			'enabled' => _('Enabled'), 'public' => _('Public'),
			'username' => _('Username'), 'date' => _('Date'));
		if(!$this->canPublish($engine))
			unset($columns['public']);
		$treeview = $page->append('treeview', array('request' => $r,
				'columns' => $columns, 'alternate' => TRUE));
		//toolbar
		$this->helperAdminToolbar($engine, $treeview, $request);
		//rows
		foreach($res as $r)
		{
			$row = $treeview->append('row');
			$this->helperAdminRow($engine, $row, $r);
		}
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $pcnt);
		//buttons
		$vbox = $page->append('vbox');
		$this->helperAdminButtons($engine, $vbox, $request);
		return new PageResponse($page);
	}


	//ContentModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$class = $this->content_class;
		$p = ($request !== FALSE) ? $request->get('page') : 0;

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$page = new Page(array('title' => $this->text_content_title));
		$page->append('title', array('stock' => $this->name,
				'text' => $this->text_content_title));
		$error = _('Could not list the content available');
		if(($res = $class::listAll($engine, $this, FALSE)) === FALSE)
			return new ErrorResponse($error);
		//paging
		$count = $res->count();
		$offset = 0;
		if(($limit = $this->content_list_count) > 0)
		{
			if(is_numeric($p) && $p > 1)
				$offset = $limit * ($p - 1);
		}
		else
			$limit = $count;
		for($res->seek($offset), $i = 0; $res->valid() && $i < $limit;
				$res->next(), $i++)
		{
			$content = $res->current();
			$page->append($content->preview($engine,
					$request));
		}
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count);
		return new PageResponse($page);
	}


	//ContentModule::callDisplay
	protected function callDisplay($engine, $request)
	{
		$error = _('Could not display the content');

		//obtain the content
		if(($content = $this->getContent($engine, $request->getID(),
				$request->getTitle(), $request)) === FALSE)
			return new ErrorResponse($error,
					Response::$CODE_ENOENT);
		//display the content
		$response = $content->display($engine, $request);
		if($response instanceof PageElement)
		{
			$page = new Page(array('title' => $content->getTitle()));
			$page->append($response);
			return new PageResponse($page);
		}
		return $response;
	}


	//ContentModule::callGroup
	protected function callGroup($engine, $request)
	{
		$class = $this->content_class;
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$group = ($request !== FALSE)
			? Group::lookup($engine, $request->getTitle(),
					$request->getID())
				: Group::lookup($engine, $cred->getGroupname(),
						$cred->getGroupID());
		$p = ($request !== FALSE) ? $request->get('page') : 0;

		$title = $this->text_content_list_title_group;
		$title = $this->text_content_list_title_by_group.' '
			.$group->getGroupname();
		//title
		$page = new Page(array('title' => $title));
		$this->helperListTitle($engine, $page, $request);
		$error = _('Unable to lookup the group');
		if($group === FALSE)
			return new ErrorResponse($error,
					Response::$CODE_ENOENT);
		$error = _('Unable to list the content');
		if(($res = $class::listAll($engine, $this, FALSE, FALSE, FALSE,
				$group)) === FALSE)
			return new ErrorResponse($error);
		//paging
		$count = $res->count();
		$offset = 0;
		if(($limit = $this->content_list_count) > 0)
		{
			if(is_numeric($p) && $p > 1)
				$offset = $limit * ($p - 1);
		}
		else
			$limit = $count;
		//FIXME some helpers should move to the Content class
		//view
		$treeview = $this->helperListView($engine, $request);
		$page->append($treeview);
		//toolbar
		$this->helperListToolbar($engine, $treeview, $request);
		//rows
		for($res->seek($offset), $i = 0; $res->valid() && $i < $limit;
				$res->next(), $i++)
		{
			$content = $res->current();
			$treeview->append($content->displayRow($engine,
					$request));
		}
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count);
		//buttons
		$this->helperListButtons($engine, $page, $request);
		return new PageResponse($page);
	}


	//ContentModule::callHeadline
	protected function callHeadline($engine, $request = FALSE)
	{
		$class = $this->content_class;
		$title = $this->text_content_headline_title;

		//view
		$columns = array('title' => _('Title'), 'date' => _('Date'),
				'username' => _('Author'));
		$view = new PageElement('treeview', array('view' => 'details',
				'title' => $title, 'columns' => $columns));
		//obtain contents
		$count = (is_integer($this->content_headline_count))
			? $this->content_headline_count : 6;
		$error = _('Unable to list contents');
		if(($res = $class::listAll($engine, $this, 'timestamp',
				$count, 0)) === FALSE)
			return new ErrorResponse($error);
		//rows
		foreach($res as $r)
		{
			$row = $view->append('row');
			$request = $r->getRequest();
			$link = new PageElement('link', array(
					'request' => $request,
					'text' => $r->getTitle()));
			$row->set('title', $link);
			$row->set('timestamp', $r->get('timestamp'));
			$row->set('date', $r->getDate($engine));
			$request = new Request('user', FALSE, $r->getUserID(),
				$r->getUsername());
			$link = new PageElement('link', array(
				'request' => $request, 'stock' => 'user',
				'text' => $r->getUsername()));
			$row->set('username', $link);
			$content = $r->getContent($engine);
			if(($len = $this->content_preview_length) > 0
					&& strlen($content) > $len)
				$content = substr($content, 0, $len).'...';
			$row->set('content', $content);
		}
		return new PageResponse($view);
	}


	//ContentModule::callList
	protected function callList($engine, $request = FALSE)
	{
		$class = $this->content_class;
		$db = $engine->getDatabase();
		$user = ($request !== FALSE)
			? User::lookup($engine, $request->getTitle(),
					$request->getID()) : FALSE;
		$p = ($request !== FALSE) ? $request->get('page') : 0;
		$error = _('Unable to list contents');
		$dialog = FALSE;

		//perform actions if necessary
		$actions = array();
		if($this->canPublish($engine, $request))
			$actions[] = 'post';
		if($this->canUnpublish($engine, $request))
			$actions[] = 'unpost';
		if($request !== FALSE)
			foreach($actions as $a)
				if($request->get($a) !== FALSE)
				{
					$a = 'helper'.$a;
					$dialog = $this->$a($engine, $request);
					break;
				}
		if($user !== FALSE && ($uid = $user->getUserID()) == 0)
			$user = FALSE;
		$title = $this->text_content_list_title;
		if($user !== FALSE)
			$title = $this->text_content_list_title_by.' '
				.$user->getUsername();
		//title
		$page = new Page(array('title' => $title));
		$this->helperListTitle($engine, $page, $request);
		if($dialog != FALSE)
			$page->append($dialog);
		if(($res = $class::listAll($engine, $this, FALSE, FALSE, FALSE,
				$user)) === FALSE)
			return new ErrorResponse($error);
		//paging
		$count = count($res);
		$offset = 0;
		if(($limit = $this->content_list_count) > 0)
		{
			if(is_numeric($p) && $p > 1)
				$offset = $limit * ($p - 1);
		}
		else
			$limit = $count;
		//FIXME some helpers should move to the Content class
		//view
		$treeview = $this->helperListView($engine, $request);
		$page->append($treeview);
		//toolbar
		$this->helperListToolbar($engine, $treeview, $request);
		//rows
		for($i = $offset; $i < $offset + $limit && isset($res[$i]);
			$i++)
		{
			$content = $res[$i];
			$treeview->append($content->displayRow($engine,
					$request));
		}
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count);
		//buttons
		$this->helperListButtons($engine, $page, $request);
		return new PageResponse($page);
	}


	//ContentModule::callPreview
	protected function callPreview($engine, $request)
	{
		$error = _('Could not preview content');

		//obtain the content
		if(($content = $this->getContent($engine, $request->getID(),
				$request->getTitle(), $request)) === FALSE)
			return new ErrorResponse($error,
					Response::$CODE_ENOENT);
		//preview the content
		$page = new Page(array('title' => $content->getTitle()));
		$page->append($content->preview($engine, $request));
		return new PageResponse($page);
	}


	//ContentModule::callPublish
	protected function callPublish($engine, $request)
	{
		$error = _('Could not preview content');

		//obtain the content
		if(($content = $this->getContent($engine, $request->getID(),
				$request->getTitle(), $request)) === FALSE)
			return new ErrorResponse($error,
					Response::$CODE_ENOENT);
		//check permissions
		$error = _('Permission denied');
		if($this->canPublish($engine, $request, $content, $error)
				=== FALSE)
			return new ErrorResponse($error, Response::$CODE_EPERM);
		//create the page
		$title = $this->text_content_publish.' '.$content->getTitle();
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//toolbar
		$this->helperToolbar($engine, $request, $content, $page);
		//process the request
		if(($error = $this->_publishProcess($engine, $request,
				$content)) === FALSE)
			return $this->_publishSuccess($engine, $request,
					$content, $page);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//preview
		$vbox = $page->append('vbox');
		$text = _('Preview: ').$content->getTitle();
		//XXX do not preview the buttons
		$vbox->append($content->preview($engine));
		//form
		$r = $content->getRequest('publish');
		$form = $page->append('form', array('request' => $r));
		//buttons
		$r = $content->getRequest();
		$form->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'name' => 'action', 'value' => 'publish',
				'text' => $this->text_content_publish));
		return new PageResponse($page);
	}

	protected function _publishProcess($engine, $request, $content)
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$query = static::$query_publish;
		$args = array('module_id' => $this->id,
			'content_id' => $content->getID(),
			'user_id' => $cred->getUserID());

		//verify the request
		if($request === FALSE || $request->isIdempotent())
			return TRUE;
		//publish the content
		if($db->query($engine, $query, $args) === FALSE)
			return _('Internal server error');
		return FALSE;
	}

	protected function _publishSuccess($engine, $request, $content, $page)
	{
		$r = $content->getRequest();
		return $this->helperRedirect($engine, $r, $page,
				$this->text_content_publish_progress);
	}


	//ContentModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		$title = $this->text_content_submit_content;
		$code = 0;
		$error = _('Could not submit content');

		//check permissions
		if($this->canSubmit($engine, $request, FALSE, $error) === FALSE)
			return new ErrorResponse($error,
				Response::$CODE_EUNKNOWN);
		//create the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//obtain the new content
		$content = $this->_submitContent($engine, $request);
		//toolbar
		$this->helperToolbar($engine, $request, $content, $page);
		//process the request
		if(($error = $this->_submitProcess($engine, $request, $content))
				=== FALSE)
			return $this->_submitSuccess($engine, $request,
					$content, $page);
		else if(is_string($error))
		{
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			$code = Response::$CODE_EUNKNOWN;
		}
		//preview
		$this->helperSubmitPreview($engine, $request, $content, $page);
		//form
		$form = $this->formSubmit($engine, $request);
		$page->append($form);
		return new PageResponse($page, $code);
	}

	protected function _submitContent($engine, $request)
	{
		$class = $this->content_class;
		$cred = $engine->getCredentials();
		$content = array('user_id' => $cred->getUserID(),
			'username' => $cred->getUsername(),
			'title' => $request->get('title'),
			'content' => $request->get('content'));

		if(($public = $request->get('public')) !== FALSE)
			$content['public'] = $public ? TRUE : FALSE;
		return new $class($engine, $this, $content);
	}

	protected function _submitProcess($engine, $request, $content)
	{
		//verify the request
		if($request === FALSE || $request->isIdempotent())
			return TRUE;
		//store the content uploaded
		$error = _('Internal server error');
		if($content->save($engine, $request, $error) === FALSE)
			return $error;
		return FALSE;
	}

	protected function _submitSuccess($engine, $request, $content, $page)
	{
		$r = $content->getRequest();
		return $this->helperRedirect($engine, $r, $page,
				$this->text_content_submit_progress);
	}


	//ContentModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		//obtain the content
		$error = _('Unable to fetch content');
		if(($content = $this->getContent($engine, $request->getID(),
				$request->getTitle(), $request)) === FALSE)
			return new ErrorResponse($error,
					Response::$CODE_ENOENT);
		//check permissions
		$error = _('Could not update content');
		if($this->canUpdate($engine, $request, FALSE, $error)
				=== FALSE)
			return new ErrorResponse($error, Response::$CODE_EPERM);
		//create the page
		$title = _('Update ').$content->getTitle();
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//toolbar
		$this->helperToolbar($engine, $request, $content, $page);
		//process the request
		if(($error = $this->_updateProcess($engine, $request, $content))
				=== FALSE)
			return $this->_updateSuccess($engine, $request,
					$content, $page);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//preview
		$this->helperUpdatePreview($engine, $request, $content, $page);
		$form = $this->formUpdate($engine, $request, $content);
		$page->append($form);
		return new PageResponse($page);
	}

	protected function _updateProcess($engine, $request, $content)
	{
		//verify the request
		if($request === FALSE || $request->isIdempotent())
			return TRUE;
		//update the content
		$error = _('Internal server error');
		if($content->save($engine, $request, $error) === FALSE)
			return $error;
		return FALSE;
	}

	protected function _updateSuccess($engine, $request, $content, $page)
	{
		$r = $content->getRequest();
		return $this->helperRedirect($engine, $r, $page,
				$this->text_content_update_progress);
	}


	//helpers
	//ContentModule::helperAction
	protected function helperAction($engine, $stock, $request, $text)
	{
		$icon = new PageElement('image', array('stock' => $stock));
		$link = new PageElement('link', array('request' => $request,
				'text' => $text));
		return new PageElement('row', array('icon' => $icon,
				'label' => $link));
	}


	//ContentModule::helperActions
	protected function helperActions($engine, $request)
	{
		return FALSE;
	}


	//ContentModule::helperActionsAdmin
	protected function helperActionsAdmin($engine, $request)
	{
		if($request->get('admin') === 0)
			return FALSE;
		$ret = array();
		$r = $this->getRequest('admin');
		$ret[] = $this->helperAction($engine, 'admin', $r,
				$this->text_content_admin);
		return $ret;
	}


	//ContentModule::helperActionsGroup
	protected function helperActionsGroup($engine, $request, $group)
	{
		$ret = array();

		//group's content
		$r = new Request($this->name, 'group', $group->getGroupID(),
			$group->getGroupname());
		$ret[] = $this->helperAction($engine, $this->name, $r,
				$this->text_content_list_title_by_group
				.' '.$group->getGroupname());
		return $ret;
	}


	//ContentModule::helperActionsList
	protected function helperActionsList($engine, $request, $user)
	{
		$ret = array();

		//user's content
		$r = new Request($this->name, 'list', $user->getUserID(),
			$user->getUsername());
		$ret[] = $this->helperAction($engine, $this->name, $r,
				$this->text_content_list_title_by
				.' '.$user->getUsername());
		return $ret;
	}


	//ContentModule::helperActionsSubmit
	protected function helperActionsSubmit($engine, $request)
	{
		$ret = array();

		$r = $this->getRequest('submit');
		$ret[] = $this->helperAction($engine, $this->stock_content_new,
				$r, $this->text_content_submit_content);
		return $ret;
	}


	//ContentModule::helperActionsUser
	protected function helperActionsUser($engine, $request, $user)
	{
		$ret = array();
		$cred = $engine->getCredentials();

		if($user->getUserID() == $cred->getUserID()
				&& $this->canSubmit($engine, $request))
			$ret = $this->helperActionsSubmit($engine, $request);
		if(($r = $this->helperActionsList($engine, $request, $user))
				!== FALSE)
			$ret = array_merge($ret, $r);
		return $ret;
	}


	//ContentModule::helperAdminActions
	protected function helperAdminActions($engine, $request)
	{
		$actions = array('delete', 'disable', 'enable', 'post',
			'unpost');

		foreach($actions as $a)
			if($request->get($a) !== FALSE)
			{
				$a = 'helper'.$a;
				return $this->$a($engine, $request);
			}
		return FALSE;
	}


	//ContentModule::helperAdminButtons
	protected function helperAdminButtons($engine, $page, $request)
	{
		$r = $this->getRequest();
		$page->append('link', array('request' => $r,
				'stock' => $this->stock_back,
				'text' => _('Back to this module')));
		$r = new Request('admin');
		$page->append('link', array('request' => $r, 'stock' => 'admin',
				'text' => _('Back to the administration')));
	}


	//ContentModule::helperAdminRow
	protected function helperAdminRow($engine, $row, $res)
	{
		$db = $engine->getDatabase();
		$no = new PageElement('image', array('stock' => 'no',
				'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
				'size' => 16, 'title' => _('Enabled')));

		$row->set('id', 'ids['.$res['id'].']');
		$row->set('icon', '');
		$r = new Request($this->name, 'update', $res['id'],
			$res['title']);
		$link = new PageElement('link', array('request' => $r,
				'stock' => $this->name,
				'text' => $res['title']));
		$row->set('title', $link);
		$row->set('enabled', $db->isTrue($res['enabled']) ? $yes : $no);
		$row->set('public', $db->isTrue($res['public']) ? $yes : $no);
		$r = new Request('user', FALSE, $res['user_id'],
			$res['username']);
		$link = new PageElement('link', array('request' => $r,
				'stock' => 'user',
				'text' => $res['username']));
		$row->set('username', $link);
		$date = $db->formatDate($res['timestamp']);
		$row->set('date', $date);
	}


	//ContentModule::helperAdminToolbar
	protected function helperAdminToolbar($engine, $page, $request)
	{
		$actions = array();

		$toolbar = $page->append('toolbar');
		$r = $this->getRequest('admin', array(
				'page' => $request->get('page')));
		$toolbar->append('button', array('stock' => 'refresh',
					'request' => $r,
					'text' => _('Refresh')));
		//actions
		if($this->canDisable($engine, $request))
			$actions['disable'] = _('Disable');
		if($this->canEnable($engine, $request))
			$actions['enable'] = _('Enable');
		if($this->canUnpublish($engine, $request))
			$actions['unpost'] = _('Unpublish');
		if($this->canPublish($engine, $request))
			$actions['post'] = _('Publish');
		if($this->canDelete($engine, $request))
			$actions['delete'] = _('Delete');
		foreach($actions as $k => $v)
			$toolbar->append('button', array('stock' => $k,
					'text' => $v, 'type' => 'submit',
					'name' => 'action', 'value' => $k));
	}


	//ContentModule::helperApply
	protected function helperApply(Engine $engine, Request $request,
			$query, $args, $success, $failure, $key = FALSE)
	{
		if($key === FALSE)
			$key = 'content_id';
		return parent::helperApply($engine, $request, $query, $args,
				$success, $failure, $key);
	}


	//ContentModule::helperDelete
	protected function helperDelete($engine, $request)
	{
		$query = static::$query_delete;
		$args = array('module_id' => $this->getID());
		$cred = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$this->canDelete($engine, $request, FALSE, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if($cred->isAdmin())
			$query = static::$query_admin_delete;
		else
			$args['user_id'] = $cred->getUserID();
		return $this->helperApply($engine, $request, $query, $args,
				_('Content could be deleted successfully'),
				_('Some content could not be deleted'));
	}


	//ContentModule::helperDisable
	protected function helperDisable($engine, $request)
	{
		$query = static::$query_disable;
		$args = array('module_id' => $this->getID());
		$cred = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$this->canDisable($engine, $request, FALSE, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if($cred->isAdmin())
			$query = static::$query_admin_disable;
		else
			$args['user_id'] = $cred->getUserID();
		return $this->helperApply($engine, $request, $query, $args,
				_('Content could be disabled successfully'),
				_('Some content could not be disabled'));
	}


	//ContentModule::helperEnable
	protected function helperEnable($engine, $request)
	{
		$query = static::$query_enable;
		$args = array('module_id' => $this->getID());
		$cred = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$this->canEnable($engine, $request, FALSE, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if($cred->isAdmin())
			$query = static::$query_admin_enable;
		else
			$args['user_id'] = $cred->getUserID();
		return $this->helperApply($engine, $request, $query, $args,
				_('Content could be enabled successfully'),
				_('Some content could not be enabled'));
	}


	//ContentModule::helperListButtons
	protected function helperListButtons($engine, $page, $request = FALSE)
	{
		$user = ($request !== FALSE)
			? User::lookup($engine, $request->getTitle(),
				$request->getID()) : FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$r = ($uid !== FALSE)
			? new Request('user', 'display', $user->getUserID(),
				$user->getUsername())
			: $this->getRequest();
		$page->append('link', array('request' => $r,
				'stock' => $this->stock_back,
				'text' => _('Back')));
	}


	//ContentModule::helperListTitle
	protected function helperListTitle($engine, $page, $request = FALSE)
	{
		$title = $page->getProperty('title');

		$page->append('title', array('stock' => $this->name,
				'text' => $title));
	}


	//ContentModule::helperListToolbar
	protected function helperListToolbar($engine, $page, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$user = ($request !== FALSE)
			? User::lookup($engine, $request->getTitle(),
				$request->getID()) : FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$r = new Request($this->name, 'list', $uid,
			$uid ? $user->getUsername() : FALSE);
		$toolbar = $page->append('toolbar');
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $r));
		$r = $this->getRequest('submit');
		if($this->canSubmit($engine, $request))
			$toolbar->append('button', array(
					'stock' => $this->stock_content_submit,
					'request' => $r,
					'text' => $this->text_content_submit_content));
		if($uid !== FALSE && $uid === $cred->getUserID()
				&& $this->canPublish($engine, $request))
		{
			$toolbar->append('button', array('stock' => 'post',
						'text' => _('Publish'),
						'type' => 'submit',
						'name' => 'action',
						'value' => 'post'));
			$toolbar->append('button', array('stock' => 'unpost',
						'text' => _('Unpublish'),
						'type' => 'submit',
						'name' => 'action',
						'value' => 'unpost'));
		}
	}


	//ContentModule::helperListView
	protected function helperListView($engine, $request = FALSE)
	{
		$class = $this->content_class;
		$cred = $engine->getCredentials();
		$user = ($request !== FALSE && $request->getID() !== FALSE)
			? User::lookup($engine, $request->getTitle(),
				$request->getID()) : FALSE;
		$r = FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		if($uid === $cred->getUserID())
			$r = new Request($this->name, 'list', $uid,
				$uid ? $user->getUsername() : FALSE);
		$columns = $class::getColumns();
		if($uid === $cred->getUserID()
				&& $this->canPublish($engine, $request))
			$columns['public'] = _('Public');
		return new PageElement('treeview', array('request' => $r,
				'columns' => $columns));
	}


	//ContentModule::helperPaging
	protected function helperPaging($engine, $request, $page, $limit, $pcnt)
	{
		$action = ($request !== FALSE) ? $request->getAction() : FALSE;
		$id = ($request !== FALSE) ? $request->getID() : FALSE;
		$title = ($request !== FALSE) ? $request->getTitle() : FALSE;
		$args = ($request !== FALSE)
			? $request->getParameters() : array();

		if($pcnt === FALSE || $limit <= 0 || $pcnt <= $limit)
			return;
		if($request === FALSE
				|| ($pcur = $request->get('page')) === FALSE)
			$pcur = 1;
		$pcnt = ceil($pcnt / $limit);
		unset($args['page']);
		$r = new Request($this->name, $action, $id, $title, $args);
		$form = $page->append('form', array('idempotent' => TRUE,
				'request' => $r));
		$hbox = $form->append('hbox');
		//first page
		$hbox->append('link', array('stock' => 'gotofirst',
				'request' => $r, 'text' => ''));
		//previous page
		$a = $args;
		$a['page'] = max(1, $pcur - 1);
		$r = new Request($this->name, $action, $id, $title, $a);
		$hbox->append('link', array('stock' => 'previous',
				'request' => $r, 'text' => ''));
		//entry
		$hbox->append('entry', array('name' => 'page', 'width' => '4',
				'value' => $pcur));
		$hbox->append('label', array('text' => " / $pcnt"));
		//next page
		$args['page'] = min($pcur + 1, $pcnt);
		$r = new Request($this->name, $action, $id, $title, $args);
		$hbox->append('link', array('stock' => 'next',
				'request' => $r, 'text' => ''));
		//last page
		$args['page'] = $pcnt;
		$r = new Request($this->name, $action, $id, $title, $args);
		$hbox->append('link', array('stock' => 'gotolast',
				'request' => $r, 'text' => ''));
	}


	//ContentModule::helperPost
	protected function helperPost($engine, $request)
	{
		$query = static::$query_publish;
		$args = array('module_id' => $this->getID());
		$cred = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$this->canPublish($engine, $request, FALSE, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if($cred->isAdmin())
			$query = static::$query_admin_publish;
		else
			$args['user_id'] = $cred->getUserID();
		return $this->helperApply($engine, $request, $query, $args,
				_('Content could be published successfully'),
				_('Some content could not be published'));
	}


	//ContentModule::helperRedirect
	protected function helperRedirect($engine, $request, $page,
			$text = FALSE)
	{
		if($text === FALSE)
			$text = $this->text_content_redirect_progress;
		$page->set('location', $engine->getURL($request));
		$page->set('refresh', 30);
		$box = $page->append('vbox');
		$box->append('label', array('text' => $text));
		$box = $box->append('hbox');
		$text = _('If you are not redirected within 30 seconds, please ');
		$box->append('label', array('text' => $text));
		$box->append('link', array('text' => _('click here'),
				'request' => $request));
		$box->append('label', array('text' => '.'));
		return new PageResponse($page);
	}


	//ContentModule::helperSubmitButtons
	protected function helperSubmitButtons($engine, $request, $page)
	{
		$r = $this->getRequest();

		$hbox = $page->append('hbox');
		$hbox->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel', 'text' => _('Cancel')));
		if($this->canPreview($engine, $request))
			$hbox->append('button', array('type' => 'submit',
					'stock' => 'preview',
					'name' => 'action',
					'value' => '_preview',
					'text' => _('Preview')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => $this->stock_content_submit,
				'text' => $this->text_content_submit,
				'name' => 'action', 'value' => '_submit'));
	}


	//ContentModule::helperSubmitContent
	protected function helperSubmitContent($engine, $request, $page)
	{
		$class = $this->content_class;

		$content = new $class($engine, $this);
		$page->append($content->form($engine, $request));
	}


	//ContentModule::helperSubmitPreview
	protected function helperSubmitPreview($engine, $request, $content,
			$page)
	{
		if($this->canPreview($engine, $request, $content) === FALSE
				|| $request === FALSE
				|| $request->get('_preview') === FALSE)
			return;
		$page->append($content->formPreview($engine, $request));
	}


	//ContentModule::helperToolbar
	protected function helperToolbar($engine, $request = FALSE,
			$content = FALSE, $page)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $page->append($content->displayToolbar($engine,
				$request));
	}


	//ContentModule::helperUnpost
	protected function helperUnpost($engine, $request)
	{
		$query = static::$query_unpublish;
		$args = array('module_id' => $this->getID());
		$cred = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$this->canUnpublish($engine, $request, FALSE, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if($cred->isAdmin())
			$query = static::$query_admin_unpublish;
		else
			$args['user_id'] = $cred->getUserID();
		return $this->helperApply($engine, $request, $query, $args,
				_('Content could be unpublished successfully'),
				_('Some content could not be unpublished'));
	}


	//ContentModule::helperUpdateContent
	protected function helperUpdateContent($engine, $request, $content,
			$page)
	{
		$page->append($content->form($engine, $request));
	}


	//ContentModule::helperUpdatePreview
	protected function helperUpdatePreview($engine, $request, $content,
			$page)
	{
		if($this->canPreview($engine, $request, $content) === FALSE
				|| $request === FALSE
				|| $request->get('_preview') === FALSE)
			return;
		$page->append($content->formPreview($engine, $request));
	}


	//ContentModule::helperUpdateButtons
	protected function helperUpdateButtons($engine, $request, $content,
			$page)
	{
		$hbox = $page->append('hbox');
		$r = new Request($this->name, FALSE, $request->getID(),
				$content->getTitle());
		$hbox->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel', 'text' => _('Cancel')));
		$hbox->append('button', array('type' => 'reset',
				'stock' => 'reset', 'text' => _('Reset')));
		if($this->canPreview($engine, $request))
			$hbox->append('button', array('type' => 'submit',
					'stock' => 'preview',
					'name' => 'action',
					'value' => '_preview',
					'text' => _('Preview')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'update', 'name' => 'action',
				'value' => '_submit', 'text' => _('Update')));
	}
}

?>
