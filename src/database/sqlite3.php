<?php //$Id$
//Copyright (c) 2012-2015 Pierre Pronchery <khorben@defora.org>
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



//SQLite3Database
class SQLite3Database extends Database
{
	//SQLite3Database::~SQLite3Database
	function __destruct()
	{
		if($this->handle == NULL)
			return;
		$this->handle->close();
	}


	//public
	//methods
	//accessors
	//SQLite3Database::getLastID
	public function getLastID($engine, $table, $field)
	{
		if($this->handle === FALSE)
			return FALSE;
		//FIXME return the real last ID for $table_$field
		return $this->handle->lastInsertRowID();
	}


	//useful
	//SQLite3Database::enum
	public function enum($engine, $table, $field)
	{
		$query = 'SELECT name FROM '.$table.'_enum_'.$field;
		if(($res = $this->query($engine, $query)) === FALSE)
			return FALSE;
		$ret = array();
		foreach($res as $r)
			$ret[] = $r['name'];
		return $ret;
	}


	//SQLite3Database::query
	public function query($engine, $query, &$parameters = FALSE,
			$async = FALSE)
	{
		global $config;

		if($this->handle === FALSE)
			return FALSE;
		if($config->get('database', 'debug'))
			$engine->log('LOG_DEBUG', $query);
		if(($stmt = $this->prepare($query, $parameters)) === FALSE)
			return $engine->log('LOG_ERR',
					'Could not prepare statement: '
					.$this->handle->lastErrorMsg());
		if($parameters === FALSE)
			$parameters = array();
		if($stmt->clear() !== TRUE)
			return $engine->log('LOG_ERR',
					'Could not clear statement: '
					.$this->handle->lastErrorMsg());
		foreach($parameters as $k => $v)
			$stmt->bindValue(':'.$k, $v);
		if(($res = $stmt->execute()) === FALSE)
			return $engine->log('LOG_ERR',
					'Could not execute statement: '
					.$this->handle->lastErrorMsg());
		return new SQLite3DatabaseResult($res);
	}


	//functions
	//SQLite3Database::concat
	public function _concat($string1, $string2)
	{
		if($string1 === NULL && $string2 === NULL)
			return '';
		else if($string1 === NULL)
			return $string2;
		else if($string2 === NULL)
			return $string1;
		return $string1.$string2;
	}


	//SQLite3Database::_date_trunc
	public function _date_trunc($where, $value)
	{
		if($where == 'month')
			return substr($value, 0, 8).'01';
		//FIXME really implement
		return $value;
	}


	//protected
	//methods
	//SQLite3Database::match
	protected function match($engine)
	{
		global $config;

		if(!class_exists('SQLite3'))
			return 0;
		if($config->get('database::sqlite3', 'filename')
				!== FALSE)
			return 100;
		return 0;
	}


	//SQLite3Database::attach
	protected function attach($engine)
	{
		global $config;

		if(($filename = $config->get('database::sqlite3', 'filename'))
				=== FALSE)
			return $engine->log('LOG_ERR',
					'Database filename not defined');
		try
		{
			$this->handle = new SQLite3($filename);
		}
		catch(Exception $e)
		{
			$this->handle = FALSE;
			return $engine->log('LOG_ERR',
				'Could not open database');
		}
		$func = array($this, '_concat');
		$this->handle->createFunction('concat', $func, 2);
		$func = array($this, '_date_trunc');
		$this->handle->createFunction('date_trunc', $func);
		//default the LIKE keyword to case-sensitive
		$query = 'PRAGMA case_sensitive_like=1';
		$this->query($engine, $query);
		return TRUE;
	}


	//SQLite3Database::escape
	protected function escape($string)
	{
		return "'".SQLite3::escapeString($string)."'";
	}


	//SQLite3Database::prepare
	public function prepare($query, &$parameters = FALSE)
	{
		//FIXME use a class property instead
		static $statements = array();

		if(isset($statements[$query]))
			return $statements[$query];
		$statements[$query] = $this->handle->prepare($query);
		return $statements[$query];
	}


	//SQLite3Database::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$func = array($this, '_regexp_callback');

		if(!$this->func_regexp)
		{
			$this->func_regexp = TRUE;
			$this->handle->createFunction('regexp', $func);
		}
		//XXX applies globally
		$this->case = $case;
		return parent::regexp($case, $pattern);
	}

	public function _regexp_callback($pattern, $subject)
	{
		//XXX the delimiter character may be used within the pattern
		$pattern = $this->case ? ",$pattern," : ",$pattern,i";
		return (preg_match($pattern, $subject) === 1) ? TRUE : FALSE;
	}


	//SQLite3Database::transactionBegin
	public function transactionBegin($engine)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->transaction++ == 0)
			return parent::transactionBegin($engine);
		return TRUE;
	}


	//SQLite3Database::transactionCommit
	public function transactionCommit($engine)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->transaction == 0)
			return FALSE;
		if($this->transaction-- == 1)
			return parent::transactionCommit($engine);
		return TRUE;
	}


	//SQLite3Database::transactionRollback
	public function transactionRollback($engine)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->transaction == 0)
			return FALSE;
		if($this->transaction-- == 1)
			return parent::transactionRollback($engine);
		return TRUE;
	}


	//private
	//properties
	private $handle = FALSE;
	private $transaction = 0;
	private $case;
	//functions
	private $func_regexp = FALSE;
}

?>
