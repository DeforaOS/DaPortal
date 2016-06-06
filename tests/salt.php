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
	}


	//SaltModuleTest::test
	public function test(Engine $engine)
	{
		$ret = TRUE;
		$accessors = array('Reboot', 'ServiceReload', 'ServiceRestart',
			'ServiceStart', 'ServiceStop', 'Shutdown', 'Upgrade');
		$helpers = array('diskusage' => 'status.diskusage',
			'loadavg' => 'status.loadavg', 'ping' => 'test.ping',
			'reboot' => 'system.reboot',
			'shutdown' => 'system.shutdown',
			'statusall' => 'status.all_status');

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
			return $this->engine->log('LOG_ERR', $error);
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
			return $this->engine->log('LOG_ERR', $error);
		}
		return TRUE;
	}


	//protected
	//methods
	protected function helperSalt($hostname = FALSE, $command = 'test.ping',
			$args = FALSE, $options = FALSE)
	{
		return $command;
	}
}

//functions
$module = new SaltModuleTest(0, 'salt', 'Salt');
if($module->test($engine) !== TRUE)
	exit(2);
exit(0);

?>
