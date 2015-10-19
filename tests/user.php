<?php //$Id$
//Copyright (c) 2013-2015 Pierre Pronchery <khorben@defora.org>
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


class Mail
{
	static public function send($engine, $from, $to, $subject, $page,
			$headers = FALSE, $attachments = FALSE)
	{
		//output the page
		if($engine->render(new PageResponse($page)) === FALSE)
			return FALSE;

		//do not really send any e-mail
		return TRUE;
	}
}


//functions
function user_authenticate($engine, $user)
{
	if(($res = $user->authenticate($engine, 'password')) === FALSE)
		exit(2);
	if(!($res instanceof AuthCredentials)
			|| $res->getUserID() != $user->getUserID()
			|| $res->getUsername() != $user->getUsername())
		exit(2);
	$error = 'Unknown error';
}

function user_lock($engine, $user)
{
	if($user->lock($engine, $error) === FALSE)
	{
		print("$error\n");
		exit(3);
	}
}

function user_unlock($engine, $user)
{
	if($user->unlock($engine, $error) === FALSE)
	{
		print("$error\n");
		exit(4);
	}
}

function user_reset($engine, $user, $module)
{
	if(User::reset($engine, $module, $user->getUsername(),
			$user->getEmail(), $error) === FALSE)
	{
		print("$error\n");
		exit(5);
	}
}

function user_register($engine, $module)
{
	if(User::register($engine, $module, 'test', FALSE, 'root@localhost',
			FALSE, $error) === FALSE)
	{
		print("$error\n");
		exit(6);
	}
}

function user_setgroup($engine, $user)
{
	if($user->setGroup($engine, 0, $error) === FALSE)
	{
		print("$error\n");
		exit(7);
	}
	if($user->setGroup($engine, 1, $error) !== FALSE)
	{
		print("$error\n");
		exit(8);
	}
}

function user_addgroup($engine, $user)
{
	if($user->addGroup($engine, 0, $error) === FALSE)
	{
		print("$error\n");
		exit(9);
	}
	if($user->removeGroup($engine, 0, $error) === FALSE)
	{
		print("$error\n");
		exit(10);
	}
	if($user->addGroup($engine, 0, $error) === FALSE)
	{
		print("$error\n");
		exit(11);
	}
}

function user_delete($engine, $user)
{
	if($user->delete($engine, $error) === FALSE)
	{
		print("$error\n");
		exit(12);
	}
}

function test($engine)
{
	$user = new User($engine, 1, 'admin');
	$module = Module::load($engine, 'user');

	user_authenticate($engine, $user);
	user_lock($engine, $user);
	user_unlock($engine, $user);
	user_reset($engine, $user, $module);
	user_register($engine, $module);
	user_setgroup($engine, $user);
	user_addgroup($engine, $user);
	user_delete($engine, $user);
}

test($engine);
exit(0);

?>
