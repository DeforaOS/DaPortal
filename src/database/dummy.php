<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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



//DummyDatabase
class DummyDatabase extends Database
{
	//public
	//methods
	//accessors
	//DummyDatabase::getLastID
	public function getLastID($engine, $table, $field)
	{
		//always fail
		return FALSE;
	}


	//useful
	//DummyDatabase::enum
	public function enum($engine, $table, $field)
	{
		//always fail
		return FALSE;
	}


	//DummyDatabase::query
	public function query($engine, $query, &$parameters = FALSE)
	{
		//always fail
		return FALSE;
	}


	//protected
	//methods
	//essential
	//DummyDatabase::match
	protected function match($engine)
	{
		//never match
		return 0;
	}


	//DummyDatabase::attach
	protected function attach($engine)
	{
		//always succeed
		return TRUE;
	}


	//useful
	//DummyDatabase::escape
	protected function escape($string)
	{
		return str_replace("'", "''", $string);
	}
}

?>
