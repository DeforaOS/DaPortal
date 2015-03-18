<?php //$Id$
//Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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
exit(0);

?>
