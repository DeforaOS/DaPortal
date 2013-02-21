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



require_once('./engines/cli.php');


//CliHTTPEngine
class CliHTTPEngine extends CliEngine
{
	//essential
	//CliHTTPEngine::match
	public function match()
	{
		//never match by default
		return 0;
	}


	//CliHTTPEngine::attach
	public function attach()
	{
		global $config;

		parent::attach();
		if(($_SERVER['SERVER_NAME'] = $config->getVariable(
				'engine::clihttp', 'hostname')) === FALSE)
			$_SERVER['SERVER_NAME'] = gethostname();
		$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
		if(($_SERVER['SERVER_PORT'] = $config->getVariable(
				'engine::clihttp', 'port')) === FALSE
				|| !is_numeric($_SERVER['SERVER_PORT']))
			$_SERVER['SERVER_PORT'] = 80;
		if($config->getVariable('engine::clihttp', 'ssl'))
			$_SERVER['HTTPS'] = 'on';
	}


	//accessors
	//Engine::getUrl
	public function getUrl($request, $absolute = TRUE)
	{
		global $config;

		if($config->getVariable('engine::clihttp', 'friendly'))
		{
			require_once('./engines/httpfriendly.php');
			return HTTPFriendlyEngine::getUrl($request, $absolute);
		}
		else
		{
			require_once('./engines/http.php');
			return HTTPEngine::getUrl($request, $absolute);
		}
	}
}

?>
