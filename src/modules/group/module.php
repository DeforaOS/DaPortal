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
		switch($action)
		{
			case 'actions':
				return $this->$action($engine, $request);
			case 'admin':
			case 'default':
			case 'display':
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
	protected function canSubmit($engine, &$error)
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
				'value' => $request->getParameter('groupname')));
		//enabled
		$vbox->append('checkbox', array('name' => 'enabled',
				'value' => $request->getParameter('enabled')
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

		if($request->getParameter('user') !== FALSE)
			return FALSE;
		if($request->getParameter('admin'))
			return $this->_actions_admin($engine, $cred,
					$this->name, $ret);
		return FALSE;
	}

	private function _actions_admin($engine, $cred, $module, &$ret)
	{
		if(!$cred->isAdmin())
			return $ret;
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


	//calls
	//GroupModule::callAdmin
	protected function callAdmin($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();

		if(!$cred->isAdmin())
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('Permission denied')));
		if($request === FALSE)
			return $this->_adminUsers($engine, $request);
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
		$db = $engine->getDatabase();
		$query = $this->query_content;
		$cred = $engine->getCredentials();

		if($request !== FALSE && ($id = $request->getID()) !== FALSE)
			return $this->callDisplay($engine, $request);
		$title = ($cred->getUserID() != 0) ? _('My groups')
			: _('Groups');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//obtain the list of modules
		if(($res = $db->query($engine, $query)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => 'Could not list modules'));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox = $vbox->append('vbox');
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$r = new Request($res[$i]['name'], 'actions', FALSE,
					FALSE, array('admin' => 0));
			$rows = $engine->process($r);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$r = new Request($res[$i]['name']);
			$text = ucfirst($res[$i]['name']);
			$link = new PageElement('link', array('request' => $r,
					'text' => $text));
			$title = $vbox->append('title', array(
					'stock' => $res[$i]['name']));
			$title->append($link);
			$view = $vbox->append('iconview');
			foreach($rows as $r)
				$view->append($r);
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
			$title = _('Content from group ').$request->getTitle();
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
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox = $vbox->append('vbox');
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
		{
			$r = new Request($res[$i]['name'], 'actions', FALSE,
				FALSE, array('group' => $group));
			$rows = $engine->process($r);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$text = ucfirst($res[$i]['name']);
			$vbox->append('title', array(
					'stock' => $res[$i]['name'],
					'text' => $text));
			$view = $vbox->append('iconview');
			foreach($rows as $r)
				$view->append($r);
		}
		//buttons
		if($link !== FALSE)
			$page->append($link);
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
		//FIXME really implement
		//form
		$form = $this->formSubmit($engine, $request);
		$page->append($form);
		return $page;
	}


	//GroupModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		$cred = $engine->getCredentials();
		$id = $request->getID();
		$groupname = $request->getTitle();
		$error = TRUE;

		//determine whose profile to update
		if($id === FALSE)
		{
			$id = $cred->getGroupID();
			$groupname = $cred->getGroupname();
		}
		$group = Group::lookup($engine, $groupname, $id);
		if($user === FALSE || ($id = $user->getUserID()) == 0)
		{
			//the anonymous user has no profile
			$error = _('There is no profile for this user');
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		}
		if($id === $cred->getUserID())
			//viewing own profile
			$id = FALSE;
		if($id !== FALSE && !$cred->isAdmin())
		{
			$error = _('Permission denied');
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		}
		//process update
		if(!$request->isIdempotent())
			$error = $this->_updateProcess($engine, $request,
					$user);
		if($error === FALSE)
			//update was successful
			return $this->_updateSuccess($engine, $request);
		return $this->formUpdate($engine, $request, $user, $id,
				$error);
	}

	private function _updateProcess($engine, $request, $group)
	{
		$ret = '';
		$db = $engine->getDatabase();
		$cred = $engine->getCredentials();

		if(($groupname = $request->getParameter('groupname')) === FALSE)
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

		$title = _('Profile update');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$info = $id ? _('The profile was updated successfully')
			: _('Your profile was updated successfully');
		$dialog = $page->append('dialog', array('type' => 'info',
				'text' => $info));
		if($id)
		{
			$r = new Request($this->name, 'admin');
			$dialog->append('button', array('stock' => 'admin',
					'request' => $r,
					'text' => _('Groups administration')));
			$text = _('User profile');
		}
		else
			$text = _('My profile');
		$r = new Request($this->name, 'profile', $id,
			$request->getTitle());
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
			if(count($x) != 2 || $x[0] != 'user_id'
					|| !is_numeric($x[1]))
				continue;
			$args = array('user_id' => $x[1]);
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
	//IN:	group_id
	//	groupname
	private $query_update = 'UPDATE daportal_group
		SET groupname=:groupname
		WHERE group_id=:group_id';
}

?>
