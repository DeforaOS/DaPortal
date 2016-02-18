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



//CLIEngine
class CLIEngine extends Engine
{
	//public
	//methods
	//accessors
	//CLIEngine::getDefaultType
	public function getDefaultType()
	{
		return 'text/plain';
	}


	//CLIEngine::getRequest
	public function getRequest()
	{
		if(($options = getopt('DM:fm:a:i:O:o:qt:v')) === FALSE)
			return parent::getRequest();
		$idempotent = TRUE;
		$module = FALSE;
		$action = FALSE;
		$id = FALSE;
		$title = FALSE;
		$parameters = array();
		$type = $this->getDefaultType();
		foreach($options as $key => $value)
			switch($key)
			{
				case 'D':
					$this->setDebug(TRUE);
					break;
				case 'M':
					$type = $options['M'];
					break;
				case 'f':
					$idempotent = FALSE;
					break;
				case 'm':
					$module = $options['m'];
					break;
				case 'a':
					$action = $options['a'];
					break;
				case 'i':
					$id = $options['i'];
					break;
				case 'O':
				case 'o':
					if(!is_array($options[$key]))
						$options[$key] = array($options[$key]);
					foreach($options[$key] as $o)
					{
						$o = explode('=', $o);
						if(count($o) < 2)
						{
							$this->usage();
							return FALSE;
						}
						$key = array_shift($o);
						$value = implode('=', $o);
						if(substr($key, -2) == '[]')
						{
							$key = substr($key, 0, -2);
							if(!isset($parameters[$key]))
								$parameters[$key] = array();
							$parameters[$key][] = $value;
						}
						else
							$parameters[$key] = $value;
					}
					break;
				case 'q':
					$this->verbose = 0;
					break;
				case 't':
					$title = $options['t'];
					break;
				case 'v':
					$this->verbose++;
					break;
			}
		$ret = new Request($module, $action, $id, $title, $parameters);
		$ret->setIdempotent($idempotent);
		$ret->setType($type);
		return $ret;
	}


	//essential
	//CLIEngine::match
	public function match()
	{
		if(isset($_SERVER['argc']) && $_SERVER['argv'])
			return 100;
		return 1;
	}


	//CLIEngine::attach
	public function attach()
	{
		DaPortal\Locale::init($this);
	}


	//useful
	//CLIEngine::usage
	protected function usage()
	{
		fputs(STDERR, static::$usage);
	}


	//protected
	//properties
	static protected $usage = "Usage: daportal [-Dfqv][-M mime-type][-m module [-a action][-i ID][-t title]]
                [-o parameter=value...]\n";
}

?>
