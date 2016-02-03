<?php //$Id$
//Copyright (c) 2015-2016 Pierre Pronchery <khorben@defora.org>
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
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->$action($request);
				default:
					return FALSE;
			}
		$method = 'call'.$action;
		if(!method_exists($this, $method))
			return new ErrorResponse(_('Invalid action'),
				Response::$CODE_ENOENT);
		return $this->$method($request);
	}


	//protected
	//methods
	//accessors
	//SaltModule::canReboot
	protected function canReboot(Request $request = NULL, $hostname = FALSE,
			&$error = FALSE)
	{
		$credentials = $this->engine->getCredentials();

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


	//SaltModule::canServiceReload
	protected function canServiceReload(Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		return $this->canServiceRestart($request, $hostname, $error);
	}


	//SaltModule::canServiceRestart
	protected function canServiceRestart(Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		$credentials = $this->engine->getCredentials();

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


	//SaltModule::canServiceStart
	protected function canServiceStart(Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		return $this->canServiceRestart($request, $hostname, $error);
	}


	//SaltModule::canServiceStop
	protected function canServiceStop(Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		return $this->canServiceRestart($request, $hostname, $error);
	}


	//SaltModule::canShutdown
	protected function canShutdown(Request $request = NULL,
			$hostname = FALSE, &$error = FALSE)
	{
		return $this->canReboot($request, $hostname, $error);
	}


	//actions
	//SaltModule::actions
	protected function actions($request)
	{
		return array();
	}


	//calls
	//SaltModule::callDefault
	protected function callDefault(Request $request)
	{
		$title = _('Salt monitoring');
		$hostname = $request->get('host');

		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'monitor',
				'text' => $title));
		$vbox = $page->append('vbox');
		$this->_defaultForm($vbox, $hostname);
		if(!is_string($hostname) || strlen($hostname) == 0)
			$this->_defaultList($vbox);
		else
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
				'placeholder' => _('Hostname (or glob)'),
				'name' => 'host', 'value' => $hostname));
		$hbox->append('button', array('type' => 'submit',
				'text' => _('Monitor')));
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
		{
			if(!is_array($data))
				$data = array($data);
			foreach($data as $d)
				foreach($d as $hostname => $data)
					$this->renderUptime($page, $data);
		}
		if(($data = $this->helperSaltServiceListEnabled($hostname))
				=== FALSE)
		{
			$error = _('Could not list services');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
		}
		else
		{
			if(!is_array($data))
				$data = array($data);
			$vbox = $page->append('vbox');
			foreach($data as $d)
				foreach($d as $hostname => $data)
				{
					$args = array('hostname' => $hostname);
					$this->renderServiceList($vbox, $data,
							$args);
				}
		}
		if(($data = $this->helperSaltStatusAll($hostname)) === FALSE)
		{
			$error = _('Could not obtain statistics');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
		}
		else
		{
			if(!is_array($data))
				$data = array($data);
			foreach($data as $d)
				foreach($d as $hostname => $data)
					$this->renderStatusAll($page, $data);
		}
	}

	private function _defaultList(PageElement $page)
	{
		if(($data = $this->helperSaltPing()) === FALSE)
		{
			$error = _('Could not list hosts');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
			return;
		}
		$view = $page->append('iconview');
		$icon = new PageElement('image', array('stock' => 'server'));
		if(!is_array($data))
			$data = array($data);
		foreach($data as $d)
			foreach($d as $hostname => $value)
			{
				$request = $this->getRequest(FALSE,
					array('host' => $hostname));
				$link = new PageElement('link', array(
						'request' => $request,
						'text' => $hostname));
				$view->append('row', array('icon' => $icon,
						'label' => $link));
			}
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


	//SaltModule::callReboot
	protected function callReboot(Request $request)
	{
		return $this->helperAction($request);
	}


	//SaltModule::callServiceReload
	protected function callServiceReload(Request $request)
	{
		return $this->helperAction($request, array('service'));
	}


	//SaltModule::callServiceRestart
	protected function callServiceRestart(Request $request)
	{
		return $this->helperAction($request, array('service'));
	}


	//SaltModule::callServiceStart
	protected function callServiceStart(Request $request)
	{
		return $this->helperAction($request, array('service'));
	}


	//SaltModule::callServiceStop
	protected function callServiceStop(Request $request)
	{
		return $this->helperAction($request, array('service'));
	}


	//SaltModule::callShutdown
	protected function callShutdown(Request $request)
	{
		return $this->helperAction($request);
	}


	//helpers
	//SaltModule::helperAction
	protected function helperAction(Request $request, $args = array())
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
				&& ($code = $this->$method($request, $hostname,
					$error)) !== Response::$CODE_SUCCESS)
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
		$a = array($hostname);
		foreach($args as $arg)
			$a[] = $request->get($arg);
		if(call_user_func_array(array($this, $method), $a) === FALSE)
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
		$salt = $this->configGet('salt') ?: 'salt';
		$options = is_array($options)
			? $options : array('--out=json', '--static');
		$hostname = (is_string($hostname) && strlen($hostname))
			? $hostname : '*';
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
		if(($data = json_decode(implode($output, "\n"))) === NULL)
			return FALSE;
		return $data;
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


	//SaltModule::helperSaltPing
	protected function helperSaltPing($hostname = FALSE)
	{
		return $this->helperSalt($hostname, 'test.ping');
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


	//SaltModule::helperSaltServiceReload
	protected function helperSaltServiceReload($hostname, $service)
	{
		return $this->helperSalt($hostname, 'service.reload',
				array($service));
	}


	//SaltModule::helperSaltServiceRestart
	protected function helperSaltServiceRestart($hostname, $service)
	{
		return $this->helperSalt($hostname, 'service.restart',
				array($service));
	}


	//SaltModule::helperSaltServiceStart
	protected function helperSaltServiceStart($hostname, $service)
	{
		return $this->helperSalt($hostname, 'service.start',
				array($service));
	}


	//SaltModule::helperSaltServiceStatus
	protected function helperSaltServiceStatus($hostname, $service)
	{
		return $this->helperSalt($hostname, 'service.status',
				array($service));
	}


	//SaltModule::helperSaltServiceStop
	protected function helperSaltServiceStop($hostname, $service)
	{
		return $this->helperSalt($hostname, 'service.stop',
				array($service));
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
	private function renderDiskusage(PageElement $page, $data,
			$args = array())
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
	protected function renderLoadavg(PageElement $page, $data,
			$args = array())
	{
		$page->append('title', array('text' => _('Load average')));
		foreach($data as $key => $value)
			$page->append('label', array(
					'text' => $key.': '.$value));
	}


	//SaltModule::renderNetdev
	protected function renderNetdev(PageElement $page, $data,
			$args = array())
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
	protected function renderServiceList(PageElement $page, $data,
			$args = array())
	{
		$calls = array(
			'reload' => array('text' => _('Reload'),
				'stock' => 'media-previous'),
			'start' => array('text' => _('Start'),
				'stock' => 'media-play'),
			'stop' => array('text' => _('Stop'),
				'stock' => 'media-stop'),
			'restart' => array('text' => _('Restart'),
				'stock' => 'media-loop'));

		if(!isset($args['hostname']))
			return;
		$hostname = $args['hostname'];
		$page->append('title', array('text' => _('Services')));
		$columns = array('service' => '', 'actions' => '');
		$view = $page->append('treeview', array('columns' => $columns,
				'alternate' => TRUE));
		foreach($data as $service)
		{
			$actions = FALSE;
			foreach($calls as $call => $p)
			{
				$c = 'canService'.$call;
				if($this->$c(NULL, $hostname, $error)
						!== Response::$CODE_SUCCESS)
					continue;
				$r = $this->getRequest('service'.$call, array(
						'host' => $hostname,
						'service' => $service));
				if($actions === FALSE)
					$actions = new PageElement('hbox');
				$actions->append('button', array(
						'stock' => $p['stock'],
						'request' => $r,
						'text' => $p['text']));
			}
			$view->append('row', array('service' => $service,
					'actions' => $actions));
		}
	}


	//SaltModule::renderStatusAll
	protected function renderStatusAll(PageElement $page, $data,
			$args = array())
	{
		if(!($data instanceof Traversable) && !is_object($data))
			return;
		foreach($data as $key => $value)
		{
			$vbox = new PageElement('vbox');
			$append = TRUE;
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
					$append = FALSE;
					break;
			}
			if($append)
				$page->append($vbox);
		}
	}


	//SaltModule::renderUptime
	protected function renderUptime(PageElement $page, $data,
			$args = array())
	{
		$page->append('label', array('text' => $data));
	}


	//private
	//properties
	private $engine;
}

?>
