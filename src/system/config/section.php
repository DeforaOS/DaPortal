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

?>
