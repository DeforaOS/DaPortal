<?php //$Id$
//Copyright (c) 2015-2016 Pierre Pronchery <khorben@defora.org>
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



abstract class Engine
{
	abstract public function match();
	abstract public function attach();

	static public function attachDefault($prefix = FALSE)
	{
		global $config;

		$config = new Config();
		$ret = new DummyEngine();
		$ret->attach();
		return $ret;
	}

	public function log($priority, $message)
	{
		error_log($message, 0);
		return FALSE;
	}

	public function process(Request $request, $internal = FALSE)
	{
		return new StringResponse();
	}

	public function render(Response $response)
	{
		return $response->getCode();
	}
}

require_once('./tests.php');
if(($objdir = getenv('OBJDIR')) !== FALSE)
	$objdir .= '../src/';
else
	$objdir = '';
require_once($objdir.'daportal.php');


exit(0);

?>
