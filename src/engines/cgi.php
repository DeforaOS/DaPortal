<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



//CGIEngine
class CGIEngine extends HTTPEngine
{
	//essential
	//CGIEngine::match
	public function match()
	{
		if(php_sapi_name() == 'cgi-fcgi')
			return 99;
		if(isset($_REQUEST) && isset($_SERVER['PHP_SELF']))
			return 99;
		return 0;
	}


	//CGIEngine::attach
	public function attach()
	{
		$keys = array_keys($_REQUEST);

		if(!isset($_SERVER['REQUEST_METHOD']))
		{
			if(isset($_GET))
				$_SERVER['REQUEST_METHOD'] = 'GET';
		}
		if(!isset($_SERVER['SCRIPT_FILENAME']))
			//XXX may be wrong
			$_SERVER['SCRIPT_FILENAME'] = str_replace('_', '.',
					$keys[0]);
		//XXX these two may be wrong
		if(!isset($_SERVER['SERVER_NAME']))
			$_SERVER['SERVER_NAME'] = php_uname('n');
		if(!isset($_SERVER['SERVER_PORT']))
			$_SERVER['SERVER_PORT'] = 80;
		parent::attach();
	}


	//accessors
	//CGIEngine::getRequest
	public function getRequest()
	{
		//FIXME no longer leak $_REQUEST[0]
		return parent::getRequest();
	}
}

?>
