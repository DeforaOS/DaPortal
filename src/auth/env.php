<?php //$Id$
//Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org>
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



//EnvAuth
class EnvAuth extends UnixAuth
{
	//protected
	//methods
	//EnvAuth::match
	protected function match(Engine $engine)
	{
		if(getenv('DAPORTAL_USERNAME') !== FALSE)
			return 2;
		return parent::match($engine);
	}


	//EnvAuth::attach
	protected function attach(Engine $engine)
	{
		if(($username = getenv('DAPORTAL_USERNAME')) === FALSE)
			return parent::attach($engine);
		if(($user = User::lookup($engine, $username)) === FALSE)
			return TRUE;
		$cred = new AuthCredentials($user->getUserID(),
				$user->getUsername(), $user->getGroupID(),
				$user->getGroupname(), $user->isAdmin());
		$this->setCredentials($engine, $cred);
		return TRUE;
	}
}

?>
