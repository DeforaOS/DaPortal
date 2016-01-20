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


//functions
//load the test database
if(($database = $engine->getDatabase()) === FALSE)
	exit(2);


function getUserID(Database $database, $username)
{
	$query = 'SELECT user_id
		FROM daportal_user
		WHERE enabled=:enabled
		AND username=:username
		ORDER BY user_id ASC';
	$args = array('enabled' => TRUE, 'username' => $username);
	if(($res = $database->query(NULL, $query, $args)) === FALSE
			|| count($res) != 1)
		return FALSE;
	$res = $res->current();
	return $res['user_id'];
}

function setUserID(Database $database, $username, $uid)
{
	$query = 'UPDATE daportal_user
		SET user_id=:uid
		WHERE enabled=:enabled
		AND username=:username';
	$args = array('uid' => $uid, 'enabled' => TRUE,
		'username' => $username);
	return ($database->query(NULL, $query, $args) !== FALSE)
		? TRUE : FALSE;
}

if(($uid = getUserID($database, 'admin')) != 1)
	exit(3);

//simple transaction (commit)
$uid = 3;
if($database->transactionBegin() === FALSE)
	exit(4);
if(setUserID($database, 'admin', $uid) === FALSE)
	exit(5);
if($database->transactionCommit() === FALSE)
	exit(6);
if(getUserID($database, 'admin') != $uid)
	exit(7);

//simple transaction (rollback)
if($database->transactionBegin() === FALSE)
	exit(8);
if(setUserID($database, 'admin', $uid + 1) === FALSE)
	exit(9);
if($database->transactionRollback() === FALSE)
	exit(10);
if(getUserID($database, 'admin') != $uid)
	exit(11);

//nested transaction (rollback)
if($database->transactionBegin() === FALSE)
	exit(12);
if($database->transactionBegin() === FALSE)
	exit(13);
if(setUserID($database, 'admin', $uid + 1) === FALSE)
	exit(14);
if($database->transactionCommit() === FALSE)
	exit(15);
if($database->transactionRollback() === FALSE)
	exit(16);
if(getUserID($database, 'admin') != $uid)
	exit(17);

//nested transaction (commit)
if($database->transactionBegin() === FALSE)
	exit(18);
if($database->transactionBegin() === FALSE)
	exit(19);
if(setUserID($database, 'admin', $uid + 1) === FALSE)
	exit(20);
if($database->transactionCommit() === FALSE)
	exit(21);
if($database->transactionCommit() === FALSE)
	exit(22);
if(getUserID($database, 'admin') != ++$uid)
	exit(23);

exit(0);

?>
