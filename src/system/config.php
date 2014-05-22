<?php //$Id$
//Copyright (c) 2011-2014 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/mutator.php');


//ConfigSection
class ConfigSection extends Mutator
{
	//methods
	//public
	//accessors
	//Config::getVariables
	public function getVariables()
	{
		//XXX implement as a list method in Mutator
		return array_keys($this->properties);
	}


	//ConfigSection::set
	public function set($name, $value)
	{
		//values must be strings as well
		if($value !== FALSE && !is_string($value))
			return FALSE;
		return parent::set($name, $value);
	}
}


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
		{
			if(preg_match("/^([a-zA-Z0-9-+_: \t]+)=([^\r\n]*)\r?$/",
					$line, $matches) == 1)
				$this->set($section, $matches[1], $matches[2]);
			else if(preg_match("/^[ \t]*\[([a-zA-Z0-9-+_:\/ \t]+)\]"
						."[ \t]*\r?$/", $line, $matches)
					== 1)
				$section = $matches[1];
			else if(preg_match("/^[ \t]*(#.*$)?\r?$/", $line) == 1)
				continue;
			else
				error_log($filename.': Line '.$i
						.' could not be parsed');
		}
		fclose($fp);
		return TRUE;
	}


	//Config::reset
	public function reset()
	{
		$this->sections = array('' => new ConfigSection);
	}
}

?>
