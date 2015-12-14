<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
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



//SaltModule
class SaltModule extends Module
{
	//public
	//methods
	//SaltModule::call
	function call(Engine $engine, Request $request, $internal = 0)
	{
		//XXX should be saved in the constructor
		$this->engine = $engine;
		if($internal)
			return FALSE;
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		$method = 'call'.$action;
		if(!method_exists($this, $method))
			return new ErrorResponse(_('Invalid action'),
				Response::$CODE_ENOENT);
		return $this->$method($engine, $request);
	}


	//protected
	//methods
	//accessors
	//SaltModule::canReboot
	protected function canReboot(Engine $engine, Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(!$credentials->isAdmin())
		{
			$error = _('Permission denied');
			return Response::$CODE_EPERM;
		}
		if($request !== NULL && $request->isIdempotent())
		{
			$error = _('Confirmation required');
			return Response::$CODE_EROFS;
		}
		//let Salt decide
		return Response::$CODE_SUCCESS;
	}


	//SaltModule::canServiceRestart
	protected function canServiceRestart(Engine $engine,
			Request $request = NULL, $hostname = FALSE,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(!$credentials->isAdmin())
		{
			$error = _('Permission denied');
			return Response::$CODE_EPERM;
		}
		if($request !== NULL && $request->isIdempotent())
		{
			$error = _('Confirmation required');
			return Response::$CODE_EROFS;
		}
		//let Salt decide
		return Response::$CODE_SUCCESS;
	}


	//SaltModule::canShutdown
	protected function canShutdown(Engine $engine, Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		return $this->canReboot($engine, $request, $hostname, $error);
	}


	//calls
	//SaltModule::callDefault
	protected function callDefault(Engine $engine, Request $request = NULL)
	{
		$title = _('Salt monitoring');
		$hostname = ($request !== NULL)
			? $request->get('host') : FALSE;

		$page = new Page(array('title' => $title));
		$page->append('title', array('text' => $title));
		$vbox = $page->append('vbox');
		$this->_defaultForm($vbox, $hostname);
		if($hostname !== FALSE)
			$this->_defaultHost($vbox, $hostname);
		return new PageResponse($page);
	}

	private function _defaultForm(PageElement $page, $hostname)
	{
		$form = $page->append('form', array(
				'idempotent' => TRUE,
				'request' => $this->getRequest()));
		$hbox = $form->append('hbox');
		$hbox->append('entry', array('text' => _('Host: '),
				'name' => 'host', 'value' => $hostname));
		$hbox->append('button', array('type' => 'submit',
				'text' => _('Monitor')));
	}

