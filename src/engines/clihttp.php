<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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



//CLIHTTPEngine
class CLIHTTPEngine extends CLIEngine
{
	//essential
	//CLIHTTPEngine::match
	public function match()
	{
		//never match by default
		return 0;
	}


	//CLIHTTPEngine::attach
	public function attach()
	{
		global $config;

		parent::attach();
		if(($_SERVER['SERVER_NAME'] = $config->get(
				'engine::clihttp', 'hostname')) === FALSE)
			$_SERVER['SERVER_NAME'] = gethostname();
		$_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
		if(($_SERVER['SERVER_PORT'] = $config->get('engine::clihttp',
				'port')) === FALSE
				|| !is_numeric($_SERVER['SERVER_PORT']))
			$_SERVER['SERVER_PORT'] = 80;
		if($config->get('engine::clihttp', 'ssl'))
			$_SERVER['HTTPS'] = 'on';
	}


	//accessors
	//CLIHTTPEngine::getURL
	public function getURL(Request $request = NULL, $absolute = TRUE)
	{
		global $config;

		if($config->get('engine::clihttp', 'friendly'))
			$engine = new HTTPFriendlyEngine();
		else
			$engine = new HTTPEngine();
		return $engine->getURL($request, $absolute);
	}
}

?>
