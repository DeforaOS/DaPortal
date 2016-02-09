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



//Config
class Config
{
	private $sections = array();


	//methods
	//public
	//essential
	//Config::Config
	public function __construct()
	{
		$this->reset();
	}


	//accessors
	//Config::get
	public function get($section, $name)
	{
		if($section === FALSE)
			$section = '';
		if(!isset($this->sections[$section]))
			return FALSE;
		return $this->sections[$section]->get($name);
	}


	//Config::getSections
	public function getSections()
	{
		return array_keys($this->sections);
	}


	//Config::set
	public function set($section, $name, $value)
	{
		if($section === FALSE)
			$section = '';
		if(!isset($this->sections[$section]))
			$this->sections[$section] = new ConfigSection;
		$this->sections[$section]->set($name, $value);
	}


	//useful
	//Config::load
	public function load($filename)
	{
		$section = '';

		if(($fp = @fopen($filename, 'r')) === FALSE)
			return FALSE;
		for($i = 1; ($line = fgets($fp)) !== FALSE; $i++)
			if($this->loadLine($line, $section) === FALSE)
				//XXX should cancel and return FALSE
				error_log($filename.': Line '.$i
						.' could not be parsed');
		fclose($fp);
		return TRUE;
	}


	//Config::loadString
	public function loadString($string, &$error = FALSE)
	{
		$section = '';

		$lines = explode("\n", $string);
		for($i = 1; ($line = array_shift($lines)) !== NULL; $i++)
			if($this->loadLine($line, $section) === FALSE)
				//XXX should cancel and return FALSE
				$error = _('Line '.$i.' could not be parsed');
		return TRUE;
	}


	//Config::reset
	public function reset()
	{
		$this->sections = array('' => new ConfigSection);
	}


	//protected
	//methods
	//Config::loadLine
	protected function loadLine($line, &$section = '')
	{
		$reg_comment = "/^[ \t]*(#.*$)?\r?$/";
		$reg_section = "/^[ \t]*\[([a-zA-Z0-9-+_:\/ \t]*)\][ \t]*\r?$/";
		$reg_variable = "/^([a-zA-Z0-9-+_: \t]+)=([^\r\n]*)\r?$/";

		if(preg_match($reg_variable, $line, $matches) == 1)
			$this->set($section, $matches[1], $matches[2]);
		else if(preg_match($reg_section, $line, $matches) == 1)
			$section = $matches[1];
		else if(preg_match($reg_comment, $line) != 1)
			return FALSE;
		return TRUE;
	}
}

?>
