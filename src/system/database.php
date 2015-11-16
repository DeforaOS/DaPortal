<?php //$Id$
//Copyright (c) 2011-2015 Pierre Pronchery <khorben@defora.org>
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



//Database
abstract class Database
{
	//public
	//methods
	//essential
	public function __construct($name)
	{
		$this->name = $name;
	}


	//accessors
	//Database::inTransaction
	public function inTransaction()
	{
		return $this->transaction > 0;
	}


	//Database::isFalse
	public function isFalse($value)
	{
		return $value == 0;
	}


	//Database::isTrue
	public function isTrue($value)
	{
		return $value == 1;
	}


	//useful
	//Database::formatDate
	public function formatDate($date, $outformat = FALSE, $informat = FALSE)
	{
		//XXX obsolete this call
		return Date::format($date, $outformat, $informat);
	}


	//Database::like
	public function like($case = TRUE, $pattern = FALSE)
	{
		$ret = 'LIKE';

		if($pattern !== FALSE)
			$ret .= ' '.$this->escape($pattern);
		return $ret;
	}


	//Database::limit
	public function limit($limit = FALSE, $offset = FALSE)
	{
		if($limit === FALSE && $offset === FALSE)
			return '';
		$ret = ($limit !== FALSE && is_numeric($limit))
			? " LIMIT $limit" : '';
		if($offset !== FALSE && is_numeric($offset))
			$ret .= " OFFSET $offset";
		return $ret;
	}


	//Database::offset
	public function offset($limit, $offset = FALSE)
	{
		return $this->limit($limit, $offset);
	}


	//Database::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$ret = 'REGEXP';

		if($pattern !== FALSE)
			$ret .= ' '.$this->escape($pattern);
		return $ret;
	}


	//Database::transactionBegin
	public function transactionBegin(Engine $engine = NULL)
	{
		if($this->inTransaction())
		{
			$this->transaction++;
			return TRUE;
		}
		$this->rollback = FALSE;
		if($this->_beginTransaction($this->engine) === FALSE)
			return FALSE;
		$this->transaction = 1;
		return TRUE;
	}

	protected function _beginTransaction()
	{
		return $this->query($this->engine, 'START TRANSACTION');
	}


	//Database::transactionCommit
	public function transactionCommit(Engine $engine = NULL)
	{
		if(!$this->inTransaction())
			return FALSE;
		if($this->transaction != 1)
		{
			$this->transaction--;
			return TRUE;
		}
		if($this->rollback)
		{
			$this->transactionRollback($this->engine);
			return FALSE;
		}
		$this->transaction--;
		return $this->_commitTransaction($this->engine);
	}

	protected function _commitTransaction()
	{
		return $this->query($this->engine, 'COMMIT');
	}


	//Database::transactionRollback
	public function transactionRollback(Engine $engine = NULL)
	{
		if(!$this->inTransaction())
			return FALSE;
		if($this->transaction-- == 1)
			return $this->_rollbackTransaction($this->engine);
		$this->rollback = TRUE;
		return TRUE;
	}

	protected function _rollbackTransaction()
	{
		return $this->query($this->engine, 'ROLLBACK');
	}


	//Database::withTransaction
	public function withTransaction(Engine $engine, callable $callback)
	{
		if($this->inTransaction())
			return $callback();
		if($this->transactionBegin($this->engine) === FALSE)
			return FALSE;
		if(($ret = $callback()) === FALSE)
			$this->transactionRollback($this->engine);
		else if($this->transactionCommit($this->engine) === FALSE)
			return FALSE;
		return $ret;
	}


	//static
	//Database::attachDefault
	public static function attachDefault(Engine $engine)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		if(($name = $config->get('database', 'backend')) !== FALSE)
		{
			$class = $name.'Database';
			$ret = new $class($name);
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' (default)');
			if($ret->attach($engine) === FALSE)
				return FALSE;
			return $ret;
		}
		if(($dir = opendir('database')) === FALSE)
			return FALSE;
		while(($de = readdir($dir)) !== FALSE)
		{
			if(substr($de, -4) != '.php')
				continue;
			$name = substr($de, 0, strlen($de) - 4);
			$class = $name.'Database';
			$db = new $class($name);
			if(($p = $db->match($engine)) <= $priority)
				continue;
			$ret = $db;
			$priority = $p;
		}
		closedir($dir);
		if($ret != FALSE)
		{
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
			if($ret->attach($engine) === FALSE)
				return FALSE;
		}
		return $ret;
	}


	//virtual
	abstract public function getLastID(Engine $engine, $table, $field);

	abstract public function enum(Engine $engine, $table, $field);
	abstract public function query(Engine $engine, $query,
			$parameters = FALSE, $async = FALSE);


	//protected
	//properties
	protected $name;
	protected $engine = FALSE;
	//profiling
	protected $profile = FALSE;
	//transactions
	protected $transaction = 0;
	protected $rollback = FALSE;

	//queries
	static protected $query_sql_profile = 'INSERT INTO daportal_sql_profile
		(time, query) VALUES (:time, :query)';


	//methods
	//virtual
	abstract protected function match(Engine $engine);
	abstract protected function attach(Engine $engine);

	abstract protected function escape($string);


	//accessors
	//Database::configGet
	protected function configGet($variable)
	{
		global $config;

		return $config->get('database::'.$this->name, $variable);
	}


	//useful
	//Database::prepare
	protected function prepare($query, &$parameters = FALSE)
	{
		if($parameters === FALSE)
			$parameters = array();
		else if(!is_array($parameters))
			return FALSE;
		$from = array();
		$to = array();
		foreach($parameters as $k => $v)
		{
			$from[] = ':'.$k;
			$to[] = ($v !== NULL) ? $this->escape($v) : 'NULL';
		}
		//FIXME should really use preg_replace() with proper matching
		return str_replace($from, $to, $query);
	}


	//Database::profileStart
	protected function profileStart()
	{
		if($this->profile === FALSE)
			$this->profile = microtime(TRUE);
		return TRUE;
	}


	//Database::profileStop
	protected function profileStop($query)
	{
		global $config;

		if(($time = $this->profile) === FALSE
				|| $this->profile === TRUE)
			return TRUE;
		$this->profile = FALSE;
		if($config->get('database', 'profile') == FALSE
				|| ($threshold = $config->get('database',
						'profile_threshold')) === FALSE
				|| !is_numeric($threshold)
				|| ($time = round((microtime(TRUE) - $time)
					* 1000)) < $threshold)
			return TRUE;
		//prevent any foreseeable failure (may break transactions)
		$error = 'Could not store query for profiling';
		if(strlen($query) > 255)
			$query = substr($query, 0, 252).'...';
		$args = array('time' => $time, 'query' => $query);
		$this->profile = TRUE;
		if($this->query($this->engine, static::$query_sql_profile,
				$args) === FALSE)
			$this->engine->log('LOG_ERR', $error.' (SQL error)');
		$this->profile = FALSE;
		return TRUE;
	}
}

?>
