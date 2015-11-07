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



//PgSQLDatabase
class PgSQLDatabase extends Database
{
	//PgSQLDatabase::~PgSQLDatabase
	function __destruct()
	{
		if($this->handle !== FALSE)
			pg_close($this->handle);
	}


	//public
	//methods
	//accessors
	//PgSQLDatabase::getLastID
	public function getLastID(Engine $engine, $table, $field)
	{
		$sequence = $this->getSequence($table, $field);
		$query = $this->query_currval;
		$args = array('sequence' => $sequence);
		if(($res = $this->query($this->engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		$res = $res->current();
		return $res['currval'];
	}


	//PgSQLDatabase::isFalse
	public function isFalse($value)
	{
		return $value == 'f';
	}


	//PgSQLDatabase::isTrue
	public function isTrue($value)
	{
		return $value == 't';
	}


	//useful
	//PgSQLDatabase::enum
	public function enum(Engine $engine, $table, $field)
	{
		$query = $this->query_enum;
		$args = array('table' => $table,
			'field' => $table.'_'.$field);

		if(($res = $this->query($this->engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return array();
		$res = $res->current();
		$res = explode("'", $res['constraint']);
		$str = array();
		for($i = 1, $cnt = count($res); $i < $cnt; $i += 2)
			$str[] = $res[$i];
		return $str;
	}


	//PgSQLDatabase::formatDate
	public function formatDate($date, $outformat = FALSE, $informat = FALSE)
	{
		if($informat !== FALSE
				|| ($timestamp = strtotime($date)) === FALSE
				|| $timestamp == -1)
			return parent::formatDate($date, $outformat, $informat);
		return Date::formatTimestamp($timestamp, $outformat);
	}


	//PgSQLDatabase::like
	public function like($case = TRUE, $pattern = FALSE)
	{
		$ret = $case ? 'LIKE' : 'ILIKE';

		if($pattern !== FALSE)
			$ret .= ' '.$this->escape($pattern);
		return $ret;
	}


	//PgSQLDatabase::limit
	public function limit($limit = FALSE, $offset = FALSE)
	{
		if($limit === FALSE && $offset === FALSE)
			return '';
		$ret = ($offset !== FALSE && is_numeric($offset))
			? " OFFSET $offset" : '';
		if($limit !== FALSE && is_numeric($limit))
			$ret .= " LIMIT $limit";
		return $ret;
	}


	//PgSQLDatabase::query
	public function query(Engine $engine, $query, $parameters = FALSE,
			$async = FALSE)
	{
		global $config;

		if($this->handle === FALSE)
			return FALSE;
		if($config->get('database', 'debug'))
			$this->engine->log('LOG_DEBUG', $query);
		//convert the query to the PostgreSQL way
		//FIXME cache the results of the conversion
		//XXX this may break the query string in illegitimate places
		$q = explode(':', $query);
		$query = $q[0];
		$args = array();
		for($i = 1, $cnt = count($q); $i < $cnt; $i++)
		{
			for($j = 0, $len = strlen($q[$i]); $j < $len
				&& (ctype_alnum($q[$i][$j])
					|| $q[$i][$j] == '_'); $j++);
			$k = substr($q[$i], 0, $j);
			if(!array_key_exists($k, $parameters))
			{
				$error = $k.': Missing parameter in query';
				return $this->engine->log('LOG_ERR', $error);
			}
			$query .= "\$$i ".substr($q[$i], $j);
			if(is_bool($parameters[$k]))
				$args[$i] = $parameters[$k] ? '1' : '0';
			else
				$args[$i] = $parameters[$k];
		}
		if($config->get('database::pgsql', 'debug'))
			$this->engine->log('LOG_DEBUG', get_class().': '.$query);
		//prepare the query
		if(($q = $this->prepare($query, $args)) === FALSE)
			return FALSE;
		//execute the query
		$this->profileStart();
		if($async)
			$res = pg_send_execute($this->handle, $q, $args);
		else
			$res = pg_execute($this->handle, $q, $args);
		$this->profileStop($this->engine, $query);
		if($res === FALSE)
		{
			if(($error = pg_last_error($this->handle)) !== FALSE)
				$this->engine->log('LOG_DEBUG', $error);
			return FALSE;
		}
		if($async)
			return TRUE;
		if(pg_num_rows($res) == -1)
			return FALSE;
		return new PgSQLDatabaseResult($res);
	}


	//PgSQLDatabase::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$ret = $case ? '~' : '~*';

		if($pattern !== FALSE)
			$ret .= ' '.$this->escape($pattern);
		return $ret;
	}


	//PgSQLDatabase::transactionBegin
	public function transactionBegin(Engine $engine = NULL)
	{
		return parent::transactionBegin();
	}

	protected function _beginTransaction()
	{
		return $this->query($this->engine, 'BEGIN');
	}


	//protected
	//properties
	//queries
	//IN:	sequence
	protected $query_currval = 'SELECT currval(:sequence) AS currval';
	//IN:	table
	//	field
	protected $query_enum = 'SELECT
		pg_catalog.pg_get_constraintdef(r.oid) AS constraint
		FROM pg_catalog.pg_class c, pg_catalog.pg_constraint r
		WHERE c.oid=r.conrelid AND c.relname=:table
		AND conname=:field';


	//methods
	//PgSQLDatabase::match
	protected function match(Engine $engine)
	{
		global $config;
		$variables = $this->variables;

		if(!function_exists('pg_connect'))
			return 0;
		foreach($variables as $k => $v)
			if($config->get('database::pgsql', $k) !== FALSE)
				return 100;
		return 1;
	}


	//PgSQLDatabase::attach
	protected function attach(Engine $engine)
	{
		global $config;

		if($this->_attachConfig($config) === FALSE)
			return $engine->log('LOG_ERR',
					'Could not open database');
		$this->engine = $engine;
		return TRUE;
	}

	protected function _attachConfig($config, $section = 'database::pgsql',
			$new = FALSE)
	{
		$str = '';
		$sep = '';
		$flags = $new ? PGSQL_CONNECT_FORCE_NEW : 0;

		foreach($this->variables as $k => $v)
			if(($p = $config->get($section, $k)) !== FALSE)
			{
				$str .= $sep.$v."='$p'"; //XXX escape?
				$sep = ' ';
			}
		$this->handle = $config->get($section, 'persistent')
			? pg_pconnect($str, $flags) : pg_connect($str, $flags);
		return ($this->handle !== FALSE) ? TRUE : FALSE;
	}


	//PgSQLDatabase::escape
	protected function escape($string)
	{
		if(is_bool($string))
			$string = $string ? '1' : '0';
		if(function_exists('pg_escape_literal'))
			return pg_escape_literal($this->handle, $string);
		return "'".pg_escape_string($this->handle, $string)."'";
	}


	//PgSQLDatabase::prepare
	protected function prepare($query, &$parameters = FALSE)
	{
		//FIXME use a class property instead
		static $statements = array();

		if(isset($statements[$query]))
			return $statements[$query];
		$id = uniqid();
		$statements[$query] = (pg_prepare($this->handle, $id, $query)
			!== FALSE) ? $id : FALSE;
		return $statements[$query];
	}


	//accessors
	//PgSQLDatabase::getSequence
	protected function getSequence($table, $field)
	{
		return $table.'_'.$field.'_seq';
	}


	//private
	//properties
	private $handle = FALSE;

	private $variables = array('username' => 'user',
		'password' => 'password',
		'database' => 'dbname', 'hostname' => 'host',
		'port' => 'port',
		'timeout' => 'connect_timeout',
		'service' => 'service',
		'sslmode' => 'sslmode');
}

?>
