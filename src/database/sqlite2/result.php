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



//SQLite2DatabaseResult
class SQLite2DatabaseResult extends DatabaseResult
{
	//public
	//methods
	//essential
	//SQLite2DatabaseResult::SQLite2DatabaseResult
	public function __construct($res)
	{
		//XXX this obtains every result directly
		$this->res = sqlite_fetch_all($res);
		$this->count = count($this->res);
	}


	//SeekableIterator
	//SQLite2DatabaseResult::current
	public function current()
	{
		return $this->res[$this->key];
	}


	//private
	//properties
	private $res;
}

?>