	private function _defaultToolbar(PageElement $page, $hostname)
	{
		$toolbar = $page->append('toolbar');
		$request = $this->getRequest('reboot', array(
				'host' => $hostname));
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Reboot'),
				'request' => $request));
		$request = $this->getRequest('shutdown', array(
				'host' => $hostname));
		$toolbar->append('button', array('stock' => 'logout',
				'text' => _('Shutdown'),
				'request' => $request));
	}

	private function _defaultHost(PageElement $page, $hostname)
	{
		$page->append('title', array('text' => $hostname));
		$this->_defaultToolbar($page, $hostname);
		if(($data = $this->helperSaltUptime($hostname)) === FALSE)
		{
			$error = _('Could not obtain uptime');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
		}
		else
			foreach($data as $hostname => $data)
				$this->renderUptime($page, $data);
		if(($data = $this->helperSaltServiceListEnabled($hostname))
				=== FALSE)
		{
			$error = _('Could not list services');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
		}
		else
			foreach($data as $hostname => $data)
				$this->renderServiceList($page, $data);
		if(($data = $this->helperSaltStatusAll($hostname)) === FALSE)
		{
			$error = _('Could not obtain statistics');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
		}
		else
			foreach($data as $hostname => $data)
				$this->renderStatusAll($page, $data);
	}


	//SaltModule::callReboot
	protected function callReboot(Engine $engine, Request $request)
	{
		return $this->helperAction($engine, $request);
	}


	//SaltModule::callShutdown
	protected function callShutdown(Engine $engine, Request $request)
	{
		return $this->helperAction($engine, $request);
	}


	//helpers
	//SaltModule::helperAction
	protected function helperAction(Engine $engine, Request $request)
	{
		$action = $request->getAction();

		if(($hostname = $request->get('host')) === FALSE)
		{
			$error = _('Unknown host');
			$page = new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
			return new PageResponse($page, Response::$CODE_ENOENT);
		}
		$method = 'can'.$action;
		if(method_exists($this, $method)
				&& ($code = $this->$method($engine, $request,
					$hostname, $error))
					!== Response::$CODE_SUCCESS)
			return $this->_actionError($request, $hostname, $action,
					$code, $error);
		$method = 'helperSalt'.$action;
		if(!method_exists($this, $method))
		{
			$error = _('Unsupported action');
			$page = new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
			return new PageResponse($page,
				Response::$CODE_ENOENT);
		}
		if($this->$method($hostname) === FALSE)
		{
			$error = sprintf(_('Could not %s'), $action);
			$page = new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		$message = sprintf(_('%s successful'), ucfirst($action));
		$dialog = new PageElement('dialog', array('type' => 'info',
				'title' => $hostname, 'text' => $message));
		$r = $this->getRequest(FALSE, array('host' => $hostname));
		$dialog->append('button', array('stock' => 'back',
				'request' => $r, 'text' => _('Back')));
		return new PageResponse($dialog);
	}

	private function _actionError($request, $hostname, $action, $code,
			$error)
	{
		switch($code)
		{
			case Response::$CODE_EROFS:
				$error .= "\n";
				$format = _('Do you really want to %s %s?');
				$error .= sprintf($format, $action, $hostname);
				$dialog = new PageElement('dialog', array(
						'type' => 'question',
						'text' => $error));
				$form = $dialog->append('form', array(
						'request' => $request));
				$r = $this->getRequest(FALSE, array(
						'host' => $hostname));
				$form->append('button', array(
						'stock' => 'cancel',
						'request' => $r,
						'text' => _('Cancel')));
				$form->append('button', array(
						'type' => 'submit',
						'text' => ucfirst($action)));
				return new PageResponse($dialog);
			default:
				$dialog = new PageElement('dialog', array(
						'type' => 'error',
						'text' => $error));
				return new PageResponse($dialog);
		}
	}


	//SaltModule::helperSalt
	protected function helperSalt($hostname = FALSE, $command = 'test.ping',
			$args = FALSE, $options = FALSE)
	{
		$salt = 'salt';
		$options = is_array($options) ? $options : array('--out=json');
		$hostname = $hostname ?: '*';
		$args = is_array($args) ? $args : array();

		$cmd = escapeshellarg($salt);
		foreach($options as $option)
			$cmd .= ' '.escapeshellarg($option);
		$cmd .= ' '.escapeshellarg($hostname);
		$cmd .= ' '.escapeshellarg($command);
		foreach($args as $arg)
			$cmd .= ' '.escapeshellarg($arg);
		exec($cmd, $output, $res);
		if($res != 0)
			//something went really wrong, or finally right, or this
			//is not salt, or it is not installed
			return FALSE;
		$output = $this->_saltOutput($output);
		if(($data = json_decode($output)) === NULL)
			return FALSE;
		return $data;
	}

	private function _saltOutput($output)
	{
		//XXX re-format as valid JSON
		$ret = '';
		$array = FALSE;
		$sep = '';

		for($i = 0, $cnt = count($output); $i < $cnt; $i++, $sep = "\n")
			if($i + 1 != $cnt && $output[$i] == '}')
			{
				$ret .= $sep.'},';
				$array = TRUE;
			}
			else
				$ret .= $sep.$output[$i];
		if($array)
			return '['.$ret.']';
		return $ret;
	}


	//SaltModule::helperSaltDiskusage
	protected function helperSaltDiskusage($hostname)
	{
		return $this->helperSalt($hostname, 'status.diskusage');
	}


	//SaltModule::helperSaltLoadavg
	protected function helperSaltLoadavg($hostname)
	{
		return $this->helperSalt($hostname, 'status.loadavg');
	}


	//SaltModule::helperSaltNetdev
	protected function helperSaltNetdev($hostname)
	{
		return $this->helperSalt($hostname, 'status.netdev');
	}


	//SaltModule::helperSaltReboot
	protected function helperSaltReboot($hostname)
	{
		return $this->helperSalt($hostname, 'system.reboot');
	}


	//SaltModule::helperSaltShutdown
	protected function helperSaltShutdown($hostname)
	{
		return $this->helperSalt($hostname, 'system.shutdown');
	}


	//SaltModule::helperSaltServiceList
	protected function helperSaltServiceList($hostname)
	{
		return $this->helperSalt($hostname, 'service.get_all');
	}


	//SaltModule::helperSaltServiceListEnabled
	protected function helperSaltServiceListEnabled($hostname)
	{
		return $this->helperSalt($hostname, 'service.get_enabled');
	}


	//SaltModule::helperSaltServiceStatus
	protected function helperSaltServiceStatus($hostname, $service)
	{
		return $this->helperSalt($hostname, 'service.status', $service);
	}


	//SaltModule::helperSaltStatusAll
	protected function helperSaltStatusAll($hostname)
	{
		return $this->helperSalt($hostname, 'status.all_status');
	}


	//SaltModule::helperSaltUptime
	protected function helperSaltUptime($hostname)
	{
		return $this->helperSalt($hostname, 'status.uptime');
	}


	//rendering
	//SaltModule::renderDiskusage
	private function renderDiskusage(PageElement $page, $data)
	{
		$page->append('title', array('text' => 'Disk usage'));
		foreach($data as $vol => $voldata)
		{
			if($voldata->total == 0)
				continue;
			$capacity = 100 - ($voldata->available
				/ $voldata->total * 100);
			$progress = $page->append('progress', array(
				'text' => $vol.': '.round($capacity).'%',
				'min' => 0, 'max' => 100, 'high' => 75,
				'value' => $capacity));
			$progress->append('label', array(
				'text' => ' '.$vol));
		}
	}


	//SaltModule::renderLoadavg
	protected function renderLoadavg(PageElement $page, $data)
	{
		$page->append('title', array('text' => _('Load average')));
		foreach($data as $key => $value)
			$page->append('label', array(
					'text' => $key.': '.$value));
	}


	//SaltModule::renderNetdev
	protected function renderNetdev(PageElement $page, $data)
	{
		$title = _('Network interfaces');

		$page->append('title', array('text' => $title));
		foreach($data as $name => $interface)
		{
			$title = $name;
			$vbox = $page->append('vbox');
			$vbox->append('title', array('text' => $title));
			foreach($interface as $key => $value)
				$vbox->append('label', array(
						'text' => $key.': '.$value));
		}
	}


	//SaltModule::renderServiceList
	protected function renderServiceList(PageElement $page, $data)
	{
		$hostname = FALSE; //XXX really obtain

		$vbox = $page->append('vbox');
		$vbox->append('title', array('text' => _('Services')));
		$columns = array('service' => '', 'actions' => '');
		$view = $vbox->append('treeview', array('columns' => $columns,
				'alternate' => TRUE));
		foreach($data as $service)
			$view->append('row', array('service' => $service));
	}


	//SaltModule::renderStatusAll
	protected function renderStatusAll(PageElement $page, $data)
	{
		if(!($data instanceof Traversable) && !is_object($data))
			return;
		foreach($data as $key => $value)
		{
			$vbox = new PageElement('vbox');
			switch($key)
			{
				case 'diskusage':
					$this->renderDiskusage($vbox, $value);
					break;
				case 'loadavg':
					$this->renderLoadavg($vbox, $value);
					break;
				case 'netdev':
					$this->renderNetdev($vbox, $value);
					break;
				default:
					continue;
			}
			$page->append($vbox);
		}
	}


	//SaltModule::renderUptime
	protected function renderUptime(PageElement $page, $data)
	{
		$page->append('label', array('text' => $data));
	}


	//private
	//properties
	private $engine;
}

?>
