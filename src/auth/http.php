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



//HTTPAuth
class HTTPAuth extends Auth
{
	//protected
	//methods
	//HTTPAuth::match
	protected function match(Engine $engine)
	{
		if(!isset($_SERVER['SERVER_PROTOCOL']))
			return 0;
		switch($_SERVER['SERVER_PROTOCOL'])
		{
			case 'HTTP/1.1':
			case 'HTTP/1.0':
				break;
			default:
				return 0;
		}
		if(isset($_SERVER['PHP_AUTH_USER']))
			return 100;
		return 1;
	}


	//HTTPAuth::attach
	protected function attach(Engine $engine)
	{
		global $config;
		$protocol = isset($_SERVER['SERVER_PROTOCOL'])
			? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
		$error = $protocol.' 401 Unauthorized';

		if(($realm = $config->get('auth::basic', 'realm'))
				=== FALSE)
			$realm = 'DaPortal';
		if(!isset($_SERVER['PHP_AUTH_USER'])
				|| !isset($_SERVER['PHP_AUTH_PW']))
		{
			//FIXME only up getCredentials()?
			header('WWW-Authenticate: Basic realm="'
					.htmlspecialchars($realm).'"');
			header($error);
			return TRUE;
		}
		if(($db = $engine->getDatabase()) === FALSE)
			return TRUE;
		$username = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		if(($user = User::lookup($engine, $username)) === FALSE
				|| ($cred = $user->authenticate($engine,
					$password)) === FALSE)
		{
			if($config->get('engine::http', 'private'))
			{
				header('WWW-Authenticate: Basic realm="'
						.htmlspecialchars($realm).'"');
				header($error);
			}
			return TRUE;
		}
		$cred = new AuthCredentials($user->getUserID(),
				$user->getUsername(), $user->getGroupID(),
				$user->getGroupname(), $user->isAdmin());
		$this->setCredentials($engine, $cred);
		return TRUE;
	}
}

?>
