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
		if($internal)
			return FALSE;
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		switch($action)
		{
			case 'default':
			case 'display':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//protected
	//methods
	//calls
	//SaltModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$title = 'Salt monitoring';
		$hostname = $request->get('host');

		$page = new Page(array('title' => $title));
		$page->append('title', array('text' => $title));
		$vbox = $page->append('vbox');
		$this->_defaultForm($vbox, $hostname);
		if($hostname !== FALSE)
			$this->_defaultHost($vbox, $hostname);
		return new PageResponse($page);
	}

	private function _defaultForm($page, $hostname)
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

	private function _defaultHost($page, $hostname)
	{
		if(($data = $this->helperSaltStatusAll($hostname)) === FALSE)
		{
			$error = _('Could not obtain statistics');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
				return;
		}
		foreach($data as $hostname => $data)
		{
			$page->append('title', array('text' => $hostname));
			$this->renderStatusAll($page, $data);
		}
	}


	//helpers
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
		$output = implode("\n", $output);
		if(($data = json_decode($output)) === NULL)
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


	//SaltModule::helperSaltStatusAll
	protected function helperSaltStatusAll($hostname)
	{
		return $this->helperSalt($hostname, 'status.all_status');
	}


	//rendering
	//SaltModule::renderDiskusage
	private function renderDiskusage($page, $data)
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
	protected function renderLoadavg($page, $data)
	{
		$page->append('title', array('text' => _('Load average')));
		foreach($data as $key => $value)
			$page->append('label', array(
					'text' => $key.': '.$value));
	}


	//SaltModule::renderNetdev
	protected function renderNetdev($page, $data)
	{
		$page->append('title', array('text' => _('Network interfaces')));
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


	//SaltModule::renderStatusAll
	protected function renderStatusAll($page, $data)
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
}

?>
