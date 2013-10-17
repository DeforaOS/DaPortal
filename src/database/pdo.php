<?php //$Id$
//Copyright (c) 2012-2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/database.php');


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
		{
			$error = $this->handle->errorInfo();
			$error[] = '';
			$error[] = 'Unknown error';
			return $engine->log('LOG_ERR',
				'Could not prepare statement: '
				.$error[0].': '.$error[2]);
		}
		if($parameters === FALSE)
			$parameters = array();
		$args = array();
		foreach($parameters as $k => $v)
		{
			if(is_bool($v))
				$v = $v ? 1 : 0;
			$args[':'.$k] = $v;
		}
		if($stmt->execute($args) !== TRUE)
		{
			$error = $stmt->errorInfo();
			$error[] = '';
			$error[] = 'Unknown error';
			return $engine->log('LOG_ERR',
					'Could not execute query: '
					.$error[0].': '.$error[2]);
		}
		return $stmt->fetchAll();
	}


	//PDODatabase::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$func = array($this, '_regexp_callback');

		if(!$this->regexp)
		{
			$this->regexp = TRUE;
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

		if(($dsn = $config->get('database::pdo', 'dsn'))
				=== FALSE)
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
		return TRUE;
	}


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
	private $transaction = 0;
	private $regexp = FALSE;
	private $case;
}

?>
