<?php //$Id$
//Copyright (c) 2011 Pierre Pronchery <khorben@defora.org>
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



//CliEngine
require_once('./system/engine.php');
class CliEngine extends Engine
{
	protected function match()
	{
		return 1;
	}

	protected function attach()
	{
	}

	public function render($page)
	{
		print_r($page);
	}


	public function getRequest()
	{
		if(($options = getopt('m:a:i:t:')) === FALSE)
			return FALSE;
		$module = FALSE;
		$action = FALSE;
		$id = FALSE;
		$title = FALSE;
		foreach($options as $key => $value)
			switch($key)
			{
				case 'm':
					$module = $options['m'];
					break;
				case 'a':
					$action = $options['a'];
					break;
				case 'i':
					$id = $options['i'];
					break;
				case 't':
					$title = $options['t'];
					break;
			}
		//FIXME also allow parameters to be set
		$ret = new Request($this, $module, $action, $id, $title);
		return $ret;
	}
}

?>
