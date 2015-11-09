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



//UserModule
class UserModule extends Module
{
	//public
	//methods
	//essential
	//UserModule::UserModule
	public function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Users') : $title;
		parent::__construct($id, $name, $title);
	}


	//accessors
	//UserModule::getTitle
	public function getTitle(Engine $engine)
	{
		$credentials = $engine->getCredentials();

		return $credentials->getUserID()
			? _('User') : parent::getTitle($engine);
	}


	//useful
	//UserModule::call
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
			case 'close':
			case 'default':
			case 'disable':
			case 'display':
			case 'enable':
			case 'groups':
			case 'list':
			case 'lock':
			case 'login':
			case 'logout':
			case 'profile':
			case 'register':
			case 'reset':
			case 'submit':
			case 'unlock':
			case 'update':
			case 'validate':
			case 'widget':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//protected
	//methods
	//accessors
	//UserModule::canClose
	protected function canClose($engine, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if($cred->isAdmin())
		{
			$error = _('Administrators cannot close themselves');
			return FALSE;
		}
		if($this->configGet('close') != 1)
		{
			$error = _('Closing accounts is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canDelete
	protected function canDelete($engine, $user = FALSE, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Deleting users is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canDisable
	protected function canDisable($engine, $user = FALSE, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Disabling users is not allowed');
			return FALSE;
		}
		if($user !== FALSE && $user->getUserID() == $cred->getUserID())
		{
			$error = _('Disabling oneself is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canEnable
	protected function canEnable($engine, $user = FALSE, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Enabling users is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canLock
	protected function canLock($engine, $user = FALSE, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Locking users is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canRegister
	protected function canRegister($request = FALSE, &$error = FALSE)
	{
		if($this->configGet('register') != 1)
		{
			$error = _('Registering is not allowed');
			return FALSE;
		}
		if($request !== FALSE && $request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canReset
	protected function canReset($request = FALSE, &$error = FALSE)
	{
		if($this->configGet('reset') != 1)
		{
			$error = _('Password resets are not allowed');
			return FALSE;
		}
		if($request !== FALSE && $request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		return TRUE;
	}


	//UserModule::canSubmit
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


	//UserModule::canUnlock
	protected function canUnlock($engine, $user = FALSE, &$error = FALSE)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			$error = _('Unlocking users is not allowed');
			return FALSE;
		}
		return TRUE;
	}


	//forms
	//UserModule::formClose
	protected function formClose($engine)
	{
		$r = $this->getRequest('close');
		$form = new PageElement('form', array('request' => $r));
		$message = _('Do you really want to close your account?');

		//FIXME make it a warning dialog
		$vbox = $form->append('vbox');
		$vbox->append('label', array('text' => $message));
		$form->append('button', array('stock' => 'cancel',
				'target' => '_cancel',
				'text' => _('Cancel'),
				'request' => $this->getRequest('profile')));
		$form->append('button', array('stock' => 'close',
				'type' => 'submit', 'value' => 'submit',
				'text' => _('Close')));
		return $form;
	}


	//UserModule::formLogin
	protected function formLogin($engine, $request, $cancel = TRUE)
	{
		$username = $request->get('username');
		$r = $this->getRequest('login', $request->getParameters());

		$form = new PageElement('form', array('request' => $r));
		$entry = $form->append('entry', array(
					'name' => 'username',
					'text' => _('Username: '),
					'placeholder' => _('Username'),
					'value' => $username));
		$entry = $form->append('entry', array(
					'hidden' => TRUE,
					'name' => 'password',
					'placeholder' => _('Password'),
					'text' => _('Password: ')));
		$r = $this->getRequest();
		if($cancel)
			$form->append('button', array('text' => _('Cancel'),
						'stock' => 'cancel',
						'target' => '_cancel',
						'request' => $r));
		$button = $form->append('button', array('type' => 'submit',
					'stock' => 'login',
					'text' => _('Login')));
		return $form;
	}


	//UserModule::formRegister
	protected function formRegister($engine, $username, $email)
	{
		$r = $this->getRequest('register');
		$form = new PageElement('form', array('request' => $r));
		$form->append('entry', array('text' => _('Username: '),
				'name' => 'username', 'value' => $username));
		$form->append('entry', array('text' => _('e-mail address: '),
				'name' => 'email', 'value' => $email));
		$form->append('button', array('stock' => 'cancel',
				'text' => _('Cancel'),
				'target' => '_cancel',
				'request' => $this->getRequest()));
		$form->append('button', array('stock' => 'register',
				'type' => 'submit', 'text' => _('Register')));
		return $form;
	}


	//UserModule::formReset
	protected function formReset($engine, $username, $email)
	{
		$r = $this->getRequest('reset');
		$form = new PageElement('form', array('request' => $r));
		$form->append('entry', array('text' => _('Username: '),
				'name' => 'username', 'value' => $username));
		$form->append('entry', array('text' => _('e-mail address: '),
				'name' => 'email', 'value' => $email));
		$form->append('button', array('stock' => 'cancel',
				'text' => _('Cancel'),
				'target' => '_cancel',
				'request' => $this->getRequest()));
		$form->append('button', array('stock' => 'reset',
				'type' => 'submit', 'text' => _('Reset')));
		return $form;
	}


	//UserModule::formSubmit
	protected function formSubmit($engine, $request)
	{
		$r = $this->getRequest('submit');
		$form = new PageElement('form', array('request' => $r));
		$vbox = $form->append('vbox');
		$vbox->append('entry', array('name' => 'username',
				'text' => _('Username: '),
				'value' => $request->get('username')));
		$vbox->append('entry', array('name' => 'fullname',
				'text' => _('Full name: '),
				'value' => $request->get('fullname')));
		$vbox->append('entry', array('name' => 'password',
				'hidden' => TRUE,
				'text' => _('Password: '), 'value' => ''));
		$vbox->append('entry', array('name' => 'email',
				'text' => _('e-mail: '),
				'value' => $request->get('email')));
		//groups
		if(($groups = Group::listAll($engine, TRUE)) !== FALSE)
		{
			//primary group
			if(($group_id = $request->get('group_id')) === FALSE)
				$group_id = 0;
			$combobox = $vbox->append('combobox', array(
					'text' => _('Primary group: '),
					'name' => 'group_id',
					'value' => $group_id));
			foreach($groups as $group)
				$combobox->append('label', array(
					'text' => $group->getGroupname(),
					'value' => $group->getGroupID()));
			//secondary groups
			$vbox->append('label', array(
					'text' => _('Secondary groups:')));
			$columns = array('group' => _('Group'),
					'member' => _('Member'));
			$view = $vbox->append('treeview', array(
					'columns' => $columns,
					'alternate' => TRUE));
			foreach($groups as $group)
			{
				$r = new Request('group', 'list',
					$group->getGroupID(),
					$group->getGroupName());
				$link = new PageElement('link', array(
						'stock' => 'group',
						'request' => $request,
						'text' => $group->getGroupName()));
				$checkbox = new PageElement('checkbox', array(
					'name' => 'ids['.$group->getGroupID().']'));
				$row = $view->append('row');
				$row->set('group', $link);
				$row->set('member', $checkbox);
			}
		}
		//enabled
		$vbox->append('checkbox', array('name' => 'enabled',
				'value' => $request->get('enabled')
					? TRUE : FALSE,
				'text' => _('Enabled')));
		//locked
		$vbox->append('checkbox', array('name' => 'locked',
				'value' => $request->get('locked')
					? TRUE : FALSE,
				'text' => _('Locked')));
		//administrator
		$vbox->append('checkbox', array('name' => 'admin',
				'value' => $request->get('admin')
					? TRUE : FALSE,
				'text' => _('Administrator')));
		//buttons
		$r = $this->getRequest('admin');
		$form->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel',
				'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'stock' => 'new', 'name' => 'action',
				'value' => 'submit', 'text' => _('Create')));
		return $form;
	}


	//UserModule::formUpdate
	protected function formUpdate($engine, $request, $user, $id, $error)
	{
		$cred = $engine->getCredentials();

		//output the page
		$title = $id ? _('Profile update for ').$user->getUsername()
			: _('Profile update');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'user',
				'text' => $title));
		if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		$r = new Request($this->name, 'update', $request->getID(),
		       	$request->getID() ? $request->getTitle() : FALSE);
		$form = $page->append('form', array('request' => $r));
		//fields
		//username (cannot be changed)
		$form->append('label', array('text' => _('Username: ')));
		$form->append('label', array('text' => $user->getUsername()));
		//full name
		if(($fullname = $request->get('fullname')) === FALSE)
			$fullname = $user->getFullname();
		$form->append('entry', array('text' => _('Full name: '),
				'name' => 'fullname', 'value' => $fullname));
		//e-mail address
		if(($email = $request->get('email')) === FALSE)
			$email = $user->getEmail();
		$form->append('entry', array('text' => _('e-mail: '),
				'name' => 'email', 'value' => $email));
		//groups
		$vbox = $form->append('vbox');
		if($id && ($groups = Group::listAll($engine, TRUE)) !== FALSE)
		{
			//primary group
			if(($group_id = $request->get('group_id')) === FALSE)
				$group_id = $user->getGroupID();
			$combobox = $vbox->append('combobox', array(
					'text' => _('Primary group: '),
					'name' => 'group_id',
					'value' => $group_id));
			foreach($groups as $group)
				$combobox->append('label', array(
					'text' => $group->getGroupname(),
					'value' => $group->getGroupID()));
			//secondary groups
			$vbox->append('label', array(
					'text' => _('Secondary groups:')));
			$columns = array('group' => _('Group'),
					'member' => _('Member'));
			$view = $vbox->append('treeview', array(
					'columns' => $columns,
					'alternate' => TRUE));
			foreach($groups as $group)
			{
				$r = new Request('group', 'list',
					$group->getGroupID(),
					$group->getGroupName());
				$link = new PageElement('link', array(
						'stock' => 'group',
						'request' => $request,
						'text' => $group->getGroupName()));
				$checkbox = new PageElement('checkbox', array(
					'name' => 'ids['.$group->getGroupID().']'));
				if($user->isMember($engine, $group->getGroupName()))
					$checkbox->set('value', TRUE);
				$row = $view->append('row');
				$row->set('group', $link);
				$row->set('member', $checkbox);
			}
		}
		//password
		$form->append('label', array('text' => _('Optionally: ')));
		if($id === FALSE && !$cred->isAdmin())
			$form->append('entry', array(
				'text' => _('Current password: '),
				'name' => 'password', 'hidden' => TRUE));
		$form->append('entry', array('text' => _('New password: '),
				'name' => 'password1', 'hidden' => TRUE));
		$form->append('entry', array(
				'text' => _('Repeat new password: '),
				'name' => 'password2', 'hidden' => TRUE));
		//buttons
		if($cred->isAdmin() && $request->getID() !== FALSE)
			$r = $this->getRequest('admin');
		else
			$r = ($request->getID() !== FALSE)
				? $user->getRequest($this->name, 'profile')
				: $this->getRequest('profile');
		$form->append('button', array('stock' => 'cancel',
				'request' => $r, 'target' => '_cancel',
				'text' => _('Cancel')));
		$form->append('button', array('stock' => 'update',
				'type' => 'submit', 'text' => _('Update')));
		return $page;
	}


	//useful
	//UserModule::actions
	protected function actions($engine, $request)
	{
		$cred = $engine->getCredentials();
		$list = $this->configGet('list');

		if($request->get('user') !== FALSE
				|| $request->get('group') !== FALSE)
			return FALSE;
		$ret = array();
		if($request->get('admin'))
			return $this->_actions_admin($engine, $cred, $ret);
		if($list == 1)
		{
			$r = $this->getRequest('list');
			$icon = new PageElement('image', array(
					'stock' => 'user'));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('User list')));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'important' => TRUE, 'label' => $link));
		}
		if($cred->getUserID() == 0)
		{
			//not logged in yet
			$r = $this->getRequest('login');
			$icon = new PageElement('image', array(
					'stock' => 'login'));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('Login')));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'important' => TRUE,
					'label' => $link));
			if($this->canReset())
			{
				$r = $this->getRequest('reset');
				$icon = new PageElement('image', array(
						'stock' => 'reset'));
				$link = new PageElement('link', array(
						'request' => $r,
						'text' => _('Password reset')));
				$ret[] = new PageElement('row', array(
						'icon' => $icon,
						'label' => $link));
			}
			if($this->canRegister())
			{
				$r = $this->getRequest('register');
				$icon = new PageElement('image', array(
						'stock' => 'register'));
				$link = new PageElement('link', array(
						'request' => $r,
						'text' => _('Register')));
				$ret[] = new PageElement('row', array(
						'icon' => $icon,
						'label' => $link));
			}
		}
		else
		{
			//already logged in
			if($request->get('admin') != 0)
				$this->_actions_admin($engine, $cred, $ret);
			//user's content
			$r = $this->getRequest('display');
			$icon = new PageElement('image', array(
					'stock' => 'user'));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('My content')));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'label' => $link));
			//user's groups
			$r = $this->getRequest('groups');
			$icon = new PageElement('image', array(
					'stock' => 'user'));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('My groups')));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'label' => $link));
			//user's profile
			$r = $this->getRequest('profile');
			$icon = new PageElement('image', array(
					'stock' => 'user'));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('My profile')));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'label' => $link));
			//logout
			$r = $this->getRequest('logout');
			$icon = new PageElement('image', array(
					'stock' => 'logout'));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('Logout')));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'important' => TRUE,
					'label' => $link));
		}
		return $ret;
	}

	private function _actions_admin($engine, $cred, &$ret)
	{
		if(!$cred->isAdmin())
			return $ret;
		//user creation
		$r = $this->getRequest('submit');
		$icon = new PageElement('image', array('stock' => 'new'));
		$link = new PageElement('link', array('request' => $r,
				'text' => _('New user')));
		$ret[] = new PageElement('row', array('icon' => $icon,
				'label' => $link));
		//administration
		$r = $this->getRequest('admin');
		$icon = new PageElement('image', array('stock' => 'admin'));
		$link = new PageElement('link', array('request' => $r,
				'text' => _('Users administration')));
		$ret[] = new PageElement('row', array('icon' => $icon,
				'label' => $link));
		$r = $this->getRequest('admin', array('type' => 'group'));
		return $ret;
	}


	//calls
	//UserModule::callAdmin
	protected function callAdmin($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$actions = array('disable' => _('Disable'),
			'enable' => _('Enable'), 'lock' => _('Lock'),
			'unlock' => _('Unlock'), 'delete' => _('Delete'));
		$dialog = FALSE;

		if(!$cred->isAdmin())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		//perform actions if necessary
		if($request !== FALSE)
			foreach($actions as $a => $t)
				if($request->get($a) !== FALSE)
				{
					$a = '_admin'.$a;
					$dialog = $this->$a($engine, $request);
					break;
				}
		//list users
		$title = _('Users administration');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if($dialog !== FALSE)
			$page->append($dialog);
		$query = static::$query_admin;
		//FIXME implement sorting
		$query .= ' ORDER BY username ASC';
		if(($res = $db->query($engine, $query)) === FALSE)
			return new ErrorResponse(_('Could not list users'));
		$columns = array('username' => _('Username'),
				'group' => _('Group'),
				'enabled' => _('Enabled'),
				'locked' => _('Locked'),
				'admin' => _('Administrator'),
				'email' => _('e-mail'));
		$r = $this->getRequest('admin');
		$view = $page->append('treeview', array('request' => $r,
				'view' => 'details', 'columns' => $columns));
		//toolbar
		$toolbar = $view->append('toolbar');
		$toolbar->append('button', array('stock' => 'new',
				'text' => _('New user'),
				'request' => $this->getRequest('submit')));
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $r));
		foreach($actions as $value => $text)
			$toolbar->append('button', array('stock' => $value,
					'text' => $text, 'type' => 'submit',
					'name' => 'action', 'value' => $value));
		$no = new PageElement('image', array('stock' => 'no',
				'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
				'size' => 16, 'title' => _('Enabled')));
		$locked = new PageElement('image', array('stock' => 'no',
				'size' => 16, 'title' => _('Locked')));
		$unlocked = new PageElement('image', array('stock' => 'yes',
				'size' => 16, 'title' => _('Unlocked')));
		foreach($res as $r)
		{
			$row = $view->append('row');
			$row->set('id', 'ids['.$r['id'].']');
			$row->set('username', $r['username']);
			$request = new Request($this->name, 'update', $r['id'],
				$r['username']);
			$link = new PageElement('link', array('stock' => 'user',
					'request' => $request,
					'text' => $r['username']));
			if($r['id'] != 0 && $db->isTrue($r['enabled']))
				$row->set('username', $link);
			$row->set('group', $r['groupname']);
			$row->set('enabled', $db->isTrue($r['enabled'])
				? $yes : $no);
			$row->set('locked', ($r['locked'] == '!')
				? $locked : $unlocked);
			$row->set('admin', $db->isTrue($r['admin'])
				? $yes : $no);
			$link = new PageElement('link', array(
					'url' => 'mailto:'.$r['email'],
					'text' => $r['email']));
			$row->set('email', $link);
		}
		$vbox = $page->append('vbox');
		$request = $this->getRequest();
		$vbox->append('link', array('request' => $request,
			'stock' => 'user',
			'text' => _('Back to my account')));
		$request = new Request('admin');
		$vbox->append('link', array('request' => $request,
			'stock' => 'admin',
			'text' => _('Back to the administration')));
		return new PageResponse($page);
	}

	protected function _adminDelete($engine, $request)
	{
		return $this->helperApplyUser($engine, $request, 'delete',
				$this->getRequest('admin'));
	}

	protected function _adminDisable($engine, $request)
	{
		return $this->helperApplyUser($engine, $request, 'disable',
				$this->getRequest('admin'));
	}

	protected function _adminEnable($engine, $request)
	{
		return $this->helperApplyUser($engine, $request, 'enable',
				$this->getRequest('admin'));
	}

	protected function _adminLock($engine, $request)
	{
		return $this->helperApplyUser($engine, $request, 'lock',
				$this->getRequest('admin'));
	}

	protected function _adminUnlock($engine, $request)
	{
		return $this->helperApplyUser($engine, $request, 'unlock',
				$this->getRequest('admin'));
	}


	//UserModule::callClose
	protected function callClose($engine, $request)
	{
		$cred = $engine->getCredentials();
		$error = TRUE;

		if($cred->getUserID() == 0)
			//must be logged in
			return $this->callDefault($engine, $request);
		$error = _('Unknown error');
		if(!$this->canClose($engine, $error))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		//process the request
		if(($error = $this->_closeProcess($engine, $request)) === FALSE)
			//closing was successful
			return $this->_closeSuccess($engine, $request);
		return $this->_closeForm($engine, $request, $error);
	}

	private function _closeForm($engine, $request, $error)
	{
		$title = _('Close your account');
		$page = new Page(array('title' => $title));
		$code = Response::$CODE_SUCCESS;

		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if(is_string($error))
		{
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			$code = Response::$CODE_EUNKNOWN;
		}
		$form = $this->formClose($engine);
		$page->append($form);
		return new PageResponse($page, $code);
	}

	private function _closeProcess($engine, $request)
	{
		$cred = $engine->getCredentials();
		$uid = $cred->getUserID();
		$username = $cred->getUsername();

		//verify the request
		if($request === FALSE || $request->isIdempotent())
			return TRUE;
		//disable the user
		if(($user = User::lookup($engine, $username, $uid)) === FALSE
				|| $user->disable($engine) !== TRUE)
			return _('The account could not be closed');
		//log the user out
		$this->helperLogout($engine, $username, TRUE);
		//no error
		return FALSE;
	}

	protected function _closeSuccess($engine, $request)
	{
		$title = _('Account closed');
		$text = _('Your account was closed successfully.');

		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('label', array('text' => $text));
		$vbox->append('link', array('stock' => 'home',
				'text' => _('Back to the homepage'),
				'request' => new Request()));
		return new PageResponse($page);
	}


	//UserModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$db = $engine->getDatabase();
		$query = static::$query_content;
		$cred = $engine->getCredentials();

		if($request !== FALSE && ($id = $request->getID()) !== FALSE)
			return $this->callDisplay($engine, $request);
		//FIXME add content?
		$title = ($cred->getUserID() != 0) ? _('My account')
			: _('Site menu');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//obtain the list of modules
		if(($res = $db->query($engine, $query)) === FALSE)
			return new ErrorResponse(_('Could not list modules'));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox = $vbox->append('vbox');
		foreach($res as $r)
		{
			$request = new Request($r['name'], 'actions',
				FALSE, FALSE, array('admin' => 0));
			$rows = $engine->process($request, TRUE);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$request = new Request($r['name']);
			$text = ucfirst($r['name']);
			$link = new PageElement('link', array(
					'request' => $request,
					'text' => $text));
			$title = $vbox->append('title', array(
					'stock' => $r['name']));
			$title->append($link);
			$view = $vbox->append('iconview');
			foreach($rows as $row)
				$view->append($row);
		}
		$request = new Request();
		$page->append('link', array('stock' => 'home',
				'request' => $request,
				'text' => _('Back to the homepage')));
		return new PageResponse($page);
	}


	//UserModule::callDisable
	protected function callDisable($engine, $request)
	{
		$error = _('Unknown error');

		if($request->isIdempotent())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		if(!$this->canDisable($engine, FALSE, $error))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		if(($user = User::lookup($engine, $request->getTitle(),
					$request->getID())) === FALSE)
			return new ErrorResponse(_('Could not load user'),
					Response::$CODE_ENOENT);
		if(!$this->canDisable($engine, $user, $error))
			return new ErrorResponse($user->getUsername()
					.': '.$error, Response::$CODE_EPERM);
		if(!$user->disable($engine, $error))
			return new ErrorResponse($error);
		return new StringResponse(_('User disabled successfully'));
	}


	//UserModule::callDisplay
	protected function callDisplay($engine, $request)
	{
		$database = $engine->getDatabase();
		$query = static::$query_content;
		$cred = $engine->getCredentials();
		$link = FALSE;

		//obtain the list of modules
		if(($res = $database->query($engine, $query)) === FALSE)
			return new ErrorResponse(_('Could not list modules'));
		if(($uid = $request->getID()) !== FALSE)
		{
			$user = User::lookup($engine, $request->getTitle(),
					$uid);
			$title = _('Content from ').$request->getTitle();
		}
		else if(($uid = $cred->getUserID()) != 0)
		{
			$user = User::lookup($engine, $cred->getUsername(),
					$uid);
			$title = _('My content');
			$r = $this->getRequest();
			$link = new PageElement('link', array('stock' => 'user',
					'request' => $r,
					'text' => _('Back to my account')));
		}
		if($user === FALSE || $user->getUserID() == 0)
			return $this->callLogin($engine, new Request);
		$page = new Page(array('title' => $title));
		//title
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox2 = $vbox->append('vbox');
		foreach($res as $r)
		{
			$request = new Request($r['name'], 'actions', FALSE,
				FALSE, array('user' => $user));
			$rows = $engine->process($request, TRUE);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$text = ucfirst($r['name']);
			$vbox2->append('title', array(
					'stock' => $r['name'],
					'text' => $text));
			$view = $vbox2->append('iconview');
			foreach($rows as $row)
				$view->append($row);
		}
		//buttons
		if($link !== FALSE)
			$vbox->append($link);
		if($this->configGet('list'))
		{
			$request = $this->getRequest('list');
			$vbox->append('link', array('request' => $request,
					'stock' => 'back',
					'text' => _('Back to the user list')));
		}
		return new PageResponse($page);
	}


	//UserModule::callEnable
	protected function callEnable($engine, $request)
	{
		$error = _('Unknown error');

		if($request->isIdempotent())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		if(!$this->canEnable($engine, FALSE, $error))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		if(($user = User::lookup($engine, $request->getTitle(),
					$request->getID(), FALSE)) === FALSE)
			return new ErrorResponse(_('Could not load user'),
					Response::$CODE_ENOENT);
		if(!$this->canEnable($engine, $user, $error))
			return new ErrorResponse($user->getUsername()
					.': '.$error, Response::$CODE_EPERM);
		if(!$user->enable($engine, $error))
			return new ErrorResponse($error);
		return new StringResponse(_('User enabled successfully'));
	}


	//UserModule::callGroups
	protected function callGroups($engine, $request)
	{
		$database = $engine->getDatabase();
		$query = static::$query_groups_user;
		$cred = $engine->getCredentials();
		$id = $request->getID();

		//determine whose groups to view
		if($id === FALSE)
			$user = User::lookup($engine, $cred->getUsername(),
					$cred->getUserID());
		else
			$user = User::lookup($engine, $request->getTitle(),
					$id);
		if($user === FALSE || ($id = $user->getUserID()) == 0)
			//the anonymous user has no memberships
			return new ErrorResponse(
				_('There are no groups for this user'),
				Response::$CODE_ENOENT);
		if($id === $cred->getUserID())
			//viewing own profile
			$id = FALSE;
		//output the page
		$title = $id ? _('Groups for ').$user->getUsername()
			: _('My groups');
		$page = new Page(array('title' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('title', array('stock' => 'user',
				'text' => $title));
		$args = array('user_id' => $user->getUserID());
		if(($res = $database->query($engine, $query, $args)) === FALSE)
		{
			$error = _('Could not list the groups');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		$columns = array('title' => _('Group'),
			'count' => _('Members'));
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($res as $group)
		{
			$r = new Request('group', FALSE, $group['id'],
				$group['groupname']);
			$group['title'] = new PageElement('link', array(
				'request' => $r, 'stock' => 'group',
				'text' => $group['groupname']));
			$r = new Request('group', 'list', $group['id'],
				$group['groupname']);
			$group['count'] = new PageElement('link', array(
				'request' => $r, 'stock' => 'group',
				'text' => $group['count']));
			$view->append('row', $group);
		}
		if($id === FALSE)
		{
			$r = $this->getRequest();
			$vbox->append('link', array('stock' => 'user',
					'request' => $r,
					'text' => _('Back to my account')));
		}
		return new PageResponse($page);
	}


	//UserModule::callList
	protected function callList($engine, $request)
	{
		$list = $this->configGet('list');
		$cred = $engine->getCredentials();
		$title = _('User list');

		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'login',
				'text' => $title));
		$vbox = $page->append('vbox');
		if($list != 1)
		{
			$error = _('Permission denied');
			$vbox->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page, Response::$CODE_EPERM);
		}
		if(($users = User::listAll($engine, TRUE)) === FALSE)
		{
			$error = _('Could not list the users');
			$vbox->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		$columns = array('title' => 'Username',
			'fullname' => 'Full name');
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($users as $user)
		{
			$r = $user->getRequest($this->name);
			$link = new PageElement('link', array('request' => $r,
				'stock' => 'user',
				'text' => $user->getUsername()));
			$r = array('title' => $link,
				'fullname' => $user->getFullname());
			$view->append('row', $r);
		}
		//buttons
		$r = $this->getRequest();
		$text = $cred->getUserID() ? _('Back to my account')
			: _('Back to the site menu');
		$vbox->append('link', array('request' => $r, 'stock' => 'user',
				'text' => $text));
		$r = new Request();
		$vbox->append('link', array('request' => $r, 'stock' => 'home',
				'text' => _('Back to the homepage')));
		return new PageResponse($page);
	}


	//UserModule::callLock
	protected function callLock($engine, $request)
	{
		$error = _('Unknown error');

		if($request->isIdempotent())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		if(($user = User::lookup($engine, $request->getTitle(),
					$request->getID())) === FALSE)
			return new ErrorResponse(_('Could not load user'),
					Response::$CODE_ENOENT);
		if(!$this->canLock($engine, $user, $error))
			return new ErrorResponse($user->getUsername()
					.': '.$error, Response::$CODE_EPERM);
		if(!$user->lock($engine, $error))
			return new ErrorResponse($error);
		return new StringResponse(_('User locked successfully'));
	}


	//UserModule::callLogin
	protected function callLogin($engine, $request)
	{
		$cred = $engine->getCredentials();
		$title = _('User login');
		$already = _('You are already logged in');
		$forgot = _('I forgot my password...');
		$register = _('Register an account...');
		$code = Response::$CODE_SUCCESS;

		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'login',
				'text' => $title));
		//process login
		$error = $this->_loginProcess($engine, $request);
		//login successful
		if($error === FALSE)
			return $this->_loginSuccess($engine, $request, $page);
		else if(is_string($error))
		{
			$page->append('dialog', array('type' => 'error',
						'text' => $error));
			$code = Response::$CODE_EPERM;
		}
		else if($cred->getUserID() != 0)
			$page->append('dialog', array('type' => 'info',
						'text' => $already));
		$form = $this->formLogin($engine, $request);
		$page->append($form);
		if($this->canReset())
		{
			$r = $this->getRequest('reset');
			$page->append('link', array('request' => $r,
					'stock' => 'reset',
					'text' => $forgot));
		}
		if($this->canRegister())
		{
			$r = $this->getRequest('register');
			$page->append('link', array('request' => $r,
					'stock' => 'register',
					'text' => $register));
		}
		return new PageResponse($page, $code);
	}

	protected function _loginProcess($engine, $request)
	{
		$db = $engine->getDatabase();

		if($request === FALSE || $request->isIdempotent())
			//no real login attempt
			return TRUE;
		if(($username = $request->get('username')) === FALSE
				|| strlen($username) == 0
				|| ($password = $request->get('password'))
					=== FALSE)
			return _('The username and password must be set');
		if($this->helperLogin($engine, $username, $password) === FALSE)
			return _('Invalid username or password');
		return FALSE;
	}

	protected function _loginSuccess($engine, $request, $page)
	{
		$parameters = $request->getParameters();
		$p = array();

		if(is_array($parameters))
			//sanitize the parameters
			foreach($parameters as $n => $v)
				switch($n)
				{
					case 'module':
					case 'action':
					case 'id':
					case 'title':
					case 'username':
					case 'password':
						break;
					default:
						if(substr($n, 0, 1) == '_')
							break;
						$p[$n] = $v;
						break;
				}
		$r = new Request($request->get('module'),
			$request->get('action'), $request->get('id'),
			$request->get('title'), $p);
		$page->set('location', $engine->getURL($r));
		$page->set('refresh', 30);
		$box = $page->append('vbox');
		$text = _('Authentication in progress, please wait...');
		$box->append('label', array('text' => $text));
		$box = $box->append('hbox');
		$text = _('If you are not redirected within 30 seconds, please ');
		$box->append('label', array('text' => $text));
		$box->append('link', array('text' => _('click here'),
			'request' => $r));
		$box->append('label', array('text' => '.'));
		return new PageResponse($page);
	}


	//UserModule::callLogout
	protected function callLogout($engine, $request)
	{
		$title = _('User logout');
		$cred = $engine->getCredentials();

		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'logout',
				'text' => $title));
		if($cred->getUserID() == 0)
		{
			$text = _('You were logged out successfully');
			$page->append('dialog', array('type' => 'info',
						'text' => $text));
			$r = new Request();
			$page->append('link', array('stock' => 'home',
					'request' => $r,
					'text' => _('Back to the homepage')));
			return new PageResponse($page);
		}
		$r = $this->getRequest('logout');
		if($request->isIdempotent())
		{
			//FIXME make it a question dialog
			$form = $page->append('form', array(
						'request' => $r));
			$vbox = $form->append('vbox');
			$vbox->append('label', array(
				'text' => _('Do you really want to logout?')));
			$r = $this->getRequest();
			$form->append('button', array('text' => _('Cancel'),
						'stock' => 'cancel',
						'target' => '_cancel',
						'request' => $r));
			$form->append('button', array('text' => _('Logout'),
						'stock' => 'logout',
						'type' => 'submit'));
			return new PageResponse($page);
		}
		//process logout
		$page->set('location', $engine->getURL($r));
		$page->set('refresh', 30);
		$box = $page->append('vbox');
		$text = _('Logging out, please wait...');
		$box->append('label', array('text' => $text));
		$box = $box->append('hbox');
		$text = _('If you are not redirected within 30 seconds, please ');
		$box->append('label', array('text' => $text));
		$box->append('link', array('text' => _('click here'),
					'request' => $r));
		$box->append('label', array('text' => '.'));
		$this->helperLogout($engine, $cred->getUsername());
		return new PageResponse($page);
	}


	//UserModule::callProfile
	protected function callProfile($engine, $request)
	{
		$cred = $engine->getCredentials();
		$id = $request->getID();
		$title = $request->getTitle();

		//determine whose profile to view
		if($id === FALSE)
			$user = User::lookup($engine, $cred->getUsername(),
					$cred->getUserID());
		else
			$user = User::lookup($engine, $title, $id);
		if($user === FALSE || ($id = $user->getUserID()) == 0)
			//the anonymous user has no profile
			return new ErrorResponse(
				_('There is no profile for this user'),
				Response::$CODE_ENOENT);
		if($id === $cred->getUserID())
			//viewing own profile
			$id = FALSE;
		//output the page
		$title = $id ? _('Profile for ').$user->getUsername()
			: _('My profile');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'user',
				'text' => $title));
		$vbox = $page->append('vbox');
		$hbox = $vbox->append('hbox');
		$col1 = $hbox->append('vbox');
		$col2 = $hbox->append('vbox');
		$col1->append('label', array('class' => 'bold',
				'text' => _('Fullname: ')));
		$col2->append('label', array('text' => $user->getFullname()));
		$col1->append('label', array('class' => 'bold',
				'text' => _('e-mail: ')));
		$col2->append('label', array('text' => $user->getEmail()));
		//link to profile update
		$r = $user->getRequest($this->name, 'update');
		$button = FALSE;
		if($request->getID() !== FALSE && $cred->isAdmin())
			$button = new PageElement('button', array(
				'stock' => 'admin', 'request' => $r,
				'text' => _('Update')));
		else if($id === FALSE)
			$button = new PageElement('button', array(
				'stock' => 'user', 'request' => $r,
				'text' => _('Update')));
		if($button !== FALSE)
			$vbox->append($button);
		if($id === FALSE)
		{
			$r = $this->getRequest();
			$vbox->append('link', array('stock' => 'user',
					'request' => $r,
					'text' => _('Back to my account')));
			if($this->canClose($engine))
			{
				$r = $this->getRequest('close');
				$vbox->append('link', array('stock' => 'close',
						'request' => $r,
						'text' => _('Close my account')));
			}
		}
		return new PageResponse($page);
	}


	//UserModule::callRegister
	protected function callRegister($engine, $request)
	{
		$cred = $engine->getCredentials();
		$error = TRUE;

		if($cred->getUserID() != 0)
			//already registered and logged in
			return $this->callDisplay($engine, new Request);
		//process registration
		if($this->canRegister($request, $error)
				&& $this->_registerProcess($engine, $request,
					$error))
			$error = FALSE;
		if($error === FALSE)
			//registration was successful
			return $this->_registerSuccess($engine, $request);
		return $this->_registerForm($engine, $request, $error);
	}

	private function _registerForm($engine, $request, $error)
	{
		$title = _('User registration');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if(is_string($error))
			$page->append('dialog', array('type' => 'error',
				'text' => $error));
		$username = $request->get('username');
		$email = $request->get('email');
		$form = $this->formRegister($engine, $username, $email);
		$page->append($form);
		return new PageResponse($page);
	}

	private function _registerProcess($engine, $request, &$error)
	{
		$error = '';

		if(($username = $request->get('username')) === FALSE)
			$error .= _("A username is required\n");
		if(($email = $request->get('email')) === FALSE)
			$error .= _("An e-mail address is required\n");
		if(strlen($error) > 0)
			return $ret;
		//register the user
		if(($user = User::register($engine, $this->name, $username,
				FALSE, $email, FALSE, $error)) === FALSE)
			return FALSE;
		return TRUE;
	}

	private function _registerSuccess($engine, $request)
	{
		$title = _('User registration');
		$text = _("You should receive an e-mail shortly with your password, along with a confirmation key.\n
 Thank you for registering!");
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$page->append('dialog', array('type' => 'info',
				'text' => $text));
		$page->append('link', array('stock' => 'home',
				'text' => _('Back to the homepage'),
				'request' => new Request()));
		return new PageResponse($page);
	}


	//UserModule::callReset
	protected function callReset($engine, $request)
	{
		$cred = $engine->getCredentials();
		$error = TRUE;

		if($cred->getUserID() != 0)
			//already registered and logged in
			return $this->callDisplay($engine, new Request);
		if(($uid = $request->getID()) !== FALSE
				&& ($token = $request->get('token')) !== FALSE)
			return $this->_resetToken($engine, $request, $uid,
					$token);
		//process reset
		if(!$request->isIdempotent()
				&& $this->canReset($request, $error)
				&& $this->_resetProcess($engine, $request,
					$error))
			$error = FALSE;
		if($error === FALSE)
			//reset was successful
			return $this->_resetSuccess($engine, $request);
		return $this->_resetForm($engine, $request, $error);
	}

	private function _resetForm($engine, $request, $error)
	{
		$title = _('Password reset');
		$page = new Page(array('title' => $title));

		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if(is_string($error))
			$page->append('dialog', array('type' => 'error',
				'text' => $error));
		$username = $request->get('username');
		$email = $request->get('email');
		$form = $this->formReset($engine, $username, $email);
		$page->append($form);
		return new PageResponse($page);
	}

	private function _resetProcess($engine, $request, &$error)
	{
		$error = '';

		if(($username = $request->get('username')) === FALSE)
			$error .= _("Your username is required\n");
		if(($email = $request->get('email')) === FALSE)
			$error .= _("Your e-mail address is required\n");
		if(strlen($error) > 0)
			return FALSE;
		//send a reset token to the user
		if(($user = User::reset($engine, $this->name, $username, $email,
				$error)) === FALSE)
			return FALSE;
		return TRUE;
	}

	private function _resetSuccess($engine, $request)
	{
		$title = _('Password reset');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$page->append('dialog', array('type' => 'info',
				'text' => _("You should receive an e-mail shortly, with a link allowing you to reset your password.\n")));
		$page->append('link', array('stock' => 'home',
				'text' => _('Back to the homepage'),
				'request' => new Request()));
		return new PageResponse($page);
	}

	private function _resetToken($engine, $request, $uid, $token)
	{
		$error = TRUE;

		//process reset
		if(!$this->canReset())
			$error = _('Password resets are not allowed');
		else if(!$request->isIdempotent())
			$error = $this->_resetTokenProcess($engine, $request,
					$uid, $token);
		if($error === FALSE)
			//reset was successful
			return $this->_resetTokenSuccess($engine, $request);
		return $this->_resetTokenForm($engine, $request, $uid, $token,
				$error);
	}

	private function _resetTokenForm($engine, $request, $uid, $token,
			$error)
	{
		$title = _('Password reset');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		if(is_string($error))
			$page->append('dialog', array('type' => 'error',
				'text' => $error));
		$r = new Request($this->name, 'reset', $uid, FALSE,
			array('token' => $token));
		$form = $page->append('form', array('request' => $r));
		$form->append('entry', array('text' => _('Password: '),
			'name' => 'password', 'hidden' => TRUE));
		$form->append('entry', array('text' => _('Repeat password: '),
			'name' => 'password2', 'hidden' => TRUE));
		$form->append('button', array('stock' => 'cancel',
			'text' => _('Cancel'),
			'request' => $this->getRequest()));
		$form->append('button', array('stock' => 'reset',
			'type' => 'submit', 'text' => _('Reset')));
		return new PageResponse($page);
	}

	private function _resetTokenProcess($engine, $request, $uid, $token)
	{
		$ret = '';

		if(($password = $request->get('password')) === FALSE)
			$ret .= _('A new password is required');
		else if(($password2 = $request->get('password2')) === FALSE
					|| $password !== $password2)
			$ret .= _('The passwords did not match');
		if(strlen($ret) > 0)
			return $ret;
		//reset the password
		$error = '';
		if(User::resetPassword($engine, $uid, $password, $token, $error)
				=== FALSE)
			$ret .= $error;
		return strlen($ret) ? $ret : FALSE;
	}

	private function _resetTokenSuccess($engine, $request)
	{
		$title = _('Password reset');
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$page->append('dialog', array('type' => 'info',
				'text' => _("Your password was reset successfully.\n")));
		$page->append('link', array('stock' => 'home',
				'text' => _('Back to the homepage'),
				'request' => new Request()));
		$page->append('link', array('stock' => 'login',
				'text' => _('Proceed to login page'),
				'request' => $this->getRequest('login')));
		return new PageResponse($page);
	}


	//UserModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		$cred = $engine->getCredentials();
		$title = _('New user');

		//check permissions
		$error = _('Permission denied');
		if($this->canSubmit($engine, $error) === FALSE)
			return new PageResponse($error, Response::$CODE_EPERM);
		//create the page
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//process the request
		$user = FALSE;
		if(($error = $this->_submitProcess($engine, $request, $user))
				=== FALSE)
			return $this->_submitSuccess($engine, $request, $page,
					$user);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//form
		$form = $this->formSubmit($engine, $request);
		$page->append($form);
		return new PageResponse($page);
	}

	protected function _submitProcess($engine, $request, &$user)
	{
		//verify the request
		if($request === FALSE
				|| $request->get('submit') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
		if(($username = $request->get('username')) === FALSE)
			return _('Invalid arguments');
		$enabled = $request->get('enabled') ? TRUE : FALSE;
		$locked = $request->get('locked') ? TRUE : FALSE;
		$admin = $request->get('admin') ? TRUE : FALSE;
		//create the user
		$error = FALSE;
		$user = User::insert($engine, $username,
				$request->get('group_id'),
				$request->get('fullname'),
				$request->get('password'),
				$request->get('email'),
				$enabled, $locked, $admin, $error);
		if($user === FALSE)
			return $error;
		//set the group memberships
		if(($ids = $request->get('ids')) !== FALSE && is_array($ids))
			foreach($ids as $id)
				$user->addGroup($engine, $id);
		//no error
		return FALSE;
	}

	protected function _submitSuccess($engine, $request, $page, $user)
	{
		$r = $user->isEnabled()
			? $user->getRequest($this->name)
			: $this->getRequest('admin');
		return $this->helperRedirect($engine, $r, $page);
	}


	//UserModule::callUnlock
	protected function callUnlock($engine, $request)
	{
		$error = _('Unknown error');

		if($request->isIdempotent())
			return new ErrorResponse(_('Permission denied'),
					Response::$CODE_EPERM);
		if(($user = User::lookup($engine, $request->getTitle(),
					$request->getID())) === FALSE)
			return new ErrorResponse(_('Could not load user'),
					Response::$CODE_ENOENT);
		if(!$this->canUnlock($engine, $user, $error))
			return new ErrorResponse($user->getUsername()
					.': '.$error, Response::$CODE_EPERM);
		if(!$user->unlock($engine, $error))
			return new ErrorResponse($error);
		return new StringResponse(_('User unlocked successfully'));
	}


	//UserModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		$cred = $engine->getCredentials();
		$id = $request->getID();
		$title = $request->getTitle();
		$error = TRUE;

		//determine whose profile to update
		if($id === FALSE)
			$user = User::lookup($engine, $cred->getUsername(),
					$cred->getUserID());
		else
			$user = User::lookup($engine, $title, $id);
		if($user === FALSE || ($id = $user->getUserID()) == 0)
		{
			//the anonymous user has no profile
			$error = _('There is no profile for this user');
			return new ErrorResponse($error,
				Response::$CODE_ENOENT);
		}
		if($id === $cred->getUserID())
			//viewing own profile
			$id = FALSE;
		if($id !== FALSE && !$cred->isAdmin())
			return new ErrorResponse(_('Permission denied'),
				Response::$CODE_EPERM);
		//process update
		if(!$request->isIdempotent())
			$error = $this->_updateProcess($engine, $request,
					$user);
		if($error === FALSE)
			//update was successful
			return $this->_updateSuccess($engine, $request);
		$page = $this->formUpdate($engine, $request, $user, $id,
				$error);
		return new PageResponse($page);
	}

	private function _updateProcess($engine, $request, $user)
	{
		$ret = '';
		$db = $engine->getDatabase();
		$cred = $engine->getCredentials();

		if(($fullname = $request->get('fullname')) === FALSE)
			$ret .= _("The full name is required\n");
		if(($email = $request->get('email')) === FALSE)
			$ret .= _("The e-mail address is required\n");
		if(strlen($ret) > 0)
			return $ret;
		//update the profile
		$error = '';
		$args = array('user_id' => $user->getUserID(),
			'fullname' => $fullname, 'email' => $email);
		if($db->query($engine, static::$query_update, $args) === FALSE)
			return _('Could not update the profile');

		//update the group if authorized, set and changed
		if($cred->isAdmin()
				&& ($group_id = $request->get('group_id'))
				!== FALSE
				&& $group_id != $user->getGroupID())
			$user->setGroup($engine, $group_id);

		//update the group memberships
		$db->inTransaction($engine, function()
			use ($engine, $request, $user)
		{
			$user->removeGroups($engine);
			if(($ids = $request->get('ids')) !== FALSE
					&& is_array($ids))
				foreach($ids as $id)
					$user->addGroup($engine, $id);
		});

		//update the password if requested
		if(($password1 = $request->get('password1')) === FALSE
				|| strlen($password1) == 0
				|| ($password2 = $request->get('password2'))
					=== FALSE
				|| strlen($password2) == 0)
			return FALSE;
		//check the current password (if not an admin)
		if(!$cred->isAdmin())
		{
			$error = _('The current password must be specified');
			if(($password = $request->get('password')) === FALSE
					|| strlen($password) == 0)
				return $error;
			if($user->authenticate($engine, $password) === FALSE)
				return $error;
		}
		//verify that the new password matches
		if($password1 != $password2)
			return _('The new password does not match');
		if(!$user->setPassword($engine, $password1))
			return _('Could not set the new password');
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
			$r = $this->getRequest('admin');
			$dialog->append('button', array('stock' => 'admin',
					'request' => $r,
					'text' => _('User administration')));
			$text = _('User profile');
		}
		else
			$text = _('My profile');
		$r = new Request($this->name, 'profile', $id,
			$request->getTitle());
		$dialog->append('button', array('stock' => 'user',
				'request' => $r, 'text' => $text));
		return new PageResponse($page);
	}


	//UserModule::callValidate
	protected function callValidate($engine, $request)
	{
		$cred = $engine->getCredentials();
		$error = TRUE;
		$uid = $request->getID();
		$token = $request->get('token');

		if($cred->getUserID() != 0)
			//already registered and logged in
			return $this->callDisplay($engine, new Request);
		$page = new Page(array('title' => _('Account confirmation')));
		$page->append('title', array('stock' => $this->name,
				'text' => _('Account confirmation')));
		if(!$this->canRegister())
		{
			$page->append('dialog', array('type' => 'error',
				'text' => _('Registering is not allowed')));
			return new PageResponse($page, Response::$CODE_EPERM);
		}
		$box = $page->append('vbox');
		if(($user = User::validate($engine, $uid, $token, $error))
				=== FALSE)
			$box->append('dialog', array('type' => 'error',
					'text' => $error));
		else
		{
			$box->append('dialog', array('type' => 'info',
					'title' => _('Congratulations!'),
					'text' => _('Your account is now enabled.')));
			$r = $this->getRequest();
			$box->append('link', array('stock' => 'login',
					'request' => $r,
					'text' => _('Login')));
		}
		$r = new Request();
		$box->append('link', array('stock' => 'home', 'request' => $r,
			'text' => _('Back to the homepage')));
		return new PageResponse($page);
	}


	//UserModule::callWidget
	protected function callWidget($engine, $request)
	{
		$cred = $engine->getCredentials();

		if($cred->getUserID() == 0)
			return $this->_widgetLogin($engine, $request);
		$box = new PageElement('vbox');
		$r = $this->getRequest();
		$box->append('button', array('stock' => 'home',
				'request' => $r, 'text' => _('My account')));
		$r = $this->getRequest('display');
		$box->append('button', array('stock' => 'user',
				'request' => $r, 'text' => _('My content')));
		$r = $this->getRequest('update');
		$box->append('button', array('stock' => 'user',
				'request' => $r, 'text' => _('My profile')));
		$r = $this->getRequest('logout');
		$box->append('button', array('stock' => 'logout',
				'request' => $r, 'text' => _('Logout')));
		return new PageResponse($box);
	}

	protected function _widgetLogin($engine, $request)
	{
		$parameters = $request->getParameters();
		$request = $engine->getRequest();

		$parameters['module'] = $request->getModule();
		$parameters['action'] = $request->getAction();
		$parameters['id'] = $request->getID();
		$parameters['title'] = $request->getTitle();
		$parameters = array_merge($parameters,
				$request->getParameters());
		$request = $this->getRequest('login', $parameters);
		$widget = $this->formLogin($engine, $request, FALSE);
		return new PageResponse($widget);
	}


	//helpers
	//UserModule::helperApplyUser
	protected function helperApplyUser(Engine $engine, Request $request,
			$action, $fallback, $key = 'user_id')
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
			$user = new User($engine, $id);
			if($user->getUserID() === FALSE) //XXX
				$invalid++;
			else if($user->$action($engine) === FALSE)
				$errors++;
			else
				$success++;
		}
		$type = $errors ? 'error' : ($invalid ? 'warning' : 'info');
		if($errors)
		{
			$message .= "Could not $action $errors user(s)";
			$sep = "\n";
		}
		if($invalid)
		{
			$message .= $sep.$invalid.' '._('invalid user(s)');
			$sep = "\n";
		}
		if($success)
			$message .= $sep."Could $action $success user(s)";
		if($message == '')
			return FALSE;
		return new PageElement('dialog', array('type' => $type,
				'text' => $message));
	}


	//UserModule::helperLogin
	protected function helperLogin($engine, $username, $password)
	{
		$log = $this->configGet('log');

		if(($user = User::lookup($engine, $username)) === FALSE
				|| ($credentials = $user->authenticate($engine,
						$password)) === FALSE)
			return $engine->log('LOG_NOTICE', $username
					.': Invalid login attempt');
		if($engine->setCredentials($credentials) !== TRUE)
			return $engine->log('LOG_NOTICE', $username
					.': Unable to log user in');
		if($log)
			$engine->log('LOG_NOTICE',
					$username.': User logged in');
		return TRUE;
	}


	//UserModule::helperLogout
	protected function helperLogout($engine, $username, $closed = FALSE)
	{
		$log = $this->configGet('log');

		if($engine->setCredentials() === FALSE)
			return $engine->log('LOG_ERR', $username
					.': Unable to log user out');
		if($log)
			$engine->log('LOG_NOTICE', $username
					.': User logged out');
		if($log && $closed)
			$engine->log('LOG_NOTICE',
					$username.': User account closed');
		return TRUE;
	}


	//UserModule::helperRedirect
	protected function helperRedirect($engine, $request, $page,
			$text = FALSE)
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
	static private $query_admin = 'SELECT user_id AS id, username, admin,
		daportal_user.enabled AS enabled, email,
		daportal_group.group_id AS group_id, groupname,
		substr(password, 1, 1) AS locked
		FROM daportal_user
		LEFT JOIN daportal_group
		ON daportal_user.group_id=daportal_group.group_id';
	static private $query_content = "SELECT name FROM daportal_module
		WHERE enabled='1' ORDER BY name ASC";
	//IN:	user_id
	static private $query_groups_user = 'SELECT dug.group_id AS id,
		groupname, COUNT(member.user_id) AS count
		FROM daportal_user_group dug, daportal_group,
		daportal_user_group member
		WHERE dug.group_id=daportal_group.group_id
		AND dug.user_id=:user_id
		AND daportal_group.group_id=member.group_id
		GROUP BY dug.group_id, groupname
		ORDER BY groupname ASC';
	//IN:	user_id
	//	fullname
	//	email
	static private $query_update = 'UPDATE daportal_user
		SET fullname=:fullname, email=:email
		WHERE user_id=:user_id';
}

?>
