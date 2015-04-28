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



//User
class User
{
	//public
	//methods
	//essential
	//User::User
	public function __construct($engine, $uid, $username = FALSE)
	{
		$db = $engine->getDatabase();
		$query = static::$query_get_by_id;
		$args = array('user_id' => $uid);

		if($username !== FALSE)
		{
			if($engine instanceof HTTPFriendlyEngine)
			{
				//XXX workaround for friendly titles
				$query .= ' AND username '
					.$db->like().' :username';
				$username = str_replace('-', '_', $username);
			}
			else
				$query = static::$query_get_by_id_username;
			$args['username'] = $username;
		}
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return;
		$res = $res->current();
		$this->user_id = $res['id'];
		$this->username = $res['username'];
		$this->enabled = $db->isTrue($res['enabled']);
		$this->locked = ($res['locked'] == '!');
		$this->admin = $db->isTrue($res['admin']);
		$this->email = $res['email'];
		$this->fullname = $res['fullname'];
	}


	//accessors
	//User::getEmail
	public function getEmail()
	{
		return $this->email;
	}


	//User::getFullname
	public function getFullname()
	{
		return $this->fullname;
	}


	//User::getGroupID
	public function getGroupID()
	{
		return $this->group_id;
	}


	//User::getGroupname
	public function getGroupname()
	{
		return $this->groupname;
	}


	//User::getRequest
	public function getRequest($module, $action = FALSE, $properties = FALSE)
	{
		return new Request($module, $action, $this->getUserID(),
			$this->getUsername(), $properties);
	}


	//User::getUserID
	public function getUserID()
	{
		return $this->user_id;
	}


	//User::getUsername
	public function getUsername()
	{
		return $this->username;
	}


	//User::isAdmin
	public function isAdmin()
	{
		return $this->admin;
	}


	//User::isEnabled
	public function isEnabled()
	{
		return $this->enabled;
	}


	//User::isLocked
	public function isLocked()
	{
		return $this->locked;
	}


	//User::isMember
	public function isMember($engine, $group)
	{
		$database = $engine->getDatabase();
		$query = static::$query_member;
		$args = array('user_id' => $this->user_id,
			'groupname' => $group);

		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return TRUE;
	}


	//User::setEnabled
	public function setEnabled($engine, $enabled)
	{
		$db = $engine->getDatabase();
		$query = static::$query_set_enabled;
		$args = array('user_id' => $this->user_id,
			'enabled' => $enabled ? 1 : 0);

		return ($db->query($engine, $query, $args) !== FALSE);
	}


	//User::setPassword
	public function setPassword($engine, $password)
	{
		$db = $engine->getDatabase();
		$query = static::$query_set_password;

	       	//XXX seems to default to sh-md5 (should be configurable)
		$hash = crypt($password);
		$args = array('user_id' => $this->user_id,
			'password' => $hash);
		return ($db->query($engine, $query, $args) !== FALSE);
	}


	//useful
	//User::authenticate
	public function authenticate($engine, $password)
	{
		$db = $engine->getDatabase();
		$query = static::$query_authenticate;
		$args = array('username' => $this->username);

		//obtain the password hash
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return $engine->log('LOG_ERR', $this->username
					.': Could not obtain the password hash');
		$res = $res->current();
		if(strlen($res['password']) > 0 && $res['password'][0] == '$')
		{
			//the password is salted
			$a = explode('$', $res['password']);
			$cipher = $a[1];
			switch($cipher)
			{
				case '1':
				case '2a':
				case '5':
				case '6':
					$hash = crypt($password,
							$res['password']);
					break;
				default:
					$error = $this->username
						.': Unsupported cipher';
					return $engine->log('LOG_ERR', $error);
			}
		}
		else if(strlen($res['password']) == 32)
		{
			//the password is not salted (plain MD5)
			$hash = md5($password);
			//if it matches, hash it and save it again
			if($res['password'] == $hash)
				$this->setPassword($engine, $password);
		}
		else if(strlen($res['password']) > 0 && $res['password'][0] == '!')
			return $engine->log('LOG_ERR', $this->username
					.': User is locked');
		else
			return $engine->log('LOG_ERR', $this->username
					.': Invalid password hash');
		if($res['password'] != $hash)
			return $engine->log('LOG_ERR', $this->username
					.': Could not authenticate user');
		//the password is correct
		return new AuthCredentials($res['user_id'], $res['username'],
				$res['group_id'], $res['groupname'],
				$db->isTrue($res['admin']));
	}


