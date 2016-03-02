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



//Request
class Request extends Mutator
{
	//public
	//methods
	//Request::Request
	public function __construct($module = FALSE, $action = FALSE,
			$id = FALSE, $title = FALSE, $parameters = FALSE)
	{
		if($module === FALSE
				|| $this->setModule($module) === FALSE
				|| $this->setAction($action) === FALSE
				|| $this->setID($id) === FALSE
				|| $this->setTitle($title) === FALSE
				|| $this->setParameters($parameters) === FALSE)
			return;
	}


	//accessors
	//Request::getAction
	public function getAction()
	{
		return $this->action;
	}


	//Request::getID
	public function getID()
	{
		return $this->id;
	}


	//Request::getModule
	public function getModule()
	{
		return $this->module;
	}


	//Request::getParameter
	public function getParameter($name)
	{
		return $this->get($name);
	}


	//Request::getParameters
	public function getParameters()
	{
		return $this->properties;
	}


	//Request::getTitle
	public function getTitle()
	{
		return $this->title;
	}


	//Request::getType
	public function getType()
	{
		return $this->type;
	}


	//Request::isIdempotent
	public function isIdempotent()
	{
		return $this->idempotent;
	}


	//Request::setIdempotent
	public function setIdempotent($idempotent)
	{
		$this->idempotent = $idempotent ? TRUE : FALSE;
	}


	//Request::setParameter
	public function setParameter($name, $value)
	{
		return $this->set($name, $value);
	}


	//Request::setType
	public function setType($type)
	{
		$this->type = $type;
	}


	//private
	//methods
	//accessors
	//Request::setModule
	private function setModule($module)
	{
		if($module === FALSE)
		{
			$this->module = $module;
			return TRUE;
		}
		if(!is_string($module)
				|| strpos($module, '.') !== FALSE
				|| strpos($module, '/') !== FALSE)
			return $this->reset();
		$this->module = $module;
		return TRUE;
	}


	//Request::setAction
	private function setAction($action)
	{
		if($action === FALSE)
		{
			$this->action = FALSE;
			return TRUE;
		}
		if(!is_string($action)
				|| strpos($action, '.') !== FALSE
				|| strpos($action, '/') !== FALSE)
			return $this->reset();
		$this->action = basename($action);
		return TRUE;
	}


	//Request::setID
	private function setID($id)
	{
		if($id !== FALSE && !is_numeric($id))
			return $this->reset();
		$this->id = $id;
		return TRUE;
	}


	//Request::setParameters
	private function setParameters($parameters)
	{
		if($parameters === FALSE)
		{
			$this->properties = array();
			return TRUE;
		}
		if(!is_array($parameters))
			return $this->reset();
		foreach($parameters as $k => $v)
			if($this->set($k, $v) === FALSE)
				return $this->reset();
		return TRUE;
	}


	//Request::setTitle
	private function setTitle($title)
	{
		if($title !== FALSE && !is_string($title))
			return $this->reset();
		$this->title = $title;
		return TRUE;
	}


	//useful
	//Request::reset
	private function reset()
	{
		$this->module = FALSE;
		$this->action = FALSE;
		$this->id = FALSE;
		$this->title = FALSE;
		$this->properties = array();
		return FALSE;
	}


	//private
	//properties
	private $idempotent = TRUE;
	private $module = FALSE;
	private $action = FALSE;
	private $id = FALSE;
	private $title = FALSE;
	private $type = FALSE;
}

?>
