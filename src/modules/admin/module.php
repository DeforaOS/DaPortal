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



//AdminModule
class AdminModule extends Module
{
	//public
	//methods
	//essential
	//AdminModule::AdminModule
	public function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Administration') : $title;
		parent::__construct($id, $name, $title);
	}


	//useful
	//AdminModule::call
	public function call($engine, $request, $internal = 0)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
		{
			if($request->getAction() == 'actions')
				return FALSE;
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('Permission denied')));
		}
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
			case 'enable':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return FALSE;
	}


	//protected
	//properties
	static protected $stock_back = 'back';
	//queries
	static protected $query_admin = "SELECT name FROM daportal_module
		WHERE enabled='1' ORDER BY name ASC";
	static protected $query_check_password = "SELECT username
		FROM daportal_user
		WHERE username='admin'
		AND password='$1$?0p*PI[G\$kbHyE5VE/S32UrV88Unz/1'";
	static protected $query_module_disable = "UPDATE daportal_module
		SET enabled='0'
		WHERE module_id=:module_id";
	static protected $query_module_enable = "UPDATE daportal_module
		SET enabled='1'
		WHERE module_id=:module_id";
	static protected $query_module_list = "SELECT module_id, name, enabled
		FROM daportal_module
		ORDER BY name ASC";


	//methods
	//useful
	//actions
	//AdminModule::actions
	protected function actions($engine, $request)
	{
		$admin = array('audit' => _('Configuration audit'),
			'modules' => _('Modules administration'));

		if($request->get('user') !== FALSE
				|| $request->get('group') !== FALSE)
			return FALSE;
		foreach($admin as $k => $v)
		{
			$r = new Request($this->name, 'admin', FALSE, FALSE,
					array('admin' => $k));
			$icon = new PageElement('image', array(
					'stock' => 'admin'));
			$link = new PageElement('link', array('request' => $r,
					'text' => $v));
			$ret[] = new PageElement('row', array('icon' => $icon,
					'label' => $link));
		}
		return $ret;
	}


	//calls
	//AdminModule::callAdmin
	protected function callAdmin($engine, $request = FALSE)
	{
		$title = _('Administration');
		$admin = ($request !== FALSE) ? $request->get('admin') : FALSE;

		$page = new Page();
		switch($admin)
		{
			case 'audit':
				$this->callAdminAudit($engine, $request, $page);
				break;
			case 'modules':
				$this->callAdminModules($engine, $request,
						$page);
				break;
			default:
				$page->append('title', array('stock' => 'admin',
						'text' => $title));
				$this->callAdminAudit($engine, $request, $page);
				$this->callAdminModules($engine, $request,
						$page);
				$page->set('title', $title);
				break;
		}
		$request = $this->getRequest();
		$page->append('link', array('request' => $request,
				'stock' => static::$stock_back,
				'text' => _('Back to the administration')));
		return new PageResponse($page);
	}

	protected function callAdminAudit($engine, $request, $page)
	{
		$title = _('Configuration audit');
		$database = $engine->getDatabase();
		$query = static::$query_check_password;

		$page->append('title', array('stock' => 'admin',
				'text' => $title));
		//check for the default password
		if(($res = $database->query($engine, $query)) === FALSE)
		{
			$text = _('Could not check for the default password');
			$page->append('dialog', array('type' => 'error',
					'text' => $text));
		}
		else if(count($res) > 0)
		{
			$text = _('The administrative password must be changed');
			//XXX add a direct link
			$page->append('dialog', array('type' => 'warning',
					'text' => $text));
		}
		else
		{
			$text = _('The default password was changed accordingly');
			$page->append('dialog', array('type' => 'info',
					'text' => $text));
		}
	}

	protected function callAdminModules($engine, $request, $page)
	{
		$actions = array('disable', 'enable');
		$database = $engine->getDatabase();
		$query = static::$query_module_list;
		$title = _('Modules administration');
		$dialog = FALSE;

		//perform actions if necessary
		if($request !== FALSE)
			foreach($actions as $a)
				if($request->get($a) !== FALSE)
				{
					$a = 'helperModule'.$a;
					$dialog = $this->$a($engine, $request);
					break;
				}
		//list modules
		if(($res = $database->query($engine, $query)) === FALSE)
			return new ErrorResponse(_('Could not list modules'));
		$page->set('title', $title);
		$page->append('title', array('stock' => 'admin',
				'text' => $title));
		if($dialog !== FALSE)
			$page->append($dialog);
		$r = new Request($this->name, 'admin', FALSE, FALSE, array(
				'admin' => 'modules'));
		$columns = array('module' => _('Module'),
				'enabled' => _('Enabled'));
		$view = $page->append('treeview', array('request' => $r,
				'columns' => $columns));
		//toolbar
		$toolbar = $view->append('toolbar');
		$toolbar->append('button', array('request' => $r,
				'stock' => 'refresh',
				'text' => _('Refresh')));
		$toolbar->append('button', array('stock' => 'disable',
				'text' => _('Disable'),
				'type' => 'submit', 'name' => 'action',
				'value' => 'disable'));
		$toolbar->append('button', array('stock' => 'enable',
				'text' => _('Enable'),
				'type' => 'submit', 'name' => 'action',
				'value' => 'enable'));
		$no = new PageElement('image', array('stock' => 'no',
			'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
			'size' => 16, 'title' => _('Enabled')));
		foreach($res as $r)
		{
			$row = $view->append('row');
			$row->setProperty('id', 'module_id:'.$r['module_id']);
			$request = new Request($r['name'], 'admin');
			$text = ucfirst($r['name']);
			$link = new PageElement('link', array(
				'request' => $request, 'stock' => $r['name'],
				'text' => $text));
			$row->setProperty('module', $link);
			$row->setProperty('enabled', $database->isTrue(
					$r['enabled']) ? $yes : $no);
		}
	}


	//AdminModule::callDefault
	protected function callDefault($engine, $request)
	{
		$title = _('Administration');
		$database = $engine->getDatabase();
		$query = static::$query_admin;

		//obtain the list of modules
		if(($res = $database->query($engine, $query)) === FALSE)
			return new ErrorResponse(_('Could not list modules'));
		$page = new Page(array('title' => $title));
		//title
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$vbox = $page->append('vbox');
		$vbox->append('title'); //XXX to reduce the next level of titles
		$vbox = $vbox->append('vbox');
		foreach($res as $r)
		{
			$request = new Request($r['name'], 'actions', FALSE,
					FALSE, array('admin' => TRUE));
			$rows = $engine->process($request, TRUE);
			if(!is_array($rows) || count($rows) == 0)
				continue;
			$text = ucfirst($r['name']);
			$request = new Request($r['name']);
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
		return new PageResponse($page);
	}


	//helpers
	//AdminModule::helperApply
	protected function helperApply($engine, $request, $query, $success,
			$failure, $key = 'module_id')
	{
		//FIXME synchronize with ContentModule
		$cred = $engine->getCredentials();
		$db = $engine->getDatabase();
		$affected = 0;

		if(!$cred->isAdmin())
			//must be admin
			return new PageElement('dialog', array(
					'type' => 'error',
					'text' => _('Permission denied')));
		if($request->isIdempotent())
			//must be safe
			return FALSE;
		$type = 'info';
		$message = $success;
		$parameters = $request->getParameters();
		foreach($parameters as $k => $v)
		{
			$x = explode(':', $k);
			if(count($x) != 2 || $x[0] != $key
					|| !is_numeric($x[1]))
				continue;
			$args = array($key => $x[1]);
			if(($res = $db->query($engine, $query, $args))
					!== FALSE)
			{
				$affected += $res->getAffectedCount();
				continue;
			}
			$type = 'error';
			$message = $failure;
		}
		return ($affected > 0) ? new PageElement('dialog', array(
				'type' => $type, 'text' => $message)) : FALSE;
	}


	//AdminModule::helperModuleDisable
	protected function helperModuleDisable($engine, $request)
	{
		$query = static::$query_module_disable;

		return $this->helperApply($engine, $request, $query,
				_('Module(s) could be disabled successfully'),
				_('Some module(s) could not be disabled'));
	}


	//AdminModule::helperModuleEnable
	protected function helperModuleEnable($engine, $request)
	{
		$query = static::$query_module_enable;

		return $this->helperApply($engine, $request, $query,
				_('Module(s) could be enabled successfully'),
				_('Some module(s) could not be enabled'));
	}
}

?>
