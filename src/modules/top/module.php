<?php //$Id$
//Copyright (c) 2011-2016 Pierre Pronchery <khorben@defora.org>
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



//TopModule
class TopModule extends Module
{
	//public
	//methods
	//TopModule::call
	function call(Engine $engine, Request $request, $internal = 0)
	{
		if($internal)
			return FALSE;
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		switch($action)
		{
			case 'admin':
			case 'default':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//protected
	//methods
	//calls
	//TopModule::callAdmin
	protected function callAdmin(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
			return $engine->log('LOG_ERR', 'Permission denied');
		$title = _('Top links administration');
		//FIXME implement
		return FALSE;
	}


	//TopModule::callDefault
	private function callDefault($engine, Request $request = NULL)
	{
		//FIXME implement
		return FALSE;
	}
}

?>
