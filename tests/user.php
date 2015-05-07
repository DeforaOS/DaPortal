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
$user = new User($engine, 1, 'admin');
if(($res = $user->authenticate($engine, 'password')) === FALSE)
	exit(2);
if(!($res instanceof AuthCredentials)
		|| $res->getUserID() != $user->getUserID()
		|| $res->getUsername() != $user->getUsername())
	exit(2);
$error = 'Unknown error';
if($user->lock($engine, $error) === FALSE)
{
	print("$error\n");
	exit(3);
}
if($user->unlock($engine, $error) === FALSE)
{
	print("$error\n");
	exit(4);
}
$module = Module::load($engine, 'user');
if(User::reset($engine, $module, $user->getUsername(), $user->getEmail(),
		$error) === FALSE)
{
	print("$error\n");
	exit(5);
}
exit(0);

?>
