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
//FIXME:
//- complete the import from the user module



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
			case 'disable':
			case 'display':
			case 'enable':
			case 'list':
			case 'submit':
			case 'update':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//methods
	//accessors
	//GroupModule::canDisable
	protected function canDisable(Engine $engine, Group $group = NULL,
			&$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Disabling groups is not allowed');
			return FALSE;
		}
		if($group !== NULL
				&& $group->getGroupID() == $cred->getGroupID())
		{
			$error = _('Disabling oneself is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//GroupModule::canEnable
	protected function canEnable(Engine $engine, Group $group = NULL,
			&$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Enabling groups is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//GroupModule::canSubmit
	protected function canSubmit(Engine $engine, &$error = FALSE)
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
	protected function formSubmit(Engine $engine, Request $request)
	{
		$r = $this->getRequest('submit', array('type' => 'group'));
		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		$vbox->append('entry', array('name' => 'groupname',
				'text' => _('Group name: '),
				'placeholder' => _('Group name'),
				'value' => $request->get('groupname')));
		//enabled
		$vbox->append('checkbox', array('name' => 'enabled',
				'value' => $request->get('enabled')
					? TRUE : FALSE,
				'text' => _('Enabled')));
		//buttons
		$r = $this->getRequest('admin', array('type' => 'group'));
		$form->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'stock' => 'new', 'name' => 'action',
				'value' => '_submit', 'text' => _('Create')));
		return $form;
	}


	//GroupModule::formUpdate
	protected function formUpdate(Engine $engine, Request $request,
			Group $group, $id, $error)
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
		$form->append('entry', array('name' => 'groupname',
				'text' => _('Group name: '),
				'placeholder' => _('Group name'),
				'value' => $group->getGroupname()));
		//buttons
		$r = $this->getRequest('admin');
		$form->append('button', array('stock' => 'cancel',
				'request' => $r,
				'target' => '_cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'stock' => 'update', 'name' => 'action',
				'value' => '_submit', 'text' => _('Update')));
		return $page;
	}


	//useful
	//GroupModule::actions
	protected function actions(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();

		if(($user = $request->get('user')) !== FALSE)
			return $this->_actionsUser($engine, $user);
		if($request->get('admin'))
			return $this->_actionsAdmin($engine, $cred,
					$this->name);
		return FALSE;
	}

	private function _actionsAdmin(Engine $engine, AuthCredentials $cred,
			$module)
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

	private function _actionsUser(Engine $engine, User $user)
	{
		$ret = array();
		$db = $engine->getDatabase();
		$query = static::$query_list_members;
		$args = array('user_id' => $user->getUserID());

		if($user->getUserID() == 0)
			return FALSE;
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return FALSE;
		foreach($res as $r)
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
	protected function callAdmin(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$actions = array('delete', 'disable', 'enable');
		$title = _('Groups administration');
		$dialog = FALSE;

		if(!$cred->isAdmin())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		//perform actions if necessary
		foreach($actions as $a)
			if($request->get($a) !== FALSE)
			{
				$a = '_admin'.$a;
				$dialog = $this->$a($engine, $request);
				break;
			}
		//list groups
		//FIXME implement sorting
		$query = static::$query_admin;
		$query .= ' ORDER BY groupname ASC';
		if(($res = $db->query($engine, $query)) === FALSE)
			return new ErrorResponse(_('Could not list groups'));
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if($dialog !== FALSE)
			$page->append($dialog);
		//group list
		$columns = array('groupname' => _('Group'),
				'enabled' => _('Enabled'),
				'members' => _('Members'));
		$request = $this->getRequest('admin');
		$view = $page->append('treeview', array('request' => $request,
				'view' => 'details', 'columns' => $columns));
		//toolbar
		$toolbar = $view->append('toolbar');
		$toolbar->append('button', array('stock' => 'new',
				'text' => _('New group'),
				'request' => $this->getRequest('submit',
					array('type' => 'group'))));
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $request));
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
		foreach($res as $r)
		{
			$row = $view->append('row');
			$row->set('id', 'ids['.$r['id'].']');
			//members
			$row->set('members', $r['count']);
			$request = new Request($this->name, 'list', $r['id'],
					$r['groupname']);
			$link = new PageElement('link', array(
					'stock' => 'group',
					'request' => $request,
					'text' => $r['count']));
			if($r['id'] != 0)
				$row->set('members', $link);
			//groupname
			$row->set('groupname', $r['groupname']);
			$request = new Request($this->name, 'update', $r['id'],
				$r['groupname'], array('type' => 'group'));
			$link = new PageElement('link', array(
					'stock' => 'group',
					'request' => $request,
					'text' => $r['groupname']));
			if($r['id'] != 0)
				$row->set('groupname', $link);
			$row->set('enabled', $db->isTrue($r['enabled'])
				? $yes : $no);
		}
		$vbox = $page->append('vbox');
		$request = $this->getRequest();
		$vbox->append('link', array('request' => $request,
			'stock' => 'back', 'text' => _('Back to my account')));
		$request = new Request('admin');
		$vbox->append('link', array('request' => $request,
			'stock' => 'admin',
			'text' => _('Back to the administration')));
		return new PageResponse($page);
	}

	protected function _adminDelete(Engine $engine, Request $request)
	{
		return $this->helperApplyGroup($engine, $request, 'delete',
				$this->getRequest('admin'));
	}

	protected function _adminDisable(Engine $engine, Request $request)
	{
		return $this->helperApplyGroup($engine, $request, 'disable',
				$this->getRequest('admin'));
	}

	protected function _adminEnable(Engine $engine, Request $request)
	{
		return $this->helperApplyGroup($engine, $request, 'enable',
				$this->getRequest('admin'));
	}


	//GroupModule::callDefault
	protected function callDefault(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();
		$title = _('Group menu');
		$actions = array();

		if(($id = $request->getID()) !== FALSE)
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
			$request = $this->getRequest($a['action']);
			$link = new PageElement('link', array(
					'request' => $request,
					'text' => $a['title']));
			$row = array('icon' => $icon, 'label' => $link);
			$view->append('row', $row);
		}
		$request = new Request();
		$page->append('link', array('stock' => 'back',
				'request' => $request,
				'text' => _('Back to the site')));
		return new PageResponse($page);
	}


	//GroupModule::callDisable
	protected function callDisable(Engine $engine, Request $request)
	{
		$error = _('Unknown error');

		if($request->isIdempotent())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		if(!$this->canDisable($engine, FALSE, $error))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		if(($group = Group::lookup($engine, $request->getTitle(),
					$request->getID(), TRUE)) === FALSE)
			return new ErrorResponse(_('Could not find group'),
					Response::$CODE_ENOENT);
		if(!$this->canDisable($engine, $group, $error))
			return new ErrorResponse($group->getGroupname()
					.': '.$error, Response::$CODE_EPERM);
		if(!$group->disable($engine, $error))
			return new ErrorResponse($error);
		return new StringResponse(_('Group disabled successfully'));
	}


	//GroupModule::callDisplay
	protected function callDisplay(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();
		$database = $engine->getDatabase();
		$query = static::$query_content;
		$title = FALSE;
		$stock = $this->name;
		$link = FALSE;
		$group = FALSE;

		//obtain the list of modules
		if(($res = $database->query($engine, $query)) === FALSE)
			return new ErrorResponse('Could not list modules');
		if(($gid = $request->getID()) !== FALSE)
		{
			$group = Group::lookup($engine, $request->getTitle(),
					$gid);
			if($group !== FALSE)
				$title = _('Content from group')
					.' '.$group->getGroupname();
			$stock = 'content';
		}
		else if(($gid = $cred->getGroupID()) != 0)
		{
			//FIXME use the default group instead
			$group = Group::lookup($engine, $cred->getUsername(),
					$gid);
			$title = _('My content');
			$request = $this->getRequest();
			$link = new PageElement('link', array('stock' => 'back',
					'request' => $request,
					'text' => _('Back to my account')));
		}
		$page = new Page(array('title' => $title));;
		if($group === FALSE || $group->getGroupID() == 0)
			return $this->callDefault($engine, new Request());
		//title
		$page->append('title', array('stock' => $stock,
				'text' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox2 = $vbox->append('vbox');
		foreach($res as $r)
		{
			$request = new Request($r['name'], 'actions', FALSE,
				FALSE, array('group' => $group));
			$rows = $engine->process($request, TRUE);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$text = ucfirst($r['name']);
			$vbox2->append('title', array('stock' => $r['name'],
					'text' => $text));
			$view = $vbox2->append('iconview');
			foreach($rows as $row)
				$view->append($row);
		}
		//buttons
		if($link !== FALSE)
			$vbox->append($link);
		$request = new Request($this->name, 'list',
			$group->getGroupID(), $group->getGroupname());
		$vbox->append('link', array('request' => $request,
			'stock' => 'user',
			'text' => _('Members of group')
				.' '.$group->getGroupname()));
		$request = $this->getRequest();
		$vbox->append('link', array('request' => $request,
				'stock' => 'back',
				'text' => _('Back to the group menu')));
		return new PageResponse($page);
	}


	//GroupModule::callEnable
	protected function callEnable(Engine $engine, Request $request)
	{
		$error = _('Unknown error');

		if($request->isIdempotent())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		if(!$this->canEnable($engine, FALSE, $error))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		if(($group = Group::lookup($engine, $request->getTitle(),
					$request->getID(), FALSE)) === FALSE)
			return new ErrorResponse(_('Could not find group'),
					Response::$CODE_ENOENT);
		if(!$this->canEnable($engine, $group, $error))
			return new ErrorResponse($group->getGroupname()
					.': '.$error, Response::$CODE_EPERM);
		if(!$group->enable($engine, $error))
			return new ErrorResponse($error);
		return new StringResponse(_('Group enabled successfully'));
	}


	//GroupModule::callList
	protected function callList(Engine $engine, Request $request)
	{
		$db = $engine->getDatabase();
		$query = static::$query_list;

		if($request->getID() !== FALSE)
			return $this->_listGroup($engine, $request);
		$title = _('Group list');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//obtain the list of groups
		$error = _('Could not list the groups');
		if(($res = $db->query($engine, $query)) === FALSE)
			return new ErrorResponse($error);
		$columns = array('title' => _('Group'),
			'members' => _('Members'));
		$view = $page->append('treeview', array('columns' => $columns));
		foreach($res as $r)
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
		$r = $this->getRequest();
		$page->append('link', array('stock' => 'back', 'request' => $r,
				'text' => _('Back to the group menu')));
		return new PageResponse($page);
	}

	private function _listGroup(Engine $engine, Request $request)
	{
		$db = $engine->getDatabase();
		$id = $request->getID();
		$group = $request->getTitle();
		$query = ($group !== FALSE)
			? static::$query_list_group_groupname
			: static::$query_list_group;
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
			return new ErrorResponse($error);
		$columns = array('title' => _('Username'),
			'fullname' => _('Full name'));
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($res as $r)
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
		$r = $this->getRequest('list');
		$vbox->append('link', array('stock' => 'back', 'request' => $r,
				'text' => _('Back to the group list')));
		return new PageResponse($page);
	}


	//GroupModule::callSubmit
	protected function callSubmit(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();
		$error = _('Permission denied');
		$title = _('New group');

		//check permissions
		if($this->canSubmit($engine, $error) === FALSE)
			return new ErrorResponse($error);
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
		return new PageResponse($page);
	}

	protected function _submitProcess(Engine $engine, Request $request,
			&$group)
	{
		//verify the request
		if($request->isIdempotent())
			return TRUE;
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

	protected function _submitSuccess(Engine $engine, Request $request,
			$page, Group $group)
	{
		$r = new Request($this->name, FALSE, $group->getGroupID(),
			$group->getGroupname());
		return $this->helperRedirect($engine, $r, $page);
	}


	//GroupModule::callUpdate
	protected function callUpdate(Engine $engine, Request $request)
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
			return new ErrorResponse($error,
					Response::$CODE_ENOENT);
		}
		if(!$cred->isAdmin())
		{
			$error = _('Permission denied');
			return new ErrorResponse($error, Response::$CODE_EPERM);
		}
		//process update
		if(!$request->isIdempotent())
			$error = $this->_updateProcess($engine, $request,
					$group);
		if($error === FALSE)
			//update was successful
			return $this->_updateSuccess($engine, $request);
		$page = $this->formUpdate($engine, $request, $group, $id,
				$error);
		return new PageResponse($page);
	}

	private function _updateProcess(Engine $engine, Request $request,
			Group $group)
	{
		$ret = '';
		$db = $engine->getDatabase();
		$cred = $engine->getCredentials();

		//verify the request
		if($request->isIdempotent())
			return TRUE;
		if(($groupname = $request->get('groupname')) === FALSE)
			$ret .= _("The group name is required\n");
		if(strlen($ret) > 0)
			return $ret;
		//update the group
		$error = '';
		$args = array('group_id' => $group->getGroupID(),
			'groupname' => $groupname);
		if($db->query($engine, static::$query_update, $args) === FALSE)
			return _('Could not update the group');
		return FALSE;
	}

	private function _updateSuccess(Engine $engine, Request $request)
	{
		$id = $request->getID();

		$title = _('Group update');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$info = _('The group was updated successfully');
		$dialog = $page->append('dialog', array('type' => 'info',
				'text' => $info));
		$r = $this->getRequest('admin');
		$dialog->append('button', array('stock' => 'admin',
				'request' => $r,
				'text' => _('Groups administration')));
		$text = _('Group menu');
		$r = $this->getRequest();
		$dialog->append('button', array('stock' => 'user',
				'request' => $r, 'text' => $text));
		return new PageResponse($page);
	}


	//helpers
	//GroupModule::helperApplyGroup
	protected function helperApplyGroup(Engine $engine, Request $request,
			$action, $fallback, $key = 'group_id')
	{
		$cred = $engine->getCredentials();

		//FIXME use $this->can$action() instead
		if(!$cred->isAdmin())
			//must be admin
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('Permission denied')));
		if($request->isIdempotent())
			//must be safe
			return FALSE;
		$invalid = 0;
		$errors = 0;
		$success = 0;
		$message = '';
		$sep = '';
		if(($ids = $request->get('ids')) === FALSE || !is_array($ids))
			$ids = array();
		foreach($ids as $id)
		{
			$group = new Group($engine, $id);
			if($group->getGroupID() === FALSE) //XXX
				$invalid++;
			else if($group->$action($engine) === FALSE)
				$errors++;
			else
				$success++;
		}
		$type = $errors ? 'error' : ($invalid ? 'warning' : 'info');
		if($errors)
		{
			$message .= "Could not $action $errors group(s)";
			$sep = "\n";
		}
		if($invalid)
		{
			$message .= $sep.$invalid.' '._('invalid group(s)');
			$sep = "\n";
		}
		if($success)
			$message .= $sep."Could $action $success group(s)";
		if($message == '')
			return FALSE;
		return new PageElement('dialog', array('type' => $type,
				'text' => $message));
	}


	//GroupModule::helperRedirect
	protected function helperRedirect(Engine $engine, Request $request,
			PageElement $page, $text = FALSE)
	{
		if($text === FALSE)
			$text = _('Redirection in progress, please wait...');
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


	//private
	//properties
	//queries
	static private $query_admin = 'SELECT daportal_group.group_id AS id,
		groupname, COUNT(user_id) AS count,
		daportal_group.enabled AS enabled
		FROM daportal_group
		LEFT JOIN daportal_user_group
		ON daportal_group.group_id=daportal_user_group.group_id
		GROUP BY daportal_group.group_id';
	static private $query_content = "SELECT name
		FROM daportal_module
		WHERE enabled='1'
		ORDER BY name ASC";
	static private $query_list = "SELECT group_id AS id, groupname
		FROM daportal_group_enabled
		WHERE group_id <> '0'";
	//IN:	group_id
	static private $query_list_group = 'SELECT
		daportal_user_enabled.user_id AS id, username, fullname
		FROM daportal_user_group, daportal_user_enabled
		WHERE daportal_user_group.user_id=daportal_user_enabled.user_id
		AND daportal_user_group.group_id=:group_id
		ORDER BY username ASC';
	//IN:	group_id
	//	groupname
	//FIXME should return an error if the group does not exist
	static private $query_list_group_groupname = 'SELECT
		daportal_user_enabled.user_id AS id, username, fullname
		FROM daportal_group_enabled, daportal_user_group,
		daportal_user_enabled
		WHERE daportal_group_enabled.group_id=daportal_user_group.group_id
		AND daportal_user_group.user_id=daportal_user_enabled.user_id
		AND daportal_user_group.group_id=:group_id
		AND daportal_group_enabled.groupname=:groupname
		ORDER BY username ASC';
	//IN:	user_id
	static private $query_list_members = 'SELECT
		daportal_group_enabled.group_id AS group_id, groupname
		FROM daportal_user_group, daportal_group_enabled
		WHERE daportal_user_group.group_id
		=daportal_group_enabled.group_id
		AND user_id=:user_id';
	//IN:	group_id
	//	groupname
	static private $query_update = 'UPDATE daportal_group
		SET groupname=:groupname
		WHERE group_id=:group_id';
}

?>
