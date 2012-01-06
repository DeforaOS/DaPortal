<?php //$Id$
//Copyright (c) 2011-2012 Pierre Pronchery <khorben@defora.org>
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
	//accessors
	public function getGroupId()
	{
		return $this->gid;
	}


	public function getUserId()
	{
		return $this->uid;
	}


	public function isAdmin()
	{
		return $this->admin;
	}


	public function setGroupId($gid)
	{
		if(!is_numeric($gid))
			return FALSE;
		$this->gid = $gid;
		return TRUE;
	}


	public function setUserId($uid, $admin = FALSE)
	{
		if(!is_numeric($uid))
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
	private $group = FALSE;
	private $groups = array();
	private $admin = FALSE;
}


//Auth
abstract class Auth
{
	//public
	//accessors
	public function getCredentials()
	{
		if($this->credentials === FALSE)
			$this->credentials = new AuthCredentials;
		return $this->credentials;
	}


	//static
	public static function attachDefault(&$engine)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		if(($name = $config->getVariable('auth', 'backend')) !== FALSE)
		{
			$res = require_once('./auth/'.$name.'.php');
			if($res === FALSE)
				return FALSE;
			$name = ucfirst($name).'Auth';
			$ret = new $name();
			$ret->attach();
			return $ret;
		}
		if(($dir = opendir('auth')) === FALSE)
			return FALSE;
		while(($de = readdir($dir)) !== FALSE)
		{
			if(substr($de, -4) != '.php')
				continue;
			require_once('./auth/'.$de);
			$name = substr($de, 0, strlen($de) - 4);
			$name = ucfirst($name).'Auth';
			$auth = new $name();
			if(($p = $auth->match($engine)) <= $priority)
				continue;
			$ret = $auth;
			$priority = $p;
		}
		closedir($dir);
		if($ret != FALSE)
		{
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
			$ret->attach($engine);
		}
		return $ret;
	}


	//protected
	//methods
	protected function setCredentials($credentials)
	{
		$this->credentials = $credentials;
	}


	//virtual
	abstract protected function match(&$engine);
	abstract protected function attach(&$engine);


	//private
	//properties
	protected $credentials = FALSE;
}

?>
