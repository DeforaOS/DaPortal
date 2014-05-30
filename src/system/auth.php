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
	public function isMember($engine, $groupname)
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


//Auth
abstract class Auth
{
	//public
	//accessors
	//Auth::getCredentials
	public function getCredentials($engine)
	{
		if($this->credentials === FALSE)
			$this->credentials = new AuthCredentials;
		return $this->credentials;
	}


	//Auth::getVariable
	public function getVariable($engine, $variable)
	{
		$credentials = $this->getCredentials($engine);
		$database = $engine->getDatabase();
		$query = $this->query_variable_get;
		$args = array('user_id' => $credentials->getUserID(),
			'variable' => $variable);

		if($args['user_id'] == 0)
			//variables are only allowed for authenticated users
			return FALSE;
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return unserialize($res[0]['value']);
	}


	//Auth::setIdempotent
	public function setIdempotent($engine, $request, $idempotent)
	{
		$request->setIdempotent($idempotent);
	}


	//Auth::setCredentials
	public function setCredentials($engine, $credentials)
	{
		$this->credentials = $credentials;
		return TRUE;
	}


	//Auth::setVariable
	public function setVariable($engine, $variable, $value)
	{
		$credentials = $this->getCredentials($engine);
		$database = $engine->getDatabase();
		$query = $this->query_variable_set;
		$args = array('user_id' => $credentials->getUserID(),
			'variable' => $variable);

		if($args['user_id'] == 0)
			//variables are only allowed for authenticated users
			return FALSE;
		if($value === FALSE)
			$query = $this->query_variable_remove;
		else if(($v = $this->getVariable($engine, $variable)) === FALSE)
		{
			//this variable is not in the database
			$query = $this->query_variable_add;
			$args['value'] = serialize($value);
		}
		else if($v != $value)
			//update the variable in the database
			$args['value'] = serialize($value);
		else
			//no need to issue any query
			return TRUE;
		return ($database->query($engine, $query, $args) !== FALSE)
			? TRUE : FALSE;
	}


	//static
	//Auth::attachDefault
	public static function attachDefault($engine)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		if(($name = $config->get('auth', 'backend')) !== FALSE)
		{
			$res = require_once('./auth/'.$name.'.php');
			if($res === FALSE)
				return FALSE;
			$name .= 'Auth';
			$ret = new $name();
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' (default)');
			$ret->attach($engine);
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
			$name .= 'Auth';
			$auth = new $name();
			if(($p = $auth->match($engine)) <= $priority)
				continue;
			$ret = $auth;
			$priority = $p;
		}
		closedir($dir);
		if($ret !== FALSE)
		{
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
			$ret->attach($engine);
		}
		return $ret;
	}


	//protected
	//properties
	protected $credentials = FALSE;
	//queries
	protected $query_variable_add = 'INSERT INTO daportal_auth_variable
		(user_id, variable, value)
		VALUES (:user_id, :variable, :value)';
	protected $query_variable_get = 'SELECT value
		FROM daportal_auth_variable
		WHERE user_id=:user_id AND variable=:variable';
	protected $query_variable_remove = 'DELETE FROM daportal_auth_variable
		WHERE user_id=:user_id AND variable=:variable';
	protected $query_variable_set = 'UPDATE daportal_auth_variable
		SET value=:value
		WHERE user_id=:user_id AND variable=:variable';


	//methods
	//virtual
	abstract protected function match($engine);
	abstract protected function attach($engine);
}

?>
