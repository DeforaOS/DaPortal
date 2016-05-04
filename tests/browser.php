<?php
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


class BrowserModuleTest extends BrowserModule
{
	//BrowserModuleTest::BrowserModuleTest
	public function __construct($id, $name, $title)
	{
		parent::__construct($id, $name, $title);
	}


	//BrowserModuleTest::test
	public function test($engine)
	{
		global $config;

		//default settings
		if($this->canUpload($engine, NULL, FALSE, $error) !== FALSE)
			return FALSE;
		$engine->log(LOG_INFO, $error);
		//allow uploading
		$config->set('module::'.$this->name, 'upload', 1);
		if($this->canUpload($engine, NULL, FALSE, $error) !== FALSE)
			return FALSE;
		$engine->log(LOG_INFO, $error);
		//authenticate
		$user = new User($engine, 1, 'admin');
		if(($credentials = $user->authenticate($engine, 'password'))
				=== FALSE)
			return FALSE;
		$engine->setCredentials($credentials);
		//should still fail (non-idempotent request)
		if($this->canUpload($engine, NULL, TRUE, $error) !== FALSE)
			return FALSE;
		$engine->log(LOG_INFO, $error);
		//repository does not exist
		$request = new Request($this->name, FALSE, FALSE, '/');
		$request->setIdempotent(FALSE);
		$config->set('module::'.$this->name, 'root', '/nonexistent');
		if($this->canUpload($engine, $request, TRUE, $error) !== FALSE)
			return FALSE;
		$engine->log(LOG_INFO, $error);
		//uploading should be allowed now
		$config->set('module::'.$this->name, 'root',
				sys_get_temp_dir());
		if($this->canUpload($engine, $request, TRUE, $error) !== TRUE)
		{
			$engine->log(LOG_ERR, $error);
			return FALSE;
		}
		return TRUE;
	}
}


//functions
$module = new BrowserModuleTest(0, 'browser', 'Browser');
if($module->test($engine) === FALSE)
	exit(2);

exit(0);

?>
