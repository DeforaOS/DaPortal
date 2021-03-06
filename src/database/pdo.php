<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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
	//public
	//methods
	//essential
	//PDODatabase::~PDODatabase
	public function __destruct()
	{
		$this->handle = FALSE;
	}


	//accessors
	//PDODatabase::getLastID
	public function getLastID(Engine $engine = NULL, $table, $field)
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
	public function enum(Engine $engine = NULL, $table, $field)
	{
		$query = 'SELECT name FROM '.$table.'_enum_'.$field;
		if(($res = $this->query($this->engine, $query)) === FALSE)
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
		if(isset($this->statements[$query]))
			return $this->statements[$query];
		$this->statements[$query] = $this->handle->prepare($query);
		return $this->statements[$query];
	}


	//PDODatabase::query
	public function query(Engine $engine = NULL, $query,
			$parameters = FALSE, $async = FALSE)
	{
		if($this->handle === FALSE)
			return FALSE;
		if($this->debug)
			$this->engine->log(LOG_DEBUG, $query);
		if(($stmt = $this->prepare($query, $parameters)) === FALSE)
			return $this->_queryError(
					'Could not prepare statement');
		if($parameters === FALSE)
			$parameters = array();
		$args = array();
		foreach($parameters as $k => $v)
			if(is_bool($v))
				$args[':'.$k] = $v ? 1 : 0;
			else
				$args[':'.$k] = $v;
		$this->profileStart();
		$res = $stmt->execute($args);
		$this->profileStop($query);
		if($res !== TRUE)
			return $this->_queryError('Could not execute query');
		return new $this->result_class($stmt);
	}

	protected function _queryError($message)
	{
		$error = $this->handle->errorInfo();

		if(count($error) == 3)
			return $this->engine->log(LOG_ERR,
					$message.': '.$error[0].': '.$error[2]);
		return $this->engine->log(LOG_ERR, $message.': '.$error[0]);
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


	//PDODatabase::transactionDoBegin
	public function transactionDoBegin()
	{
		if($this->handle === FALSE)
			return FALSE;
		return $this->handle->beginTransaction();
	}


	//PDODatabase::transactionDoCommit
	public function transactionDoCommit()
	{
		if($this->handle === FALSE)
			return FALSE;
		return $this->handle->commit();
	}


	//PDODatabase::transactionDoRollback
	public function transactionDoRollback()
	{
		if($this->handle === FALSE)
			return FALSE;
		return $this->handle->rollback();
	}


	//functions
	//PDODatabase::concat
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


	//PDODatabase::_date_trunc
	public function _date_trunc($where, $value)
	{
		if($where == 'month')
			return substr($value, 0, 8).'01';
		//FIXME really implement
		return $value;
	}


	//protected
	//properties
	static protected $transactionClass = 'PDODatabaseTransaction';


	//methods
	//PDODatabase::match
	protected function match(Engine $engine)
	{
		if(!class_exists('PDO'))
			return 0;
		if($this->configGet('dsn') !== FALSE)
			return 100;
		return 0;
	}


	//PDODatabase::attach
	protected function attach(Engine $engine)
	{
		global $config;

		return $this->_attachConfig($engine, $config);
	}

	protected function _attachConfig(Engine $engine, Config $config,
			$section = FALSE, $new = FALSE)
	{
		if($section === FALSE)
			$section = 'database::'.$this->name;
		//XXX code duplicated from PDODatabase::attach()
		if(($dsn = $config->get($section, 'dsn')) === FALSE)
			return $engine->log(LOG_ERR,
					'Data Source Name (DSN) not defined');
		$username = $config->get($section, 'username');
		$password = $config->get($section, 'password');
		$args = $config->get($section, 'persistent')
			? array(PDO::ATTR_PERSISTENT => true) : array();
		try {
			$this->handle = new PDO($dsn, $username, $password,
				$args);
		} catch(PDOException $e) {
			$message = 'Could not open database: '.$e->getMessage();
			return $engine->log(LOG_ERR, $message);
		}
		$this->engine = $engine;
		$this->debug = $config->get('database', 'debug') ? TRUE : FALSE;
		if($this->debug)
			$this->handle->setAttribute(PDO::ATTR_ERRMODE,
					PDO::ERRMODE_WARNING);
		//database-specific hacks
		switch($this->getBackend())
		{
			case 'sqlite':
				$this->_attachSQLite();
				break;
		}
		return TRUE;
	}

	protected function _attachSQLite()
	{
		$this->result_class = 'PDODatabaseResultCached';
		$func = array($this, '_concat');
		$this->handle->sqliteCreateFunction('concat', $func, 2);
		$func = array($this, '_date_trunc');
		$this->handle->sqliteCreateFunction('date_trunc', $func);
		//default the LIKE keyword to case-sensitive
		$query = 'PRAGMA case_sensitive_like=1';
		if($this->query($this->engine, $query) === FALSE)
			$this->engine->log(LOG_WARNING,
					'Pattern matching is case-insensitive');
		//enforce support for foreign keys
		$query = 'PRAGMA foreign_keys=ON';
		if($this->query($this->engine, $query) === FALSE)
			$this->engine->log(LOG_WARNING,
					'Foreign keys are not enforced');
	}


	//accessors
	//PDODatabase::getBackend
	protected function getBackend()
	{
		if($this->backend !== FALSE)
			return $this->backend;
		if(($this->backend = $this->configGet('dsn')) !== FALSE)
		{
			$this->backend = explode(':', $this->backend);
			if(is_array($this->backend))
				$this->backend = $this->backend[0];
		}
		return $this->backend;
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
	private $backend = FALSE;
	private $case;
	private $debug;
	private $handle = FALSE;
	private $result_class = 'PDODatabaseResult';
	private $statements = array();
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
	public function __construct(PDOStatement $stmt)
	{
		$this->stmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->count = count($this->stmt);
		$this->affected = $stmt->rowCount();
		$stmt->closeCursor();
	}


	//accessors
	//PDODatabaseResultCached::getAffectedCount
	public function getAffectedCount()
	{
		return $this->affected;
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
	private $affected;
}

?>
