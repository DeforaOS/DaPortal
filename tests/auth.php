<?php
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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
require_once('./system/auth.php');
require_once('./system/user.php');


class AuthTest extends Auth
{
	public function getCredentials($engine)
	{
		if($this->credentials === FALSE)
		{
			$user = new User($engine, 1, 'admin');
			$this->credentials = $user->authenticate($engine,
					'password');
		}
		return $this->credentials;
	}

	protected function attach($engine)
	{
	}

	protected function match($engine)
	{
	}
}


//functions
$auth = new AuthTest;
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