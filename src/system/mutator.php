<?php //$Id$
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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



//Mutator
abstract class Mutator
{
	//public
	//methods
	//Mutator::get
	public function get($name)
	{
		if(!is_string($name)
				|| !isset($this->properties[$name]))
			return FALSE;
		return $this->properties[$name];
	}


	//Mutator::set
	public function set($name, $value)
	{
		if(!is_string($name))
			return FALSE;
		if($value === FALSE)
			unset($this->properties[$name]);
		else
			$this->properties[$name] = $value;
		return TRUE;
	}


	//properties
	//protected
	protected $properties = array();
}

?>
