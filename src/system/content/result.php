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



//ContentResult
class ContentResult implements ArrayAccess, Countable, SeekableIterator
{
	//ContentResult::ContentResult
	public function __construct(Engine $engine, Module $module, $class,
			DatabaseResult $result)
	{
		$this->engine = $engine;
		$this->module = $module;
		$this->class = $class;
		$this->result = $result;
	}


	//ArrayAccess
	//ContentResult::offsetExists
	public function offsetExists($offset)
	{
		$this->result->seek($offset);
		return $this->result->valid();
	}


	//ContentResult::offsetGet
	public function offsetGet($offset)
	{
		$this->result->seek($offset);
		return $this->current();
	}


	//ContentResult::offsetSet
	public function offsetSet($offset, $value)
	{
	}


	//ContentResult::offsetUnset
	public function offsetUnset($offset)
	{
	}


	//Countable
	//ContentResult::count
	public function count()
	{
		return $this->result->count();
	}


	//SeekableIterator
	//ContentResult::current
	public function current()
	{
		$class = $this->class;
		$res = $this->result->current();

		return $class::loadFromProperties($this->engine, $this->module,
				$this->result->current());
	}


	//ContentResult::key
	public function key()
	{
		return $this->result->key();
	}


	//ContentResult::next
	public function next()
	{
		$this->result->next();
	}


	//ContentResult::rewind
	public function rewind()
	{
		$this->result->rewind();
	}


	//ContentResult::seek
	public function seek($key)
	{
		$this->result->seek($key);
	}


	//ContentResult::valid
	public function valid()
	{
		return $this->result->valid();
	}
}

?>
