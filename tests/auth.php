<?php
//Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org>
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


class AuthTest extends Auth
{
	protected function attach(Engine $engine)
	{
	}

	protected function match(Engine $engine)
	{
		return 0;
	}

	protected function matchAll(Engine $engine)
	{
		$classes = array('EnvAuth', 'HTTPAuth', 'SessionAuth',
			'UnixAuth');

		foreach($classes as $class)
		{
			$auth = new $class;
			$auth->match($engine);
		}

	}
}


//functions
$auth = new AuthTest;
$user = new User($engine, 1, 'admin');
$credentials = $user->authenticate($engine, 'password');
//may as well have failed
$auth->setCredentials($engine, $credentials);
if($auth->setVariable($engine, 'test1', 'test2') === FALSE)
	exit(3);
if($auth->getVariable($engine, 'test1') != 'test2')
	exit(4);
if($auth->getVariable($engine, 'test2') !== FALSE)
	exit(5);
if($auth->setVariable($engine, 'test3', 41) === FALSE)
	exit(6);
if($auth->getVariable($engine, 'test3') != 41)
	exit(7);
if($auth->setVariable($engine, 'test1', FALSE) !== TRUE)
	exit(8);
if($auth->getVariable($engine, 'test1') !== FALSE)
	exit(9);
exit(0);

?>
