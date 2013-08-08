<?php //$Id$
//Copyright (c) 2012-2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/content.php');
require_once('./system/module.php');
require_once('./system/user.php');


//ContentModule
abstract class ContentModule extends Module
{
	//public
	//methods
	//useful
	//ContentModule::call
	public function call($engine, $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		switch($action)
		{
			case 'actions':
				return $this->$action($engine, $request);
			case 'admin':
			case 'default':
			case 'display':
			case 'headline':
			case 'list':
			case 'preview':
			case 'publish':
			case 'submit':
			case 'update':
				$action = 'call'.ucfirst($action);
				return $this->$action($engine, $request);
		}
		return FALSE;
	}


	//protected
	//properties
	protected $content_class = 'Content';
	protected $content_headline_count = 6;
	protected $content_list_count = 10;
	protected $content_list_admin_count = 20;
	protected $content_list_admin_order = 'timestamp DESC';
	protected $content_preview_length = 150;
	protected $helper_apply_args;
	protected $text_content_admin = 'Content administration';
	protected $text_content_headline_title = 'Content headlines';
	protected $text_content_list_title = 'Content list';
	protected $text_content_list_title_by = 'Content by';
	protected $text_content_post = 'Publish';
	protected $text_content_publish_progress
		= 'Publication in progress, please wait...';
	protected $text_content_submit = 'Submit content';
	protected $text_content_submit_progress
		= 'Submission in progress, please wait...';
	protected $text_content_title = 'Content';
	protected $text_content_update_progress
			= 'Update in progress, please wait...';

