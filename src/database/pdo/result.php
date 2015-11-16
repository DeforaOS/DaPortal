<?php //$Id$
//Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org>
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



//PDODatabaseResult
class PDODatabaseResult extends DatabaseResult
{
	//public
	//methods
	//PDODatabaseResult::PDODatabaseResult
	public function __construct(PDOStatement $stmt)
	{
		$this->count = $stmt->rowCount();
		$this->stmt = $stmt;
	}


	//accessors
	//PDODatabaseResult::getAffectedCount
	public function getAffectedCount()
	{
		return $this->stmt->rowCount();
	}


	//SeekableIterator
	//PDODatabaseResult::current
	public function current()
	{
		return $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS,
				$this->key);
	}


	//private
	//properties
	private $stmt;
}

?>
