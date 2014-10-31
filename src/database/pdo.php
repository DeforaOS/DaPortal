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



//PDODatabase
class PDODatabase extends Database
{
	//PDODatabase::~PDODatabase
	function __destruct()
	{
		$this->handle = FALSE;
	}


	//public
	//methods
	//accessors
	//PDODatabase::getLastID
	public function getLastID($engine, $table, $field)
	{
		if($this->handle === FALSE)
			return FALSE;
		//determine the underlying backend
		switch($this->getBackend())
		{
			case 'pgsql':
				//PostgreSQL requires a sequence object
				$seq = $table.'_'.$field.'_seq';
				return $this->handle->lastInsertId($seq);
			default:
				return $this->handle->lastInsertId();
		}
	}


	//useful
	//PDODatabase::enum
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


	//PDODatabase::like
	public function like($case = TRUE, $pattern = FALSE)
	{
		switch($this->getBackend())
		{
			case 'pgsql':
				$ret = $case ? 'LIKE' : 'ILIKE';
				if($pattern !== FALSE)
					$ret .= ' '.$this->escape($pattern);
				return $ret;
			default:
				return parent::like($case, $pattern);
		}
	}


	//PDODatabase::prepare
	public function prepare($query, &$parameters = FALSE)
	{
		static $statements = array();

		if(isset($statements[$query]))
			return $statements[$query];
		$statements[$query] = $this->handle->prepare($query);
		return $statements[$query];
	}


	//PDODatabase::query
	public function query($engine, $query, &$parameters = FALSE)
	{
		global $config;

		if($this->handle === FALSE)
			return FALSE;
		if($config->get('database', 'debug'))
			$engine->log('LOG_DEBUG', $query);
		if(($stmt = $this->prepare($query, $parameters)) === FALSE)
			return $this->_queryError($engine,
					'Could not prepare statement');
		if($parameters === FALSE)
			$parameters = array();
		$args = array();
		foreach($parameters as $k => $v)
			if(is_bool($v))
				$args[':'.$k] = $v ? 1 : 0;
			else
				$args[':'.$k] = $v;
		if($stmt->execute($args) !== TRUE)
			return $this->_queryError($engine,
					'Could not execute query');
		return new $this->result_class($stmt);
	}

	protected function _queryError($engine, $message)
	{
		$error = $this->handle->errorInfo();

		if(count($error) == 3)
			return $engine->log('LOG_ERR', $message.': '.$error[0]
					.': '.$error[2]);
		return $engine->log('LOG_ERR', $message.': '.$error[0]);
	}


	//PDODatabase::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$func = array($this, '_regexp_callback');

		if(!$this->func_regexp)
		{
			$this->func_regexp = TRUE;
			if($this->getBackend() == 'sqlite')
				$this->handle->sqliteCreateFunction('regexp',
						$func);
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


	//PDODatabase::transactionBegin
	public function transactionBegin($engine)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->transaction++ == 0)
			return $this->handle->beginTransaction();
		return TRUE;
	}


	//PDODatabase::transactionCommit
	public function transactionCommit($engine)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->transaction == 0)
			return FALSE;
		if($this->transaction-- == 1)
			return $this->handle->commit();
		return TRUE;
	}


	//PDODatabase::transactionRollback
	public function transactionRollback($engine)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->transaction == 0)
			return FALSE;
		if($this->transaction-- == 1)
			return $this->handle->rollback();
		return TRUE;
	}


	//functions
	//PDODatabase::_date_trunc
	public function _date_trunc($where, $value)
	{
		if($where == 'month')
			return substr($value, 0, 8).'01';
		//FIXME really implement
		return $value;
	}


	//protected
	//methods
	//PDODatabase::match
	protected function match($engine)
	{
		global $config;

		if(!class_exists('PDO'))
			return 0;
		if($config->get('database::pdo', 'dsn') !== FALSE)
			return 100;
		return 0;
	}


	//PDODatabase::attach
	protected function attach($engine)
	{
		global $config;

		if(($dsn = $config->get('database::pdo', 'dsn')) === FALSE)
			return $engine->log('LOG_ERR',
					'Data Source Name (DSN) not defined');
		$username = $config->get('database::pdo', 'username');
		$password = $config->get('database::pdo', 'password');
		$args = $config->get('database::pdo', 'persistent')
			? array(PDO::ATTR_PERSISTENT => true) : array();
		try {
			$this->handle = new PDO($dsn, $username, $password,
				$args);
		} catch(PDOException $e) {
			$message = 'Could not open database: '.$e->getMessage();
			return $engine->log('LOG_ERR', $message);
		}
		//database-specific hacks
		switch($this->getBackend())
		{
			case 'sqlite':
				$this->result_class = 'PDODatabaseResultCached';
				$func = array($this, '_date_trunc');
				$this->handle->sqliteCreateFunction(
						'date_trunc', $func);
				break;
		}
		return TRUE;
	}


	//accessors
	//PDODatabase::getBackend
	protected function getBackend()
	{
		global $config;

		if(($backend = $config->get('database::pdo', 'dsn'))
				=== FALSE)
			return FALSE;
		$backend = explode(':', $backend);
		if(is_array($backend))
			return $backend[0];
		return $backend;
	}


	//useful
	//PDODatabase::escape
	protected function escape($string)
	{
		if($this->handle === FALSE)
			return FALSE;
		if(is_bool($string))
			return $string ? "'1'" : "'0'";
		return $this->handle->quote($string);
	}


	//private
	//properties
	private $handle = FALSE;
	private $result_class = 'PDODatabaseResult';
	private $transaction = 0;
	private $case;
	//functions
	private $func_regexp = FALSE;
}


//PDODatabaseResultCached
//FIXME move to a separate file
class PDODatabaseResultCached extends DatabaseResult
{
	//public
	//methods
	//PDODatabaseResultCached::PDODatabaseResultCached
	public function __construct($stmt)
	{
		$this->stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->count = count($this->stmt);
	}


	//accessors
	//PDODatabaseResultCached::getAffectedCount
	public function getAffectedCount()
	{
		return $this->stmt->rowCount();
	}


	//SeekableIterator
	//PDODatabaseResultCached::current
	public function current()
	{
		if(!isset($this->stmt[$this->key]))
			return FALSE;
		return $this->stmt[$this->key];
	}


	//private
	//properties
	private $stmt;
}

?>
