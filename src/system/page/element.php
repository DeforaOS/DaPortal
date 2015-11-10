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



//PageElement
class PageElement extends Mutator
{
	//public
	//methods
	//essential
	//PageElement::PageElement
	public function __construct($type, $properties = FALSE)
	{
		$this->type = $type;
		if(is_array($properties))
			foreach($properties as $key => $value)
				$this->set($key, $value);
	}


	//accessors
	//PageElement::getChildren
	public function getChildren()
	{
		return $this->children;
	}


	//PageElement::getProperties
	public function getProperties()
	{
		//XXX implement in Mutator
		return $this->properties;
	}


	//PageElement::getProperty
	public function getProperty($name, $default = FALSE)
	{
		if(($ret = $this->get($name)) === FALSE)
			return $default;
		return $ret;
	}


	//PageElement::getType
	public function getType()
	{
		return $this->type;
	}


	//PageElement::setProperty
	public function setProperty($name, $value)
	{
		return $this->set($name, $value);
	}


	//PageElement::setType
	public function setType($type)
	{
		$this->type = $type;
	}


	//useful
	//PageElement::append
	public function append($type, $properties = FALSE)
	{
		if($type instanceof PageElement)
			$element = $type;
		else
			$element = new PageElement($type);
		if(is_array($properties))
			foreach($properties as $key => $value)
				$element->set($key, $value);
		$this->children[] = $element;
		return $element;
	}


	//PageElement::prepend
	public function prepend($type, $properties = FALSE)
	{
		if($type instanceof PageElement)
			$element = $type;
		else
			$element = new PageElement($type, $properties);
		if(is_array($properties))
			foreach($properties as $key => $value)
				$element->set($key, $value);
		$this->children = array_merge(array($element), $this->children);
		return $element;
	}


	//private
	//properties
	private $type;
	private $children = array();
}

?>
