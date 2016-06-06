<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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



require_once('./tests.php');


class SaltModuleTest extends SaltModule
{
	//public
	//methods
	//SaltModuleTest::SaltModuleTest
	public function __construct($id, $name, $title)
	{
		global $config;

		parent::__construct($id, $name, $title);
		$this->configSet('salt', '/bin/echo');
	}


	//SaltModuleTest::test
	public function test(Engine $engine)
	{
		$ret = TRUE;
		$accessors = array('Reboot', 'ServiceReload', 'ServiceRestart',
			'ServiceStart', 'ServiceStop', 'Shutdown', 'Upgrade');
		$helpers = array('diskusage'
			=> 'salt --out=json --static localhost status.diskusage',
			'loadavg'
			=> 'salt --out=json --static localhost status.loadavg',
			'ping'
			=> 'salt --out=json --static localhost test.ping',
			'reboot'
			=> 'salt --out=json --static localhost system.reboot',
			'shutdown'
			=> 'salt --out=json --static localhost system.shutdown',
			'statusall'
			=> 'salt --out=json --static localhost status.all_status');

		$this->engine = $engine;
		foreach($accessors as $accessor)
			if($this->_testAccessor($accessor,
					Response::$CODE_EPERM) === FALSE)
				$ret = FALSE;
		foreach($helpers as $helper => $expected)
			if($this->_testHelper($helper, $expected) === FALSE)
				$ret = FALSE;
		$this->engine = NULL;
		return $ret;
	}

	protected function _testAccessor($accessor, $expected = FALSE)
	{
		$method = 'can'.$accessor;
		if(($obtained = $this->$method()) != $expected
				&& $expected !== FALSE)
		{
			$error = "$accessor: Obtained \"$obtained\""
			       ." (Expected \"$expected\")";
			return $this->engine->log(LOG_ERR, $error);
		}
		return TRUE;
	}

	protected function _testHelper($helper, $expected)
	{
		$hostname = 'localhost';
		$method = 'helperSalt'.$helper;

		if(($obtained = $this->$method($hostname)) != $expected)
		{
			$error = "$helper: Obtained \"$obtained\""
			       ." (Expected \"$expected\")";
			return $this->engine->log(LOG_ERR, $error);
		}
		return TRUE;
	}


	//protected
	//methods
	//SaltModuleTest::helperSalt
	protected function helperSalt($hostname = FALSE, $command = 'test.ping',
			$args = FALSE, $options = FALSE)
	{
		//XXX duplicated from SaltModule::helperSalt
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
		return 'salt '.implode("\n", $output);
	}
}

//functions
$module = new SaltModuleTest(0, 'salt', 'Salt');
if($module->test($engine) !== TRUE)
	exit(2);
exit(0);

?>
