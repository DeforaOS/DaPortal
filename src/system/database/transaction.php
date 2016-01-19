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
class DatabaseTransaction implements Observable
{
	//public
	//properties
	static public $STATUS_INITIAL = 0;
	static public $STATUS_COMMIT_PENDING = 1;
	static public $STATUS_ROLLBACK_PENDING = 2;
	static public $STATUS_COMMIT = 3;
	static public $STATUS_ROLLBACK = 4;


	//methods
	//essential
	//DatabaseTransaction::DatabaseTransaction
	public function __construct(Database $database)
	{
		$this->database = $database;
		$this->observers = new \SplObjectStorage();
	}


	//DatabaseTransaction::~DatabaseTransaction
	public function __destruct()
	{
		if($this->inTransaction())
			$this->flush();
	}


	//accessors
	//DatabaseTransaction::getStatus
	public function getStatus()
	{
		if($this->count < 0)
			return static::$STATUS_INITIAL;
		if($this->count >= 1)
			return $this->rollback
				? static::$STATUS_ROLLBACK_PENDING
				: static::$STATUS_COMMIT_PENDING;
		return $this->rollback
			? static::$STATUS_ROLLBACK
			: static::$STATUS_COMMIT;
	}


	//DatabaseTransaction::inTransaction
	public function inTransaction()
	{
		return $this->count > 0;
	}


	//Observable
	//DatabaseTransaction::addObserver
	public function addObserver(Observer $observer)
	{
		if($this->observers->contains($observer))
			return FALSE;
		$this->observers->attach($observer);
		return TRUE;
	}


	//DatabaseTransaction::notifyObservers
	public function notifyObservers()
	{
		//work on a copy in case Observers remove themselves
		$observers = clone $this->observers;
		foreach($observers as $observer)
			$observer->notify($this);
	}


	//DatabaseTransaction::removeObserver
	public function removeObserver(Observer $observer)
	{
		if(!$this->observers->contains($observer))
			return FALSE;
		$this->observers->detach($observer);
		return TRUE;
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
		$this->notifyObservers();
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
	//Observable
	private $observers;

	private $database;
	private $count = -1;
	private $rollback = FALSE;
}

?>
