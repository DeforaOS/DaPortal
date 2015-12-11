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
		$this->_defaultLoad($vbox, $hostname);
		$this->_defaultDisk($vbox, $hostname);
		$this->_defaultNetwork($vbox, $hostname);
		return new PageResponse($page);
	}

	private function _defaultDisk($page, $hostname)
	{
		if(($data = $this->helperSalt($hostname, 'disk.usage'))
				=== FALSE)
		{
			$error = _('Could not obtain disk usage');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
				return;
		}
		foreach($data as $host => $hostdata)
		{
			$page->append('title', array('text' => $host));
			$vbox = $page->append('vbox');
			$vbox->append('title', array('text' => 'Disk usage'));
			foreach($hostdata as $vol => $voldata)
			{
				sscanf($voldata->capacity, '%u', $capacity);
				$progress = $vbox->append('progress', array(
					'text' => $vol.': '.$voldata->capacity,
					'min' => 0, 'max' => 100, 'high' => 75,
					'value' => $capacity));
				$progress->append('label', array(
					'text' => ' '.$vol.' ('
					.$voldata->filesystem.')'));
			}
		}
	}

	private function _defaultLoad($page, $hostname)
	{
		if(($data = $this->helperSalt($hostname, 'status.loadavg'))
				=== FALSE)
		{
			$error = _('Could not obtain load average');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
				return;
		}
		foreach($data as $host => $hostdata)
		{
			$page->append('title', array('text' => $host));
			$vbox = $page->append('vbox');
			$vbox2 = $vbox->append('vbox');
			$vbox2->append('title', array(
					'text' => _('Load average')));
			foreach($hostdata as $key => $value)
				$vbox2->append('label', array(
						'text' => $key.': '.$value));
		}
	}

	private function _defaultNetwork($page, $hostname)
	{
		if(($data = $this->helperSalt($hostname, 'status.netdev'))
				=== FALSE)
		{
			$error = _('Could not obtain network statistics');
			$page->append('dialog', array(
					'type' => 'error', 'text' => $error));
				return;
		}
		foreach($data as $host => $hostdata)
		{
			$page->append('title', array('text' => $host));
			$vbox = $page->append('vbox');
			foreach($hostdata as $name => $interface)
			{
				$title = _('Interface ').$name;
				$vbox->append('title', array('text' => $title));
				foreach($interface as $key => $value)
					$vbox->append('label', array(
							'text' => $key.': '.$value));
			}
		}
	}


	//helper
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
}

?>
