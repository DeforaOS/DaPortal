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



//DatabaseResult
abstract class DatabaseResult implements Countable, SeekableIterator
{
	//public
	//methods
	//Countable
	//DatabaseResult::count
	public function count()
	{
		return $this->count;
	}


	//DatabaseResult::key
	public function key()
	{
		return $this->key;
	}


	//DatabaseResult::next
	public function next()
	{
		$this->key++;
	}


	//DatabaseResult::rewind
	public function rewind()
	{
		$this->key = 0;
	}


	//DatabaseResult::seek
	public function seek($key)
	{
		$this->key = $key;
	}


	//DatabaseResult::valid
	public function valid()
	{
		return $this->key < $this->count;
	}


	//protected
	//properties
	protected $count = 0;
	protected $key = 0;
}

?>
