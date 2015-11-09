<?php //$Id$
//Copyright (c) 2011-2015 Pierre Pronchery <khorben@defora.org>
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



//UserBackend
abstract class UserBackend
{
	//public
	//methods
	//essential
	//UserBackend::UserBackend
	public function __construct(Engine $engine, $uid, $username = FALSE)
	{
		$this->engine = $engine;
		$this->user_id = $uid;
		if($username !== FALSE)
			$this->username = $username;
	}


	//accessors
	//UserBackend::getEmail
	public function getEmail()
	{
		return $this->email;
	}


	//UserBackend::getFullname
	public function getFullname()
	{
		return $this->fullname;
	}


	//UserBackend::getGroupID
	public function getGroupID()
	{
		return $this->group_id;
	}


	//UserBackend::getGroupname
	public function getGroupname()
	{
		return $this->groupname;
	}


	//UserBackend::getRequest
	public function getRequest($module, $action = FALSE,
			$properties = FALSE)
	{
		return new Request($module, $action, $this->getUserID(),
			$this->getUsername(), $properties);
	}


	//UserBackend::getUserID
	public function getUserID()
	{
		return $this->user_id;
	}


	//UserBackend::getUsername
	public function getUsername()
	{
		return $this->username;
	}


	//UserBackend::isAdmin
	public function isAdmin()
	{
		return $this->admin;
	}


	//UserBackend::isEnabled
	public function isEnabled()
	{
		return $this->enabled;
	}


	//UserBackend::isLocked
	public function isLocked()
	{
		return $this->locked;
	}


	//UserBackend::isMember
	abstract public function isMember(Engine $engine, $group);


	//UserBackend::setGroup
	abstract public function setGroup(Engine $engine, $group_id,
			&$error = FALSE);


	//UserBackend::setPassword
	abstract public function setPassword(Engine $engine, $password,
			&$error = FALSE);


	//useful
	abstract public function addGroup(Engine $engine, $group_id,
			&$error = FALSE);
	abstract public function authenticate(Engine $engine, $password);
	abstract public function delete(Engine $engine, &$error = FALSE);
	abstract public function disable(Engine $engine, &$error = FALSE);
	abstract public function enable(Engine $engine, &$error = FALSE);
	abstract public function lock(Engine $engine, &$error = FALSE);
	abstract public function removeGroup(Engine $engine, $group_id,
			&$error = FALSE);
	abstract public function removeGroups(Engine $engine, &$error = FALSE);
	abstract public function removeRegister(Engine $engine, &$error = FALSE);
	abstract public function removeReset(Engine $engine, &$error = FALSE);
	abstract public function unlock(Engine $engine, &$error = FALSE);


	//protected
	//properties
	protected $engine;
	protected $user_id = 0;
	protected $username = 'username';
	protected $group_id = 0;
	protected $groupname = 'nogroup';
	protected $enabled = FALSE;
	protected $locked = TRUE;
	protected $admin = FALSE;
	protected $email = FALSE;
	protected $fullname = FALSE;
}


//User
class User extends SQLUserBackend
{
}

?>
