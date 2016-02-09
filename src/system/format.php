<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//Format
abstract class Format extends Mutator
{
	//public
	//methods
	//static
	//Format::attachDefault
	public static function attachDefault(Engine $engine, $type = FALSE)
	{
		global $config;
		$ret = FALSE;

		$name = FALSE;
		if($type !== FALSE)
			$name = $config->get("format::$type", 'backend');
		if($name === FALSE)
			$name = $config->get('format', 'backend');
		if($name !== FALSE)
		{
			$class = $name.'Format';
			$ret = new $class($name);
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' (default)');
			$ret->attach($engine, $type);
			return $ret;
		}
		if(($dir = opendir('formats')) === FALSE)
			return FALSE;
		$priority = 0;
		while(($de = readdir($dir)) !== FALSE)
		{
			if(substr($de, -4) != '.php')
				continue;
			$name = substr($de, 0, strlen($de) - 4);
			$class = $name.'Format';
			$format = new $class($name);
			if(($p = $format->match($engine, $type)) <= $priority)
				continue;
			$ret = $format;
			$priority = $p;
		}
		closedir($dir);
		if($ret != FALSE)
		{
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
			$ret->attach($engine, $type);
		}
		else
		{
			$error = 'Could not attach ';
			$error .= ($type !== FALSE)
				? 'formatting backend for '.$type
				: 'the default formatting backend';
			$engine->log('LOG_ERR', $error);
		}
		return $ret;
	}


	//virtual
	abstract public function render(Engine $engine, PageElement $page,
			$filename = FALSE);


	//protected
	//properties
	protected $name = FALSE;


	//methods
	//essential
	//Format::Format
	protected function __construct($name)
	{
		$this->name = $name;
	}


	//virtual
	abstract protected function match(Engine $engine, $type = FALSE);
	abstract protected function attach(Engine $engine, $type = FALSE);


	//accessors
	//Format::configGet
	protected function configGet($variable)
	{
		global $config;

		return $config->get('format::'.$this->name, $variable);
	}
}

?>