	//User::delete
	public function delete($engine)
	{
		$db = $engine->getDatabase();
		$query = static::$query_delete;
		$args = array('user_id' => $this->user_id);

		if($this->user_id === FALSE)
			return TRUE;
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| $res->getAffectedCount() != 1)
			return FALSE;
		$this->user_id = FALSE;
		$this->username = FALSE;
		$this->group_id = FALSE;
		$this->groupname = FALSE;
		$this->enabled = FALSE;
		$this->locked = FALSE;
		$this->admin = FALSE;
		$this->email = FALSE;
		$this->fullname = FALSE;
		return TRUE;
	}


	//User::disable
	public function disable($engine)
	{
		$db = $engine->getDatabase();
		$query = static::$query_disable;
		$args = array('user_id' => $this->user_id);

		if($this->enabled === FALSE)
			return TRUE;
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| $res->getAffectedCount() != 1)
			return FALSE;
		$this->enabled = FALSE;
		return TRUE;
	}


	//User::enable
	public function enable($engine)
	{
		$db = $engine->getDatabase();
		$query = static::$query_enable;
		$args = array('user_id' => $this->user_id);

		if($this->enabled !== FALSE)
			return TRUE;
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| $res->getAffectedCount() != 1)
			return FALSE;
		$this->enabled = TRUE;
		return TRUE;
	}


	//User::lock
	public function lock($engine, &$error = FALSE)
	{
		$db = $engine->getDatabase();
		$query = static::$query_lock;
		$args = array('user_id' => $this->user_id);

		if($this->locked !== FALSE)
			return TRUE;
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| $res->getAffectedCount() != 1)
		{
			$error = $this->username.': Could not lock user';
			return FALSE;
		}
		$this->locked = TRUE;
		return TRUE;
	}


	//User::unlock
	public function unlock($engine, &$error = FALSE)
	{
		$db = $engine->getDatabase();
		$query = static::$query_unlock;
		$args = array('user_id' => $this->user_id);

		if($this->locked === FALSE)
			return TRUE;
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| $res->getAffectedCount() != 1)
		{
			$error = $this->username.': Could not unlock user';
			return FALSE;
		}
		$this->locked = FALSE;
		return TRUE;
	}


	//static
	//useful
	//User::insert
	static public function insert($engine, $username, $fullname, $password,
		$email, $enabled = FALSE, $admin = FALSE, &$error = FALSE)
	{
		//FIXME code duplication with User::register()
		$db = $engine->getDatabase();
		$query = static::$query_insert;
		$error = '';

		//FIXME really validate username
		if(!is_string($username) || strlen($username) == 0)
			$error .= _("The username is not valid\n");
		if($fullname === FALSE)
			$fullname = '';
		//FIXME really validate e-mail
		if(strchr($email, '@') === FALSE)
			$error .= _("The e-mail address is not valid\n");
		//FIXME verify that the username and e-mail are both unique
		if(strlen($error) > 0)
			return FALSE;
		if($password === FALSE || strlen($password) == 0)
			$password = '';
		else
			$password = crypt($password);
		$args = array('username' => $username, 'fullname' => $fullname,
			'password' => $password, 'email' => $email,
			'enabled' => $enabled ? 1 : 0,
		       	'admin' => $admin ? 1 : 0);
		$res = $db->query($engine, $query, $args);
		if($res === FALSE || ($uid = $db->getLastID($engine,
						'daportal_user', 'user_id'))
				=== FALSE)
		{
			$error = _('Could not insert the user');
			return FALSE;
		}
		$user = new User($engine, $uid);
		if($user->getUserID() === FALSE)
		{
			$error = _('Could not insert the user');
			return FALSE;
		}
		$error = '';
		return $user;
	}


	//User::lookup
	static public function lookup($engine, $username, $user_id = FALSE)
	{
		static $cache = array();
		$db = $engine->getDatabase();
		$query = static::$query_get_by_username;
		$args = array('username' => $username);

		if(isset($cache[$username]))
		{
			if($user_id !== FALSE && $cache[$username]->getUserID()
					!= $user_id)
				return FALSE;
			return $cache[$username];
		}
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		$res = $res->current();
		$cache[$username] = new User($engine, $res['id'], $username);
		if($user_id !== FALSE && $cache[$username]->getUserID()
				!= $user_id)
			return FALSE;
		return $cache[$username];
	}


	//User::password_new
	static public function password_new()
	{
		$string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
			.'0123456789';
		$password = '';

		for($i = 0; $i < 8; $i++)
			$password .= $string[rand(0, strlen($string) - 1)];
		return $password;
	}


	//User::register
	static public function register($engine, $module, $username, $password,
		$email, $enabled = FALSE, &$error = FALSE)
	{
		$db = $engine->getDatabase();
		$error = '';

		//FIXME really validate username
		if(!is_string($username) || strlen($username) == 0)
			$error .= _("The username is not valid\n");
		//FIXME really validate e-mail
		if(strchr($email, '@') === FALSE)
			$error .= _("The e-mail address is not valid\n");
		//FIXME verify that the username and e-mail are both unique
		if(strlen($error) > 0)
			return FALSE;
		if($db->transactionBegin($engine) === FALSE)
		{
			$error = _('Could not register the user');
			return FALSE;
		}
		$query = static::$query_register;
		$args = array('username' => $username, 'email' => $email,
			'enabled' => $enabled ? 1 : 0);
		$res = $db->query($engine, $query, $args);
		if($res === FALSE || ($uid = $db->getLastID($engine,
						'daportal_user', 'user_id'))
				=== FALSE)
		{
			$db->transactionRollback($engine);
			$error = _('Could not register the user');
			return FALSE;
		}
		$user = User::lookup($engine, $username, $uid);
		if($user === FALSE || $user->getUserID() == 0)
		{
			$db->transactionRollback($engine);
			$error = _('Could not register the user');
			return FALSE;
		}
		if($enabled === FALSE)
		{
			$query = static::$query_register_token;
			//let the user confirm registration
			if($password === FALSE)
				//generate a random password
				$password = User::password_new();
			//generate a token
			$token = sha1(uniqid($password, TRUE));
			$args = array('user_id' => $uid, 'token' => $token);
			if($user->setPassword($engine, $password) === FALSE
				|| $db->query($engine, $query, $args) === FALSE)
			{
				$db->transactionRollback($engine);
				$error = _('Could not register the user');
				return FALSE;
			}
			//send an e-mail for confirmation
			$r = new Request($module, 'validate', $uid, FALSE,
				array('token' => $token));
			$subject = _('User registration'); //XXX add site title
			$text = _("Thank you for registering on this site.\n");
			//FIXME do not send the password if already known
			$text .= _("\nYour password is: ").$password."\n";
			$text .= _("\nPlease click on the following link to enable your account:\n");
			$text .= $engine->getURL($r)."\n";
			$text .= _("Please note that this link will expire in 7 days.\n");
			$content = new PageElement('label', array(
				'text' => $text));
			Mail::send($engine, FALSE, $email, $subject, $content);
		}
		$db->transactionCommit($engine);
		$error = '';
		return $user;
	}


	//User::reset
	static public function reset($engine, $module, $username, $email,
			&$error = FALSE)
	{
		$db = $engine->getDatabase();

		//we can ignore errors
		static::resetCleanup($engine);
		//verify the username and e-mail address
		$query = static::$query_reset_validate;
		$args = array('username' => $username, 'email' => $email);
		$res = $db->query($engine, $query, $args);
		if($res === FALSE || count($res) != 1)
		{
			//XXX consider silently failing (to avoid bruteforcing)
			$error = _('Could not reset the password');
			return FALSE;
		}
		$res = $res->current();
		$query = static::$query_reset_token;
		$uid = $res['user_id'];
		//generate a token
		$token = sha1(uniqid($uid.$username.$email, TRUE));
		$args = array('user_id' => $uid, 'token' => $token);
		if(($res = $db->query($engine, $query, $args)) === FALSE)
		{
			$error = _('Could not reset the password');
			return FALSE;
		}
		//send an e-mail with the token
		$r = new Request($module, 'reset', $uid, FALSE,
			array('token' => $token));
		$subject = _('Password reset'); //XXX add site title
		$text = _("Someone, hopefully you, has requested a password reset on your account.\n");
		$text .= _("\nPlease click on the following link to reset your password:\n");
		$text .= $engine->getURL($r)."\n";
		$text .= _("Please note that this link will expire in 24 hours.\n");
		$content = new PageElement('label', array('text' => $text));
		if(Mail::send($engine, FALSE, $email, $subject, $content)
				=== FALSE)
		{
			$error = _('Could not send the confirmation e-mail');
			return FALSE;
		}
		return TRUE;
	}


	//User::resetPassword
	static public function resetPassword($engine, $uid, $password, $token,
			&$error = FALSE)
	{
		$db = $engine->getDatabase();
		$error = _('Could not reset the password');

		if($db->transactionBegin($engine) === FALSE)
			return FALSE;
	       	//delete password reset requests older than one day
		if(static::resetCleanup($engine) === FALSE)
		{
			$db->transactionRollback($engine);
			return FALSE;
		}
		//lookup the token
		$query = static::$query_reset_validate_token;
		$args = array('user_id' => $uid, 'token' => $token);
		$res = $db->query($engine, $query, $args);
		if($res === FALSE || count($res) != 1)
		{
			$db->transactionRollback($engine);
			return FALSE;
		}
		$user = new User($engine, $uid);
		if($user->setPassword($engine, $password) === FALSE)
		{
			$db->transactionRollback($engine);
			return FALSE;
		}
		$query = static::$query_reset_delete;
		$args = array('user_id' => $uid, 'token' => $token);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$db->transactionRollback($engine);
			return FALSE;
		}
		if($db->transactionCommit($engine) === FALSE)
			return FALSE;
		$error = '';
		return FALSE;
	}


	//User::validate
	static public function validate($engine, $uid, $token, &$error = FALSE)
	{
		$db = $engine->getDatabase();
		$error = '';

		if($uid === FALSE || !is_numeric($uid))
			$error .= _("Unknown user ID\n");
		if($token === FALSE)
			$error .= _("The token must be specified\n");
		if(strlen($error) > 0)
			return FALSE;
		//delete registrations older than one week
		$query = static::$query_register_cleanup;
		$timestamp = strftime(static::$timestamp_format, time() - 604800);
		$args = array('timestamp' => $timestamp);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$error = _("Could not validate the user\n");
			return FALSE;
		}
		$query = static::$query_register_validate;
		$args = array('user_id' => $uid, 'token' => $token);
		$res = $db->query($engine, $query, $args);
		if($res === FALSE || count($res) != 1)
		{
			$error = _('Could not validate the user');
			return FALSE;
		}
		$res = $res->current();
		if($db->transactionBegin($engine) === FALSE)
		{
			$error = _('Could not validate the user');
			return FALSE;
		}
		$query = static::$query_register_delete;
		$args = array('user_register_id' => $res['user_register_id']);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$db->transactionRollback($engine);
			$error = _('Could not validate the user');
			return FALSE;
		}
		$query = static::$query_register_delete;
		$args = array('user_register_id' => $res['user_register_id']);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$db->transactionRollback($engine);
			$error = _('Could not validate the user');
			return FALSE;
		}
		$user = new User($engine, $res['user_id']);
		if($user->setEnabled($engine, TRUE) === FALSE
				|| $db->transactionCommit($engine) === FALSE)
		{
			$db->transactionRollback($engine);
			$error = _('Could not enable the user');
			return FALSE;
		}
		return $user;
	}


	//protected
	//properties
	//queries
	//IN:	username
	static protected $query_authenticate = "SELECT user_id, username,
		daportal_user.group_id AS group_id, groupname, admin, password
		FROM daportal_user
		LEFT JOIN daportal_group
		ON daportal_user.group_id=daportal_group.group_id
		WHERE username=:username
		AND daportal_user.enabled='1'
		AND daportal_group.enabled='1'";
	//IN:	user_id
	static protected $query_get_by_id = "SELECT user_id AS id, username,
		daportal_user.enabled AS enabled,
		substr(password, 1, 1) AS locked,
		daportal_user.group_id AS group_id, groupname, admin, email,
		fullname
		FROM daportal_user
		LEFT JOIN daportal_group
		ON daportal_user.group_id=daportal_group.group_id
		WHERE daportal_group.enabled='1'
		AND user_id=:user_id";
	//IN:	user_id
	//	username
	static protected $query_get_by_id_username = "SELECT user_id AS id,
		username, daportal_user.enabled AS enabled,
		substr(password, 1, 1) AS locked,
		daportal_user.group_id AS group_id, groupname, admin, email,
		fullname
		FROM daportal_user
		LEFT JOIN daportal_group
		ON daportal_user.group_id=daportal_group.group_id
		WHERE daportal_group.enabled='1'
		AND user_id=:user_id
		AND username=:username";
	//IN:	user_id
	//	groupname
	static protected $query_member = "SELECT user_id,
		daportal_group.group_id AS group_id
		FROM daportal_user_group, daportal_group
		WHERE daportal_user_group.group_id=daportal_group.group_id
		AND user_id=:user_id
		AND groupname=:groupname
		AND enabled='1'";
	static protected $query_set_password = 'UPDATE daportal_user
		SET password=:password
		WHERE user_id=:user_id';
	static protected $query_set_enabled = "UPDATE daportal_user
		SET enabled=:enabled
		WHERE user_id=:user_id";
	//IN:	user_id
	static protected $query_delete = 'DELETE FROM daportal_user
		WHERE user_id=:user_id';
	//IN:	user_id
	static protected $query_disable = "UPDATE daportal_user
		SET enabled='0'
		WHERE user_id=:user_id";
	//IN:	user_id
	static protected $query_enable = "UPDATE daportal_user
		SET enabled='1'
		WHERE user_id=:user_id";
	//IN:	username
	static protected $query_get_by_username = "SELECT user_id AS id
		FROM daportal_user
		WHERE enabled='1' AND username=:username";
	static protected $query_insert = 'INSERT INTO daportal_user
		(username, fullname, password, email, enabled, admin)
		VALUES (:username, :fullname, :password, :email, :enabled,
		:admin)';
	//IN:	user_id
	static protected $query_lock = "UPDATE daportal_user
		SET password=concat('!', password)
		WHERE user_id=:user_id AND substr(password, 1, 1) != '!'";
	static protected $query_register = 'INSERT INTO daportal_user
		(username, email, enabled)
		VALUES (:username, :email, :enabled)';
	static protected $query_register_token = 'INSERT INTO daportal_user_register
		(user_id, token)
		VALUES (:user_id, :token)';
	static protected $query_register_cleanup = 'DELETE FROM daportal_user_register
		WHERE timestamp <= :timestamp';
	static protected $query_register_delete = 'DELETE FROM daportal_user_register
		WHERE user_register_id=:user_register_id';
	static protected $query_register_validate = 'SELECT user_register_id,
		daportal_user.user_id AS user_id, username
		FROM daportal_user, daportal_user_register
		WHERE daportal_user.user_id=daportal_user_register.user_id
		AND daportal_user.user_id=:user_id AND token=:token';
	static protected $query_reset_cleanup = 'DELETE FROM daportal_user_reset
		WHERE timestamp <= :timestamp';
	static protected $query_reset_delete = 'DELETE FROM daportal_user_reset
		WHERE user_id=:user_id AND token=:token';
	static protected $query_reset_token = 'INSERT INTO daportal_user_reset
		(user_id, token)
		VALUES (:user_id, :token)';
	static protected $query_reset_validate = "SELECT user_id
		FROM daportal_user
		WHERE enabled='1' AND username=:username AND email=:email";
	static protected $query_reset_validate_token = "SELECT
		daportal_user.user_id AS user_id, username
		FROM daportal_user, daportal_user_reset
		WHERE daportal_user.user_id=daportal_user_reset.user_id
		AND enabled='1' AND daportal_user.user_id=:user_id
		AND token=:token";
	//IN:	user_id
	static protected $query_unlock = "UPDATE daportal_user
		SET password=substr(password, 2)
		WHERE user_id=:user_id AND substr(password, 1, 1) = '!'";


	//methods
	//useful
	//User::resetCleanup
	static protected function resetCleanup($engine)
	{
		$db = $engine->getDatabase();
		$query = static::$query_reset_cleanup;
		$timestamp = strftime(static::$timestamp_format, time() - 86400);
		$args = array('timestamp' => $timestamp);
		$error = 'Could not clean the password reset database up';

		//delete password reset requests older than one day
		if($db->query($engine, $query, $args) === FALSE)
			return $engine->log('LOG_ERR', $error);
		return TRUE;
	}


	//private
	//properties
	private $user_id = 0;
	private $username = 'username';
	private $group_id = 0;
	private $groupname = 'nogroup';
	private $enabled = FALSE;
	private $locked = TRUE;
	private $admin = FALSE;
	private $email = FALSE;
	private $fullname = FALSE;

	static private $timestamp_format = '%Y-%m-%d %H:%M:%S';
}

?>
