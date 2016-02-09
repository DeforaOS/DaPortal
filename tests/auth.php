<?php
//Copyright (c) 2014-2016 Pierre Pronchery <khorben@defora.org>
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

	public function matchAll(Engine $engine)
	{
		$classes = array();

		if(($dir = opendir('auth')) === FALSE)
			return;
		while(($de = readdir($dir)) !== FALSE)
		{
			if(substr($de, -4) != '.php')
				continue;
			$classes[] = substr($de, 0, -4).'Auth';
		}
		closedir($dir);
		sort($classes);
		foreach($classes as $class)
		{
			$auth = new $class();
			$engine->log('LOG_INFO', get_class($auth).': '
					.$auth->match($engine));
		}

	}

	public function setIdempotent(Engine $engine, Request &$request,
			$idempotent)
	{
		$request = NULL;
	}
}


//functions
$auth = new AuthTest();
$auth->matchAll($engine);

$request = new Request();
$auth->setIdempotent($engine, $request, FALSE);
if(!is_null($request))
	exit(2);

$user = new User($engine, 1, 'admin');
if(($credentials = $user->authenticate($engine, 'password')) === FALSE)
	exit(3);
//may as well have failed
$auth->setCredentials($engine, $credentials);

if($auth->setVariable($engine, 'test1', 'test2') === FALSE)
	exit(4);
if($auth->getVariable($engine, 'test1') != 'test2')
	exit(5);
if($auth->getVariable($engine, 'test2') !== FALSE)
	exit(6);
if($auth->setVariable($engine, 'test3', 41) === FALSE)
	exit(7);
if($auth->getVariable($engine, 'test3') != 41)
	exit(8);
if($auth->setVariable($engine, 'test1', FALSE) !== TRUE)
	exit(9);
if($auth->getVariable($engine, 'test1') !== FALSE)
	exit(10);
exit(0);

?>
