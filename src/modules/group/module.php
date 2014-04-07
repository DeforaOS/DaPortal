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
//FIXME:
//- complete the import from the user module



require_once('./system/group.php');
require_once('./system/module.php');


//GroupModule
class GroupModule extends Module
{
	//public
	//methods
	//essential
	//GroupModule::GroupModule
	public function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Groups') : $title;
		parent::__construct($id, $name, $title);
	}


	//useful
	//GroupModule::call
	public function call($engine, $request, $internal = 0)
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
			case 'list':
			case 'submit':
			case 'update':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return FALSE;
	}


	//methods
	//accessors
	//GroupModule::canSubmit
	protected function canSubmit($engine, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Permission denied');
			return FALSE;
		}
		return TRUE;
	}


	//forms
	//GroupModule::formSubmit
	protected function formSubmit($engine, $request)
	{
		$r = new Request($this->name, 'submit', FALSE, FALSE,
			array('type' => 'group'));
		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		$vbox->append('entry', array('name' => 'groupname',
				'text' => _('Name: '),
				'value' => $request->get('groupname')));
		//enabled
		$vbox->append('checkbox', array('name' => 'enabled',
				'value' => $request->get('enabled')
					? TRUE : FALSE,
				'text' => _('Enabled')));
		//buttons
		$r = new Request($this->name, 'admin', FALSE, FALSE,
			array('type' => 'group'));
		$form->append('button', array('request' => $r,
				'stock' => 'cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'stock' => 'new', 'name' => 'action',
				'value' => 'submit', 'text' => _('Create')));
		return $form;
	}


	//GroupModule::formUpdate
	protected function formUpdate($engine, $request, $group, $id, $error)
	{
		//output the page
		$title = _('Update group ').$group->getGroupname();
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		$r = new Request($this->name, 'update', $request->getID(),
			$request->getID() ? $request->getTitle() : FALSE);
		$form = $page->append('form', array('request' => $r));
		//fields
		//groupname
		$form->append('entry', array('text' => _('Group name: '),
				'name' => 'groupname',
				'value' => $group->getGroupname()));
		//buttons
		$r = new Request($this->name, 'admin');
		$form->append('button', array('stock' => 'cancel',
				'request' => $r, 'text' => _('Cancel')));
		$form->append('button', array('stock' => 'update',
				'type' => 'submit', 'text' => _('Update')));
		return $page;
	}


	//useful
	//GroupModule::actions
	protected function actions($engine, $request)
	{
		$cred = $engine->getCredentials();

		if(($user = $request->get('user')) !== FALSE)
			return $this->_actionsUser($engine, $user);
		if($request->get('admin'))
			return $this->_actionsAdmin($engine, $cred,
					$this->name);
		return FALSE;
	}

	private function _actionsAdmin($engine, $cred, $module)
	{
		if(!$cred->isAdmin())
			return FALSE;
		//group creation
		$r = new Request($module, 'submit', FALSE, FALSE,
			array('type' => 'group'));
		$icon = new PageElement('image', array('stock' => 'new'));
		$link = new PageElement('link', array('request' => $r,
				'text' => _('New group')));
		$ret[] = new PageElement('row', array('icon' => $icon,
				'label' => $link));
		//administration
		$r = new Request($module, ($module == 'admin')
			? FALSE : 'admin', FALSE, FALSE,
			array('type' => 'group'));
		$icon = new PageElement('image', array('stock' => 'admin'));
		$link = new PageElement('link', array('request' => $r,
				'text' => _('Groups administration')));
		$ret[] = new PageElement('row', array('icon' => $icon,
				'label' => $link));
		return $ret;
	}

	private function _actionsUser($engine, $user)
	{
		$ret = array();
		$db = $engine->getDatabase();
		$query = $this->query_list_members;
		$args = array('user_id' => $user->getUserID());

		if($user->getUserID() == 0)
			return FALSE;
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return FALSE;
		while(($r = array_shift($res)) !== NULL)
		{
			$req = new Request($this->name, FALSE, $r['group_id'],
				$r['groupname']);
			$icon = new PageElement('image', array(
				'stock' => 'group'));
			$link = new PageElement('link', array('request' => $req,
					'text' => _('Content from group')
						.' '.$r['groupname']));
			$ret[] = new PageElement('row', array('icon' => $icon,
				'label' => $link));
		}
		return $ret;
	}


	//calls
	//GroupModule::callAdmin
	protected function callAdmin($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$actions = array('delete', 'disable', 'enable');

		if(!$cred->isAdmin())
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('Permission denied')));
		//perform actions if necessary
		if($request !== FALSE)
			foreach($actions as $a)
				if($request->get($a) !== FALSE)
				{
					$a = 'call'.$a;
					return $this->$a($engine, $request);
				}
		//list groups
		$title = _('Groups administration');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$query = $this->query_admin;
		//FIXME implement sorting
		$query .= ' ORDER BY groupname ASC';
		if(($res = $db->query($engine, $query)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('Could not list groups')));
		$columns = array('groupname' => _('Group'),
				'enabled' => _('Enabled'));
		$r = new Request($this->name, 'admin');
		$view = $page->append('treeview', array('request' => $r,
				'view' => 'details', 'columns' => $columns));
		//toolbar
		$toolbar = $view->append('toolbar');
		$toolbar->append('button', array('stock' => 'new',
				'text' => _('New group'),
				'request' => new Request($this->name,
					'submit', FALSE, FALSE,
					array('type' => 'group'))));
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $r));
		$toolbar->append('button', array('stock' => 'disable',
				'text' => _('Disable'),
				'type' => 'submit', 'name' => 'action',
				'value' => 'disable'));
		$toolbar->append('button', array('stock' => 'enable',
				'text' => _('Enable'),
				'type' => 'submit', 'name' => 'action',
				'value' => 'enable'));
		$toolbar->append('button', array('stock' => 'delete',
				'text' => _('Delete'),
				'type' => 'submit', 'name' => 'action',
				'value' => 'delete'));
		$no = new PageElement('image', array('stock' => 'no',
				'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
				'size' => 16, 'title' => _('Enabled')));
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$row = $view->append('row');
			$row->setProperty('id', 'group_id:'.$res[$i]['id']);
			$row->setProperty('groupname', $res[$i]['groupname']);
			$r = new Request($this->name, 'update', $res[$i]['id'],
				$res[$i]['groupname'],
				array('type' => 'group'));
			$link = new PageElement('link', array(
					'stock' => 'group', 'request' => $r,
					'text' => $res[$i]['groupname']));
			if($res[$i]['id'] != 0)
				$row->setProperty('groupname', $link);
			$row->setProperty('enabled', $db->isTrue(
					$res[$i]['enabled']) ? $yes : $no);
		}
		$vbox = $page->append('vbox');
		$r = new Request($this->name);
		$vbox->append('link', array('request' => $r, 'stock' => 'back',
			'text' => _('Back to my account')));
		$r = new Request('admin');
		$vbox->append('link', array('request' => $r, 'stock' => 'admin',
			'text' => _('Back to the administration')));
		return $page;
	}


	//GroupModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$title = _('Group menu');
		$actions = array();

		if($request !== FALSE && ($id = $request->getID()) !== FALSE)
			return $this->callDisplay($engine, $request);
		//determine the actions available
		if($cred->isAdmin())
			$actions[] = array('action' => 'admin',
				'stock' => 'admin',
				'title' => _('Groups administration'));
		if($this->canSubmit($engine))
			$actions[] = array('action' => 'submit',
				'stock' => 'new', 'title' => _('New group'));
		$actions[] = array('action' => 'list',
			'stock' => 'group', 'title' => _('Group list'));
		//create the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$vbox = $page->append('vbox');
		//list the actions available
		$view = $vbox->append('iconview');
		foreach($actions as $a)
		{
			$icon = new PageElement('image', array(
				'stock' => $a['stock']));
			$r = new Request($this->name, $a['action']);
			$link = new PageElement('link', array('request' => $r,
					'text' => $a['title']));
			$row = array('icon' => $icon, 'label' => $link);
			$view->append('row', $row);
		}
		$r = new Request();
		$page->append('link', array('stock' => 'back', 'request' => $r,
				'text' => _('Back to the site')));
		return $page;
	}


	//GroupModule::callDelete
	protected function callDelete($engine, $request)
	{
		$query = $this->query_delete;

		return $this->helperApply($engine, $request, $query, 'admin',
			_('Group(s) could be deleted successfully'),
			_('Some group(s) could not be deleted'));
	}


	//GroupModule::callDisable
	protected function callDisable($engine, $request)
	{
		$query = $this->query_disable;

		return $this->helperApply($engine, $request, $query, 'admin',
			_('Group(s) could be disabled successfully'),
			_('Some group(s) could not be disabled'));
	}


	//GroupModule::callDisplay
	protected function callDisplay($engine, $request)
	{
		$database = $engine->getDatabase();
		$query = $this->query_content;
		$cred = $engine->getCredentials();
		$stock = $this->name;
		$link = FALSE;

		//obtain the list of modules
		if(($res = $database->query($engine, $query)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => 'Could not list modules'));
		$page = new Page;
		if(($gid = $request->getID()) !== FALSE)
		{
			$group = Group::lookup($engine, $request->getTitle(),
					$gid);
			$title = _('Content from group')
				.' '.$request->getTitle();
			$stock = 'content';
		}
		else if(($gid = $cred->getGroupID()) != 0)
		{
			$group = Group::lookup($engine, $cred->getUsername(),
					$gid);
			$title = _('My content');
			$r = new Request($this->name);
			$link = new PageElement('link', array('stock' => 'back',
					'request' => $r,
					'text' => _('Back to my account')));
		}
		if($group === FALSE || $group->getGroupID() == 0)
			return $this->callDefault($engine, new Request);
		//title
		$page->setProperty('title', $title);
		$page->append('title', array('stock' => $stock,
				'text' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox2 = $vbox->append('vbox');
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$r = new Request($res[$i]['name'], 'actions', FALSE,
				FALSE, array('group' => $group));
			$rows = $engine->process($r, TRUE);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$text = ucfirst($res[$i]['name']);
			$vbox2->append('title', array(
					'stock' => $res[$i]['name'],
					'text' => $text));
			$view = $vbox2->append('iconview');
			foreach($rows as $r)
				$view->append($r);
		}
		//buttons
		if($link !== FALSE)
			$vbox->append($link);
		$r = new Request($this->name, 'list', $group->getGroupID(),
			$group->getGroupname());
		$vbox->append('link', array('request' => $r, 'stock' => 'user',
			'text' => _('Members of group')
				.' '.$group->getGroupname()));
		$r = new Request($this->name);
		$vbox->append('link', array('request' => $r, 'stock' => 'back',
				'text' => _('Back to the group menu')));
		return $page;
	}


	//GroupModule::callEnable
	protected function callEnable($engine, $request)
	{
		$query = $this->query_enable;

		return $this->helperApply($engine, $request, $query, 'admin',
			_('Group(s) could be enabled successfully'),
			_('Some group(s) could not be enabled'));
	}


	//GroupModule::callList
	protected function callList($engine, $request)
	{
		$db = $engine->getDatabase();
		$query = $this->query_list;

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->_listGroup($engine, $request);
		$title = _('Group list');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//obtain the list of groups
		$error = _('Could not list the groups');
		if(($res = $db->query($engine, $query)) === FALSE)
		{
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return $page;
		}
		$columns = array('title' => _('Group'),
			'members' => _('Members'));
		$view = $page->append('treeview', array('columns' => $columns));
		while(($r = array_shift($res)) != NULL)
		{
			$request = new Request($this->name, FALSE, $r['id'],
				$r['groupname']);
			$r['title'] = new PageElement('link', array(
				'stock' => 'group', 'request' => $request,
				'text' => $r['groupname']));
			$request = new Request($this->name, 'list', $r['id'],
				$r['groupname']);
			$r['members'] = new PageElement('link', array(
				'stock' => 'user', 'request' => $request,
				'text' => _('Members of group')
					.' '.$r['groupname']));
			$view->append('row', $r);
		}
		$r = new Request($this->name);
		$page->append('link', array('stock' => 'back', 'request' => $r,
				'text' => _('Back to the group menu')));
		return $page;
	}

	private function _listGroup($engine, $request)
	{
		$db = $engine->getDatabase();
		$id = $request->getID();
		$group = $request->getTitle();
		$query = ($group !== FALSE) ? $this->query_list_group_groupname
			: $this->query_list_group;
		$args = ($group !== FALSE) ? array('group_id' => $id,
			'groupname' => $group) : array('group_id' => $id);

		$title = _('Members of group').' '.$group;
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$vbox = $page->append('vbox');
		//obtain the list of groups
		$error = _('Could not list the members for this group');
		if(($res = $db->query($engine, $query, $args)) === FALSE)
		{
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return $page;
		}
		$columns = array('title' => _('Username'),
			'fullname' => _('Full name'));
		$view = $vbox->append('treeview', array('columns' => $columns));
		while(($r = array_shift($res)) != NULL)
		{
			$request = new Request('user', FALSE, $r['id'],
				$r['username']);
			$r['title'] = new PageElement('link', array(
				'stock' => 'user', 'request' => $request,
				'text' => $r['username']));
			$view->append('row', $r);
		}
		//buttons
		$r = new Request($this->name, FALSE, $id, $group);
		$vbox->append('link', array('stock' => 'content',
				'request' => $r,
				'text' => _('Content from group').' '.$group));
		$r = new Request($this->name, 'list');
		$vbox->append('link', array('stock' => 'back', 'request' => $r,
				'text' => _('Back to the group list')));
		return $page;
	}


	//GroupModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$error = _('Permission denied');
		$title = _('New group');

		//check permissions
		if($this->canSubmit($engine, $error) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//create the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//process the request
		$group = FALSE;
		if(($error = $this->_submitProcess($engine, $request, $group))
				=== FALSE)
			return $this->_submitSuccess($engine, $request, $page,
					$group);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//form
		$form = $this->formSubmit($engine, $request);
		$page->append($form);
		return $page;
	}

	protected function _submitProcess($engine, $request, &$group)
	{
		//verify the request
		if($request === FALSE || $request->get('submit') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
		if(($groupname = $request->get('groupname')) === FALSE)
			return _('Invalid arguments');
		$enabled = $request->get('enabled') ? TRUE : FALSE;
		//create the group
		$error = FALSE;
		$group = Group::insert($engine, $groupname, $enabled, $error);
		if($group === FALSE)
			return $error;
		//no error
		return FALSE;
	}

	protected function _submitSuccess($engine, $request, $page, $group)
	{
		$r = new Request($this->name, FALSE, $group->getGroupID(),
			$group->getGroupname());
		$this->helperRedirect($engine, $r, $page);
		return $page;
	}


	//GroupModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		$cred = $engine->getCredentials();
		$id = $request->getID();
		$groupname = $request->getTitle();
		$error = TRUE;

		//determine which group to update
		if($id === FALSE)
			$groupname = FALSE;
		$group = Group::lookup($engine, $groupname, $id);
		if($group === FALSE || ($id = $group->getGroupID()) == 0)
		{
			$error = _('Could not find the group to update');
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		}
		if(!$cred->isAdmin())
		{
			$error = _('Permission denied');
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		}
		//process update
		if(!$request->isIdempotent())
			$error = $this->_updateProcess($engine, $request,
					$group);
		if($error === FALSE)
			//update was successful
			return $this->_updateSuccess($engine, $request);
		return $this->formUpdate($engine, $request, $group, $id,
				$error);
	}

	private function _updateProcess($engine, $request, $group)
	{
		$ret = '';
		$db = $engine->getDatabase();
		$cred = $engine->getCredentials();

		if(($groupname = $request->get('groupname')) === FALSE)
			$ret .= _("The group name is required\n");
		if(strlen($ret) > 0)
			return $ret;
		//update the group
		$error = '';
		$args = array('group_id' => $group->getGroupID(),
			'groupname' => $groupname);
		if($db->query($engine, $this->query_update, $args) === FALSE)
			return _('Could not update the group');
		return FALSE;
	}

	private function _updateSuccess($engine, $request)
	{
		$id = $request->getID();

		$title = _('Group update');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$info = _('The group was updated successfully');
		$dialog = $page->append('dialog', array('type' => 'info',
				'text' => $info));
		$r = new Request($this->name, 'admin');
		$dialog->append('button', array('stock' => 'admin',
				'request' => $r,
				'text' => _('Groups administration')));
		$text = _('Group menu');
		$r = new Request($this->name, FALSE);
		$dialog->append('button', array('stock' => 'user',
				'request' => $r, 'text' => $text));
		return $page;
	}


	//helpers
	//GroupModule::helperApply
	protected function helperApply($engine, $request, $query, $fallback,
			$success, $failure)
	{
		//XXX copied from ContentModule
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();

		if(!$cred->isAdmin())
		{
			//must be admin
			$page = $this->callDefault($engine);
			$error = _('Permission denied');
			$page->prepend('dialog', array('type' => 'error',
					'text' => $error));
			return $page;
		}
		$fallback = 'call'.$fallback;
		if($request->isIdempotent())
			//must be safe
			return $this->$fallback($engine);
		$type = 'info';
		$message = $success;
		$parameters = $request->getParameters();
		foreach($parameters as $k => $v)
		{
			$x = explode(':', $k);
			if(count($x) != 2 || $x[0] != 'group_id'
					|| !is_numeric($x[1]))
				continue;
			$args = array('group_id' => $x[1]);
			$res = $db->query($engine, $query, $args);
			if($res !== FALSE)
				continue;
			$type = 'error';
			$message = $failure;
		}
		$page = $this->$fallback($engine);
		//FIXME place this under the title
		$page->prepend('dialog', array('type' => $type,
				'text' => $message));
		return $page;
	}


	//GroupModule::helperRedirect
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


	//private
	//properties
	//queries
	private $query_admin = 'SELECT group_id AS id, groupname,
		daportal_group.enabled AS enabled
		FROM daportal_group';
	private $query_content = "SELECT name
	       	FROM daportal_module
		WHERE enabled='1'
	       	ORDER BY name ASC";
	//IN:	group_id
	private $query_delete = "DELETE FROM daportal_group
		WHERE group_id=:group_id";
	//IN:	group_id
	private $query_disable = "UPDATE daportal_group
		SET enabled='0'
		WHERE group_id=:group_id";
	//IN:	group_id
	private $query_enable = "UPDATE daportal_group
		SET enabled='1'
		WHERE group_id=:group_id";
	private $query_list = "SELECT group_id AS id, groupname
		FROM daportal_group_enabled
		WHERE group_id <> '0'";
	//IN:	group_id
	private $query_list_group = 'SELECT daportal_user_enabled.user_id AS id,
		username, fullname
		FROM daportal_user_group, daportal_user_enabled
		WHERE daportal_user_group.user_id=daportal_user_enabled.user_id
		AND daportal_user_group.group_id=:group_id';
	//IN:	group_id
	//	groupname
	//FIXME should return an error if the group does not exist
	private $query_list_group_groupname = 'SELECT
		daportal_user_enabled.user_id AS id, username, fullname
		FROM daportal_group_enabled, daportal_user_group,
		daportal_user_enabled
		WHERE daportal_group_enabled.group_id=daportal_user_group.group_id
		AND daportal_user_group.user_id=daportal_user_enabled.user_id
		AND daportal_user_group.group_id=:group_id
		AND daportal_group_enabled.groupname=:groupname';
	//IN:	user_id
	private $query_list_members = 'SELECT
		daportal_group_enabled.group_id AS group_id, groupname
		FROM daportal_user_group, daportal_group_enabled
		WHERE daportal_user_group.group_id
		=daportal_group_enabled.group_id
		AND user_id=:user_id';
	//IN:	group_id
	//	groupname
	private $query_update = 'UPDATE daportal_group
		SET groupname=:groupname
		WHERE group_id=:group_id';
}

?>
