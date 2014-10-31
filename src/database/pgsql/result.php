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



//PgsqlDatabaseResult
class PgsqlDatabaseResult extends DatabaseResult
{
	//public
	//methods
	//PgsqlDatabaseResult::PgsqlDatabaseResult
	public function __construct($res)
	{
		$this->count = pg_num_rows($res);
		$this->res = $res;
	}


	//accessors
	//PgsqlDatabaseResult::getAffectedCount
	public function getAffectedCount()
	{
		return pg_affected_rows($this->res);
	}


	//SeekableIterator
	//PgsqlDatabaseResult::current
	public function current()
	{
		return pg_fetch_array($this->res, $this->key, PGSQL_ASSOC);
	}


	//PgsqlDatabaseResult::valid
	public function valid()
	{
		return pg_result_seek($this->res, $this->key);
	}


	//private
	//properties
	private $res;
}

?>
