<?php //$Id$
//Copyright (c) 2011-2014 Pierre Pronchery <khorben@defora.org>
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



//AuthCredentials
class AuthCredentials
{
	//public
	//essential
	public function __construct($uid = FALSE, $username = FALSE,
			$gid = FALSE, $groupname = FALSE, $admin = FALSE)
	{
		if($uid === FALSE || !is_string($username) || !is_bool($admin))
			return;
		$this->setUserID($uid, $admin);
		if($gid === FALSE)
			return;
		$this->setGroupID($gid);
		$this->username = $username;
		$this->groupname = $groupname;
	}


	//accessors
	//AuthCredentials::getGroupID
	public function getGroupID()
	{
		return $this->gid;
	}


	//AuthCredentials::getGroupname
	public function getGroupname()
	{
		return $this->groupname;
	}


	//AuthCredentials::getUserID
	public function getUserID()
	{
		return $this->uid;
	}


	//AuthCredentials::getUsername
	public function getUsername()
	{
		return $this->username;
	}


	//AuthCredentials::isAdmin
	public function isAdmin()
	{
		return $this->admin;
	}


	//AuthCredentials::isMember
	public function isMember(Engine $engine, $groupname)
	{
		if(($user = User::lookup($engine, $this->username,
				$this->uid)) === FALSE)
			return FALSE;
		return $user->isMember($engine, $groupname);
	}


	//AuthCredentials::setGroupID
	public function setGroupID($gid)
	{
		if(!is_numeric($gid))
			return FALSE;
		$this->gid = $gid;
		return TRUE;
	}


	//AuthCredentials::setUserID
	public function setUserID($uid, $admin = FALSE)
	{
		if(!is_numeric($uid) || !is_bool($admin))
		{
			$this->uid = 0;
			$this->admin = FALSE;
			return FALSE;
		}
		$this->uid = $uid;
		$this->admin = $admin;
		return TRUE;
	}


	//private
	//properties
	private $uid = 0;
	private $username = FALSE;
	private $gid = 0;
	private $groupname = FALSE;
	private $groups = array();
	private $admin = FALSE;
}

?>