	//queries
	protected $query_admin_delete = 'DELETE FROM daportal_content
		WHERE module_id=:module_id
		AND content_id=:content_id';
	protected $query_admin_disable = "UPDATE daportal_content
		SET enabled='0'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	protected $query_admin_enable = "UPDATE daportal_content
		SET enabled='1'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	protected $query_admin_post = "UPDATE daportal_content
		SET public='1'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	protected $query_admin_unpost = "UPDATE daportal_content
		SET public='0'
		WHERE module_id=:module_id
		AND content_id=:content_id";
	protected $query_delete = 'DELETE FROM daportal_content
		WHERE module_id=:module_id
		AND content_id=:content_id
		AND user_id=:user_id';
	protected $query_disable = "UPDATE daportal_content
		SET enabled='0'
		WHERE module_id=:module_id
		AND content_id=:content_id AND user_id=:user_id";
	protected $query_enable = "UPDATE daportal_content
		SET enabled='1'
		WHERE module_id=:module_id
		AND content_id=:content_id AND user_id=:user_id";
	//IN:	module_id
	protected $query_list = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content_public.enabled AS enabled, public,
		content
		FROM daportal_content_public, daportal_user_enabled
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id';
	//IN:	module_id
	protected $query_list_admin = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname,
		title, daportal_content.enabled AS enabled,
		daportal_content.public AS public
		FROM daportal_content, daportal_user_enabled, daportal_group
		WHERE daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id';
	//IN:	module_id
	protected $query_list_admin_count = 'SELECT COUNT(*)
		FROM daportal_content, daportal_user_enabled, daportal_group
		WHERE daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id';
	//IN:	module_id
	protected $query_list_count = 'SELECT COUNT(*)
		FROM daportal_content_public
		WHERE daportal_content_public.module_id=:module_id';
	//IN:	module_id
	//	user_id
	protected $query_list_user = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content_public.enabled AS enabled
		FROM daportal_content_public, daportal_user_enabled
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id=daportal_user_enabled.user_id
		AND daportal_user_enabled.user_id=:user_id';
	//IN:	module_id
	//	user_id
	protected $query_list_user_count = 'SELECT COUNT(*)
		FROM daportal_content_public
		WHERE daportal_content_public.module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	protected $query_post = "UPDATE daportal_content
		SET public='1'
		WHERE module_id=:module_id
		AND user_id=:user_id
		AND content_id=:content_id";
	//IN:	module_id
	//	user_id
	//	content_id
	protected $query_unpost = "UPDATE daportal_content
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
		$this->helper_apply_args = array('module_id' => $id);
		//translations
		$this->text_content_admin = _('Content administration');
		$this->text_content_headline_title = _('Content headlines');
		$this->text_content_list_title = _('Content list');
		$this->text_content_list_title_by = _('Content by');
		$this->text_content_post = _('Publish');
		$this->text_content_publish_progress
			= _('Publication in progress, please wait...');
		$this->text_content_submit = _('Submit content');
		$this->text_content_submit_progress
			= _('Submission in progress, please wait...');
		$this->text_content_title = _('Content');
		$this->text_content_update_progress
			= _('Update in progress, please wait...');
	}


	//accessors
	//ContentModule::canAdmin
	protected function canAdmin($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canAdmin($engine, $request, $error);
	}


	//ContentModule::canPost
	protected function canPost($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canPost($engine, $request, $error);
	}


	//ContentModule::canPreview
	protected function canPreview($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canPreview($engine, $request, $error);
	}


	//ContentModule::canSubmit
	protected function canSubmit($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canSubmit($engine, $request, $error);
	}


	//ContentModule::canUnpost
	protected function canUnpost($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canUnpost($engine, $request, $error);
	}


	//ContentModule::canUpdate
	protected function canUpdate($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		$class = $this->content_class;

		if($content === FALSE)
			$content = new $class($engine, $this);
		return $content->canUpdate($engine, $request, $error);
	}


	//ContentModule::_get
	//XXX obsolete?
	protected function _get($engine, $id, $title = FALSE, $request = FALSE)
	{
		$class = $this->content_class;
		return $class::load($engine, $this, $id, $title);
	}


	//ContentModule::getToolbar
	protected function getToolbar($engine, $request = FALSE,
			$content = FALSE)
	{
		return new PageElement('toolbar');
	}


	//forms
	//ContentModule::formSubmit
	protected function formSubmit($engine, $request)
	{
		//FIXME use $content->form() directly instead
		$r = new Request($this->name, 'submit');

		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		//title
		$this->helperSubmitTitle($engine, $request, $vbox);
		//content
		$this->helperSubmitContent($engine, $request, $vbox);
		//buttons
		$this->helperSubmitButtons($engine, $request, $form);
		return $form;
	}


	//ContentModule::formUpdate
	protected function formUpdate($engine, $request, $content)
	{
		$r = new Request($this->name, 'update', $content->getID());

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

		if(($user = $request->getParameter('user')) !== FALSE)
			return $this->helperActionsUser($engine, $request,
					$user);
		$ret = array();
		if($cred->isAdmin())
		{
			$r = $this->helperActionsAdmin($engine, $request);
			$ret = array_merge($ret, $r);
		}
		if($request->getParameter('admin') !== FALSE)
			return $ret;
		if($this->canSubmit($engine))
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
		$query = $this->query_list_admin;
		$args = array('module_id' => $this->id);
		$p = ($request !== FALSE) ? $request->getParameter('page') : 0;
		$pcnt = FALSE;
		$actions = array('delete', 'disable', 'enable', 'post', 'unpost');
		$error = FALSE;

		if($request === FALSE)
			$request = new Request($this->name, 'admin');
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
			return $dialog;
		}
		//perform actions if necessary
		if($request !== FALSE)
			foreach($actions as $a)
				if($request->getParameter($a) !== FALSE)
				{
					$a = 'call'.ucfirst($a);
					return $this->$a($engine, $request);
				}
		//administrative page
		$page = new Page;
		$title = $this->text_content_admin;
		$page->setProperty('title', $title);
		$element = $page->append('title', array('stock' => 'admin',
				'text' => $title));
		if(is_string(($order = $this->content_list_admin_order)))
			$query .= ' ORDER BY '.$order;
		//paging
		if(($limit = $this->content_list_admin_count) > 0)
		{
			//obtain the total number of records available
			$q = $this->query_list_admin_count;
			if(($res = $db->query($engine, $q, $args)) !== FALSE
					&& count($res) == 1)
				$pcnt = $res[0][0];
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
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$r = new Request($this->name, 'admin');
		if($request !== FALSE && ($type = $request->getParameter(
					'type')) !== FALSE)
			$r->setParameter('type', $type);
		$columns = array('icon' => '', 'title' => _('Title'),
			'enabled' => _('Enabled'), 'public' => _('Public'),
			'username' => _('Username'), 'date' => _('Date'));
		$treeview = $page->append('treeview', array('request' => $r,
				'columns' => $columns, 'alternate' => TRUE));
		//toolbar
		$this->helperAdminToolbar($engine, $treeview, $request);
		//rows
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$row = $treeview->append('row');
			$this->helperAdminRow($engine, $row, $res[$i]);
		}
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $pcnt);
		//buttons
		$vbox = $page->append('vbox');
		$this->helperAdminButtons($engine, $vbox, $request);
		return $page;
	}


	//ContentModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$class = $this->content_class;
		$p = ($request !== FALSE) ? $request->getParameter('page') : 0;
		$pcnt = FALSE;

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$page = new Page(array('title' => $this->text_content_title));
		$page->append('title', array('stock' => $this->name,
				'text' => $this->text_content_title));
		//paging
		if(($limit = $this->content_list_count) > 0)
		{
			$pcnt = $class::countAll($engine, $this);
			if($pcnt !== FALSE)
			{
				$offset = FALSE;
				if(is_numeric($p) && $p > 1)
					$offset = $limit * ($p - 1);
			}
		}
		$list = $class::listAll($engine, $this, $limit, $offset);
		foreach($list as $content)
			$page->append($content->preview($engine, $request));
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $pcnt);
		return $page;
	}


	//ContentModule::callDelete
	protected function callDelete($engine, $request)
	{
		$query = $this->query_delete;
		$cred = $engine->getCredentials();

		if($cred->isAdmin())
			$query = $this->query_admin_delete;
		return $this->helperApply($engine, $request, $query, 'admin',
				_('Content could be deleted successfully'),
				_('Some content could not be deleted'));
	}


	//ContentModule::callDisable
	protected function callDisable($engine, $request)
	{
		$query = $this->query_disable;
		$cred = $engine->getCredentials();

		if($cred->isAdmin())
			$query = $this->query_admin_disable;
		return $this->helperApply($engine, $request, $query, 'admin',
				_('Content could be disabled successfully'),
				_('Some content could not be disabled'));
	}


	//ContentModule::callDisplay
	protected function callDisplay($engine, $request)
	{
		$error = _('Could not display content');

		//obtain the content
		if(($id = $request->getID()) === FALSE)
			return $this->callDefault($engine, $request);
		if(($content = $this->_get($engine, $id, $request->getTitle(),
				$request)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		return $content->display($engine, $request);
	}


	//ContentModule::callEnable
	protected function callEnable($engine, $request)
	{
		$query = $this->query_enable;
		$cred = $engine->getCredentials();

		if($cred->isAdmin())
			$query = $this->query_admin_enable;
		return $this->helperApply($engine, $request, $query, 'admin',
				_('Content could be enabled successfully'),
				_('Some content could not be enabled'));
	}


	//ContentModule::callHeadline
	protected function callHeadline($engine, $request = FALSE)
	{
		$db = $engine->getDatabase();
		$query = $this->query_list;
		$args = array('module_id' => $this->id);
		$title = $this->text_content_headline_title;

		//view
		$columns = array('title' => _('Title'), 'date' => _('Date'),
				'username' => _('Author'));
		$view = new PageElement('treeview', array('view' => 'details',
				'title' => $title, 'columns' => $columns));
		//obtain contents
		$count = (is_integer($this->content_headline_count))
			? $this->content_headline_count : 6;
		$query .= ' ORDER BY timestamp DESC LIMIT '.$count;
		$error = _('Unable to list contents');
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		//rows
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$row = $view->append('row');
			$r = new Request($this->name, FALSE, $res[$i]['id'],
				$res[$i]['title']);
			$link = new PageElement('link', array('request' => $r,
					'text' => $res[$i]['title']));
			$row->setProperty('title', $link);
			$row->setProperty('timestamp', $res[$i]['timestamp']);
			$row->setProperty('date', $db->formatDate($engine,
					$res[$i]['timestamp']));
			$r = new Request('user', FALSE, $res[$i]['user_id'],
				$res[$i]['username']);
			$link = new PageElement('link', array('request' => $r,
					'stock' => 'user',
					'text' => $res[$i]['username']));
			$row->setProperty('username', $link);
			//FIXME use the $content_class somehow
			$content = $res[$i]['content'];
			if(($len = $this->content_preview_length) > 0
					&& strlen($content) > $len)
				$content = substr($content, 0, $len).'...';
			$row->setProperty('content', $content);
		}
		return $view;
	}


	//ContentModule::callList
	protected function callList($engine, $request = FALSE)
	{
		$db = $engine->getDatabase();
		$user = ($request !== FALSE)
			? new User($engine, $request->getID(),
				$request->getTitle()) : FALSE;
		$p = ($request !== FALSE) ? $request->getParameter('page') : 0;
		$pcnt = FALSE;
		$error = _('Unable to list contents');

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$title = $this->text_content_list_title;
		if($uid !== FALSE)
			$title = $this->text_content_list_title_by.' '
				.$user->getUsername();
		//title
		$page = new Page(array('title' => $title));
		$this->helperListTitle($engine, $page, $request);
		//query
		$args = array('module_id' => $this->id);
		$query = $this->query_list;
		$cquery = $this->query_list_count;
		if($uid !== FALSE)
		{
			$query = $this->query_list_user;
			$cquery = $this->query_list_user_count;
			$args['user_id'] = $uid;
		}
		$query .= ' ORDER BY title ASC';
		//paging
		if(($limit = $this->content_list_count) > 0)
		{
			//obtain the total number of records available
			if(($res = $db->query($engine, $cquery, $args))
					!== FALSE && count($res) == 1)
				$pcnt = $res[0][0];
			if($pcnt !== FALSE)
			{
				$offset = FALSE;
				if(is_numeric($p) && $p > 1)
					$offset = $limit * ($p - 1);
				$query .= $db->offset($limit, $offset);
			}
		}
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//view
		$treeview = $this->helperListView($engine, $page, $request);
		//toolbar
		$this->helperListToolbar($engine, $treeview, $request);
		//rows
		$no = new PageElement('image', array('stock' => 'no',
			'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
			'size' => 16, 'title' => _('Enabled')));
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$content = new Content($engine, $this, $res[$i]);
			//title
			$r = $content->getRequest();
			$link = new PageElement('link', array('request' => $r,
				'text' => $content->getTitle()));
			$res[$i]['title'] = $link;
			$res[$i]['enabled'] = $db->isTrue($res[$i]['enabled'])
				? $yes : $no;
			//username
			$r = new Request('user', FALSE, $res[$i]['user_id'],
				$res[$i]['username']);
			$link = new PageElement('link', array('request' => $r,
					'stock' => 'user',
					'text' => $res[$i]['username']));
			$res[$i]['username'] = $link;
			//date
			$res[$i]['date'] = $content->getDate($engine);
			//id
			$res[$i]['id'] = 'content_id:'.$res[$i]['id'];
			$treeview->append('row', $res[$i]);
		}
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $pcnt);
		//buttons
		$this->helperListButtons($engine, $page, $request);
		return $page;
	}


	//ContentModule::callPost
	protected function callPost($engine, $request)
	{
		$query = $this->query_post;
		$cred = $engine->getCredentials();

		if(!$this->canPost($engine, $request, FALSE, $error))
			return new PageElement('dialog', array('type' => 'error',
					'text' => $error));
		if($cred->isAdmin())
			$query = $this->query_admin_post;
		return $this->helperApply($engine, $request, $query, 'admin',
				_('Content could be posted successfully'),
				_('Some content could not be posted'));
	}


	//ContentModule::callPreview
	protected function callPreview($engine, $request)
	{
		$error = _('Could not preview content');

		//obtain the content
		if(($content = $this->_get($engine, $request->getID(),
				$request->getTitle(), $request)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$page = new Page(array('title' => $content->getTitle()));
		$page->append($content->preview($engine, $request));
		return $page;
	}


	//ContentModule::callPublish
	protected function callPublish($engine, $request)
	{
		$error = _('Could not preview content');

		//obtain the content
		if(($content = $this->_get($engine, $request->getID(),
				$request->getTitle(), $request)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//check permissions
		if($content->canPost($engine, $request, $error) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//create the page
		$title = $this->text_content_post.' '.$content->getTitle();
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
			'text' => $title));
		//toolbar
		$page->append($content->displayToolbar($engine, $request));
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
		$r = new Request($this->name, 'publish', $request->getID(),
			$request->getTitle());
		$form = $page->append('form', array('request' => $r));
		//buttons
		$r = new Request($this->name, FALSE, $request->getID(),
				$request->getTitle());
		$form->append('button', array('request' => $r,
				'stock' => 'cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'name' => 'action', 'value' => 'publish',
				'text' => $this->text_content_post));
		return $page;
	}

	protected function _publishProcess($engine, $request, $content)
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$query = $this->query_post;
		$args = array('module_id' => $this->id,
			'content_id' => $content->getID(),
			'user_id' => $cred->getUserID());

		//verify the request
		if($request->getParameter('publish') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
		//publish the content
		if($db->query($engine, $query, $args) === FALSE)
			return _('Internal server error');
		return FALSE;
	}

	protected function _publishSuccess($engine, $request, $content, $page)
	{
		$r = new Request($this->name, FALSE, $content->getID(),
			$content->getTitle());
		$this->helperRedirect($engine, $r, $page,
				$this->text_content_publish_progress);
		return $page;
	}


	//ContentModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$user = new User($engine, $cred->getUserID());
		$title = $this->text_content_submit;
		$error = _('Permission denied');

		//check permissions
		if($this->canSubmit($engine, $request, FALSE, $error) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//create the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//toolbar
		$class = $this->content_class;
		$content = new $class($engine, $this);
		$page->append($content->displayToolbar($engine, $request));
		//process the request
		if(($error = $this->_submitProcess($engine, $request, $content))
				=== FALSE)
			return $this->_submitSuccess($engine, $request,
					$content, $page);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//preview
		$this->helperSubmitPreview($engine, $page, $request, $content);
		//form
		$form = $this->formSubmit($engine, $request);
		$page->append($form);
		return $page;
	}

	protected function _submitProcess($engine, $request, &$content)
	{
		$class = $this->content_class;

		//verify the request
		if($request === FALSE
				|| $request->getParameter('submit') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
		//store the content uploaded
		$content = array('title' => $request->getParameter('title'),
			'content' => $request->getParameter('content'),
			'enabled' => TRUE,
			'public' => $request->getParameter('public')
			? TRUE : FALSE);
		$content = new $class($engine, $this, $content);
		if($content->save($engine) === FALSE)
			return _('Internal server error');
		return FALSE;
	}

	protected function _submitSuccess($engine, $request, $content, $page)
	{
		$r = $content->getRequest();
		$this->helperRedirect($engine, $r, $page,
				$this->text_content_submit_progress);
		return $page;
	}


	//ContentModule::callUnpost
	protected function callUnpost($engine, $request)
	{
		$query = $this->query_unpost;
		$cred = $engine->getCredentials();

		if(!$this->canUnpost($engine, $request, FALSE, $error))
			return new PageElement('dialog', array('type' => 'error',
					'text' => $error));
		if($cred->isAdmin())
			$query = $this->query_admin_unpost;
		return $this->helperApply($engine, $request, $query, 'admin',
				_('Content could be unposted successfully'),
				_('Some content could not be unposted'));
	}


	//ContentModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		//obtain the content
		$error = _('Unable to fetch content');
		if(($content = $this->_get($engine, $request->getID()))
				=== FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//check permissions
		$error = _('Permission denied');
		if($this->canUpdate($engine, $request, $content, $error)
				=== FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//create the page
		$title = _('Update ').$content->getTitle();
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//toolbar
		$toolbar = $content->displayToolbar($engine, $request);
		$page->append($toolbar);
		//process the request
		if(($error = $this->_updateProcess($engine, $request, $content))
				=== FALSE)
			return $this->_updateSuccess($engine, $request,
					$content, $page);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//preview
		if($request->getParameter('_preview') !== FALSE)
			$this->helperUpdatePreview($engine, $request, $content,
					$page);
		$form = $this->formUpdate($engine, $request, $content);
		$page->append($form);
		return $page;
	}

	protected function _updateProcess($engine, $request, &$content)
	{
		$class = $this->content_class;
		$fields = array('title', 'content');

		//verify the request
		if($request->getParameter('submit') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
		//update the content
		$content = $content->getProperties();
		foreach($fields as $f)
			if(($v = $request->getParameter($f)) !== FALSE)
				$content[$f] = $v;
		$content = new $class($engine, $this, $content);
		if($content->save($engine) === FALSE)
			return _('Internal server error');
		return FALSE;
	}

	protected function _updateSuccess($engine, $request, $content, $page)
	{
		$r = new Request($this->name, FALSE, $content->getID(),
			$content->getTitle());
		$this->helperRedirect($engine, $r, $page,
				$this->text_content_update_progress);
		return $page;
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
		$ret = array();
		$admin = $request->getParameter('admin');

		if($admin === 0)
			return $ret;
		$r = new Request($this->name, 'admin');
		$ret[] = $this->helperAction($engine, 'admin', $r,
				$this->text_content_admin);
		return $ret;
	}


	//ContentModule::helperActionsSubmit
	protected function helperActionsSubmit($engine, $request)
	{
		$ret = array();

		$r = new Request($this->name, 'submit');
		$ret[] = $this->helperAction($engine, 'new', $r,
				$this->text_content_submit);
		return $ret;
	}


	//ContentModule::helperActionsUser
	protected function helperActionsUser($engine, $request, $user)
	{
		$ret = array();
		$cred = $engine->getCredentials();

		if($user->getUserID() == $cred->getUserID()
				&& $this->canSubmit($engine))
			$ret = $this->helperActionsSubmit($engine, $request);
		//user's content
		$request = new Request($this->name, 'list', $user->getUserID(),
			$user->getUsername());
		$ret[] = $this->helperAction($engine, $this->name, $request,
				$this->text_content_list_title_by
				.' '.$user->getUsername());
		return $ret;
	}


	//ContentModule::helperAdminButtons
	protected function helperAdminButtons($engine, $page, $request)
	{
		$r = new Request($this->name);
		$page->append('link', array('request' => $r, 'stock' => 'back',
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

		$row->setProperty('id', 'content_id:'.$res['id']);
		$row->setProperty('icon', '');
		$r = new Request($this->name, 'update', $res['id'],
			$res['title']);
		$link = new PageElement('link', array('request' => $r,
				'stock' => $this->name,
				'text' => $res['title']));
		$row->setProperty('title', $link);
		$row->setProperty('enabled', $db->isTrue($res['enabled'])
				? $yes : $no);
		$row->setProperty('public', $db->isTrue($res['public'])
				? $yes : $no);
		$r = new Request('user', FALSE, $res['user_id'],
			$res['username']);
		$link = new PageElement('link', array('request' => $r,
				'stock' => 'user',
				'text' => $res['username']));
		$row->setProperty('username', $link);
		$date = $db->formatDate($engine, $res['timestamp']);
		$row->setProperty('date', $date);
	}


	//ContentModule::helperAdminToolbar
	protected function helperAdminToolbar($engine, $page, $request)
	{
		$r = new Request($this->name, 'admin', FALSE, FALSE,
			array('type' => $request->getParameter('type'),
				'page' => $request->getParameter('page')));

		$toolbar = $page->append('toolbar');
		$toolbar->append('button', array('stock' => 'refresh',
					'text' => _('Refresh'),
					'request' => $r));
		//disable
		$toolbar->append('button', array('stock' => 'disable',
					'text' => _('Disable'),
					'type' => 'submit', 'name' => 'action',
					'value' => 'disable'));
		//enable
		$toolbar->append('button', array('stock' => 'enable',
					'text' => _('Enable'),
					'type' => 'submit', 'name' => 'action',
					'value' => 'enable'));
		//unpost
		$toolbar->append('button', array('stock' => 'unpost',
					'text' => _('Unpost'),
					'type' => 'submit', 'name' => 'action',
					'value' => 'unpost'));
		//post
		$toolbar->append('button', array('stock' => 'post',
					'text' => _('Post'),
					'type' => 'submit', 'name' => 'action',
					'value' => 'post'));
		//delete
		$toolbar->append('button', array('stock' => 'delete',
					'text' => _('Delete'),
					'type' => 'submit', 'name' => 'action',
					'value' => 'delete'));
	}


	//ContentModule::helperApply
	protected function helperApply($engine, $request, $query, $fallback,
			$success, $failure, $key = 'content_id')
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();

		if(($uid = $cred->getUserID()) == 0)
		{
			//must be logged in
			$page = $this->callDefault($engine);
			$error = _('Must be logged in');
			$page->prepend('dialog', array('type' => 'error',
						'text' => $error));
			return $page;
		}
		//prepare the fallback request
		$fallback = 'call'.ucfirst($fallback);
		$r = new Request($request->getModule(), $request->getAction(),
			$request->getID(), $request->getTitle());
		if(($type = $request->getParameter('type')) !== FALSE)
			$r->setParameter('type', $type);
		//verify the request
		if($request->isIdempotent())
			return $this->$fallback($engine, $r);
		$type = 'info';
		$message = $success;
		$parameters = $request->getParameters();
		foreach($parameters as $k => $v)
		{
			$x = explode(':', $k);
			if(count($x) != 2 || $x[0] != $key
					|| !is_numeric($x[1]))
				continue;
			$args = $this->helper_apply_args;
			$args[$key] = $x[1];
			if(!$cred->isAdmin())
				$args['user_id'] = $uid;
			if(($res = $db->query($engine, $query, $args))
					!== FALSE)
				continue;
			$type = 'error';
			$message = $failure;
		}
		$page = $this->$fallback($engine, $r);
		//FIXME place this under the title
		$page->prepend('dialog', array('type' => $type,
					'text' => $message));
		return $page;
	}


	//ContentModule::helperListButtons
	protected function helperListButtons($engine, $page, $request = FALSE)
	{
		$user = ($request !== FALSE)
			? new User($engine, $request->getID(),
				$request->getTitle()) : FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$r = ($uid !== FALSE)
			? new Request('user', 'display', $user->getUserID(),
				$user->getUsername())
			: new Request($this->name);
		$page->append('link', array('request' => $r, 'stock' => 'back',
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
			? new User($engine, $request->getID(),
				$request->getTitle()) : FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$r = new Request($this->name, 'list', $uid,
			$uid ? $user->getUsername() : FALSE);
		$toolbar = $page->append('toolbar');
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $r));
		$r = new Request($this->name, 'submit');
		if($this->canSubmit($engine))
			$toolbar->append('button', array('stock' => 'new',
					'request' => $r,
					'text' => $this->text_content_submit));
		if($uid === $cred->getUserID())
		{
			$toolbar->append('button', array('stock' => 'disable',
						'text' => _('Disable'),
						'type' => 'submit',
						'name' => 'action',
						'value' => 'disable'));
			$toolbar->append('button', array('stock' => 'enable',
						'text' => _('Enable'),
						'type' => 'submit',
						'name' => 'action',
						'value' => 'enable'));
		}
	}


	//ContentModule::helperListView
	protected function helperListView($engine, $page, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$user = ($request !== FALSE)
			? new User($engine, $request->getID(),
				$request->getTitle()) : FALSE;
		$r = FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		if($uid === $cred->getUserID())
			$r = new Request($this->name, 'list', $uid,
				$uid ? $user->getUsername() : FALSE);
		$view = $page->append('treeview', array('request' => $r));
		$columns = array('title' => _('Title'));
		if($this->canUpdate($engine, $request)
				|| $this->canPost($engine, $request))
			$columns['enabled'] = _('Enabled');
		$columns['username'] = _('Username');
		$columns['date'] = _('Date');
		$view->setProperty('columns', $columns);
		return $view;
	}


	//ContentModule::helperPaging
	protected function helperPaging($engine, $request, $page, $limit, $pcnt)
	{
		if($pcnt === FALSE || $limit <= 0 || $pcnt <= $limit)
			return;
		if(($pcur = $request->getParameter('page')) === FALSE)
			$pcur = 1;
		$pcnt = ceil($pcnt / $limit);
		$args = $request->getParameters();
		unset($args['page']);
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $args);
		$form = $page->append('form', array('idempotent' => TRUE,
				'request' => $r));
		$hbox = $form->append('hbox');
		//first page
		$hbox->append('link', array('stock' => 'gotofirst',
				'request' => $r, 'text' => ''));
		//previous page
		$a = $args;
		$a['page'] = max(1, $pcur - 1);
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $a);
		$hbox->append('link', array('stock' => 'previous',
				'request' => $r, 'text' => ''));
		//entry
		$hbox->append('entry', array('name' => 'page', 'width' => '4',
				'value' => $pcur));
		$hbox->append('label', array('text' => " / $pcnt"));
		//next page
		$args['page'] = min($pcur + 1, $pcnt);
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $args);
		$hbox->append('link', array('stock' => 'next',
				'request' => $r, 'text' => ''));
		//last page
		$args['page'] = $pcnt;
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $args);
		$hbox->append('link', array('stock' => 'gotolast',
				'request' => $r, 'text' => ''));
	}


	//ContentModule::helperRedirect
	protected function helperRedirect($engine, $request, $page,
			$text = FALSE)
	{
		if($text === FALSE)
			$text = _('Redirection in progress, please wait...');
		$page->setProperty('location', $engine->getURL($request));
		$page->setProperty('refresh', 30);
		$box = $page->append('vbox');
		$box->append('label', array('text' => $text));
		$box = $box->append('hbox');
		$text = _('If you are not redirected within 30 seconds, please ');
		$box->append('label', array('text' => $text));
		$box->append('link', array('text' => _('click here'),
				'request' => $request));
		$box->append('label', array('text' => '.'));
		return $page;
	}


	//ContentModule::helperSubmitButtons
	protected function helperSubmitButtons($engine, $request, $page)
	{
		$r = new Request($this->name);
		$page->append('button', array('request' => $r,
				'stock' => 'cancel', 'text' => _('Cancel')));
		if($this->canPreview($engine, $request))
			$page->append('button', array('type' => 'submit',
					'stock' => 'preview',
					'name' => 'action',
					'value' => '_preview',
					'text' => _('Preview')));
		$page->append('button', array('type' => 'submit',
				'stock' => 'submit', 'name' => 'action',
				'value' => 'submit', 'text' => _('Submit')));
	}


	//ContentModule::helperSubmitContent
	protected function helperSubmitContent($engine, $request, $page)
	{
		$class = $this->content_class;

		$content = new $class($engine, $this);
		$page->append($content->form($engine, $request));
	}


	//ContentModule::helperSubmitPreview
	protected function helperSubmitPreview($engine, $page, $request,
			$content)
	{
		$class = $this->content_class;
		$cred = $engine->getCredentials();
		$user = new User($engine, $cred->getUserID());

		if($request === FALSE
				|| $request->getParameter('_preview') === FALSE)
			return;
		$properties = array('title' => _('Preview: ')
				.$request->getParameter('title'),
			'content' => $request->getParameter('content'));
		$content = new $class($engine, $this, $properties);
		$vbox = $page->append('vbox');
		$vbox->append($content->displayTitle($engine, $request));
		$vbox->append($content->displayMetadata($engine, $request));
		$vbox->append($content->displayContent($engine, $request));
	}


	//ContentModule::helperSubmitTitle
	protected function helperSubmitTitle($engine, $request, $page)
	{
		$page->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->getParameter('title')));
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
				'stock' => 'cancel', 'text' => _('Cancel')));
		$hbox->append('button', array('type' => 'reset',
				'stock' => 'reset', 'text' => _('Reset')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'preview', 'name' => 'action',
				'value' => '_preview', 'text' => _('Preview')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'update', 'name' => 'action',
				'value' => 'submit', 'text' => _('Update')));
	}
}

?>
