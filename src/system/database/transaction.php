<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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



//DatabaseTransaction
class DatabaseTransaction
{
	//public
	//methods
	//essential
	//DatabaseTransaction::DatabaseTransaction
	public function __construct(Database $database)
	{
		$this->database = $database;
	}


	//DatabaseTransaction::~DatabaseTransaction
	public function __destruct()
	{
		if($this->inTransaction())
			$this->flush();
	}


	//accessors
	//DatabaseTransaction::inTransaction
	public function inTransaction()
	{
		return $this->count > 0;
	}


	//useful
	//DatabaseTransaction::begin
	public function begin()
	{
		if($this->inTransaction())
		{
			$this->count++;
			return TRUE;
		}
		if($this->databaseBegin() === FALSE)
			return FALSE;
		$this->count = 1;
		return TRUE;
	}


	//DatabaseTransaction::commit
	public function commit()
	{
		if(!$this->inTransaction())
			return FALSE;
		if($this->count > 1)
		{
			$this->count--;
			return TRUE;
		}
		return $this->complete();
	}


	//DatabaseTransaction::complete
	public function complete()
	{
		if(!$this->inTransaction())
			return FALSE;
		$this->count = 0;
		if($this->rollback)
			$res = $this->databaseRollback();
		else if(($res = $this->databaseCommit()) === FALSE)
			$this->rollback = TRUE;
		$this->database->transactionComplete();
		return $res;
	}


	//DatabaseTransaction::flush
	public function flush()
	{
		$this->rollback = TRUE;
		return $this->complete();
	}


	//DatabaseTransaction::rollback
	public function rollback()
	{
		if(!$this->inTransaction())
			return FALSE;
		$this->rollback = TRUE;
		if($this->count > 1)
		{
			$this->count--;
			return TRUE;
		}
		return $this->complete();
	}


	//methods
	//accessors
	//DatabaseTransaction::getDatabase
	protected function getDatabase()
	{
		return $this->database;
	}


	//useful
	//DatabaseTransaction::databaseBegin
	protected function databaseBegin()
	{
		return $this->database->query(NULL, 'START TRANSACTION');
	}


	//DatabaseTransaction::databaseCommit
	protected function databaseCommit()
	{
		return $this->database->query(NULL, 'COMMIT');
	}


	//DatabaseTransaction::databaseRollback
	protected function databaseRollback()
	{
		return $this->database->query(NULL, 'ROLLBACK');
	}


	//private
	//properties
	private $database;
	private $count = -1;
	private $rollback = FALSE;
}

?>
