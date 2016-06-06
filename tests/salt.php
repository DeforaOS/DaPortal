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

		$this->engine = $engine;
		foreach($accessors as $accessor)
			if($this->_testAccessor($accessor,
					Response::$CODE_EPERM) === FALSE)
				$ret = FALSE;
		$this->engine = NULL;
		return $ret;
	}
}

//functions
$module = new SaltModuleTest(0, 'salt', 'Salt');
if($module->test($engine) !== TRUE)
	exit(2);
exit(0);

?>
