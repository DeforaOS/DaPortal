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


//PgsqlDatabase
class PgsqlDatabase extends Database
{
	//PgsqlDatabase::~PgsqlDatabase
	function __destruct()
	{
		if($this->handle === FALSE)
			return;
		pg_close($this->handle);
	}


	//public
	//methods
	//accessors
	//PgsqlDatabase::getLastId
	public function getLastId($engine, $table, $field)
	{
		if($this->handle === FALSE)
			return FALSE;
		//FIXME untested
		$query = "SELECT currval('".$table."_".$field."_seq');";
		if(($res = $this->query($engine, $query)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return $res[0][0];
	}


	//PgsqlDatabase::isFalse
	public function isFalse($value)
	{
		return $value == 'f';
	}


	//PgsqlDatabase::isTrue
	public function isTrue($value)
	{
		return $value == 't';
	}


	//useful
	//PgsqlDatabase::enum
	public function enum($engine, $table, $field)
	{
		$query = $this->query_enum;
		$args = array('table' => $table,
			'field' => $table.'_'.$field);

		if(($res = $this->query($engine, $query, $args)) === FALSE)
			return array();
		$res = explode("'", $res[0]['constraint']);
		$str = array();
		for($i = 1, $cnt = count($res); $i < $cnt; $i+=2)
			$str[] = $res[$i];
		return $str;
	}


	//PgsqlDatabase::formatDate
	public function formatDate($engine, $date, $outformat = FALSE,
			$informat = FALSE)
	{
		if($informat !== FALSE
				|| ($timestamp = strtotime($date)) === FALSE
					|| $timestamp == -1)
			return parent::formatDate($engine, $date, $outformat,
					$informat);
		if($outformat === FALSE)
			$outformat = '%d/%m/%Y %H:%M:%S';
		return strftime($outformat, $timestamp);
	}


	//PgsqlDatabase::like
	public function like($case = TRUE, $pattern = FALSE)
	{
		$ret = $case ? 'LIKE' : 'ILIKE';

		if($pattern !== FALSE)
			$ret .= ' '.$this->escape($pattern);
		return $ret;
	}


	//PgsqlDatabase::offset
	public function offset($limit, $offset = FALSE)
	{
		$ret = '';

		//FIXME untested
		//XXX report errors
		if($offset !== FALSE && is_numeric($offset))
			$ret .= " OFFSET $offset";
		if(!is_numeric($limit))
			$limit = 0;
		$ret .= " LIMIT $limit";
		return $ret;
	}


	//PgsqlDatabase::query
	public function query($engine, $query, &$parameters = FALSE)
	{
		global $config;

		if($this->handle === FALSE)
			return FALSE;
		if($config->getVariable('database', 'debug'))
			$engine->log('LOG_DEBUG', $query);
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
			if(!isset($parameters[$k]))
				return $engine->log('LOG_ERR',
						'Incomplete SQL statement '
						."($k key not set)");
			$query .= "\$$i ".substr($q[$i], $j);
			if(is_bool($parameters[$k]))
				$args[$i] = $parameters[$k] ? '1' : '0';
			else
				$args[$i] = $parameters[$k];
		}
		if($config->getVariable('database', 'debug'))
			$engine->log('LOG_WARN', $query);
		//prepare the query
		if(($q = $this->prepare($query, $args)) === FALSE)
			return FALSE;
		//execute the query
		if(($res = pg_execute($this->handle, $q, $args)) === FALSE)
		{
			if(($error = pg_last_error($this->handle)) !== FALSE)
				$engine->log('LOG_DEBUG', $error);
			return FALSE;
		}
		//FIXME use pg_fetchall() instead (breaks _sql_single() for now)
		for($array = array(); ($a = pg_fetch_array($res)) != FALSE;
				$array[] = $a);
		return $array;
	}


	//PgsqlDatabase::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$ret = $case ? '~' : '~*';

		if($pattern !== FALSE)
			$ret .= ' '.$this->escape($pattern);
		return $ret;
	}


	//protected
	//methods
	//PgsqlDatabase::match
	protected function match($engine)
	{
		global $config;
		$variables = $this->variables;

		if(!function_exists('pg_connect'))
			return 0;
		foreach($variables as $k => $v)
			if($config->getVariable('database::pgsql', $k)
					!== FALSE)
				return 100;
		return 1;
	}


	//PgsqlDatabase::attach
	protected function attach($engine)
	{
		global $config;
		$str = '';
		$sep = '';

		foreach($this->variables as $k => $v)
			if(($p = $config->getVariable('database::pgsql', $k))
					!== FALSE)
			{
				$str .= $sep.$v."='$p'"; //XXX escape?
				$sep = ' ';
			}
		$this->handle = $config->getVariable('database::pgsql',
			'persistent') ? pg_pconnect($str) : pg_connect($str);
		if($this->handle === FALSE)
			return $engine->log('LOG_ERR',
					'Could not open database');
		return TRUE;
	}


	//PgsqlDatabase::escape
	protected function escape($string)
	{
		if(is_bool($string))
			$string = $string ? '1' : '0';
		if(function_exists('pg_escape_literal'))
			return pg_escape_literal($this->handle, $string);
		return "'".pg_escape_string($this->handle, $string)."'";
	}


	//PgsqlDatabase::prepare
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


	//private
	//properties
	private $handle = FALSE;

	private $variables = array('username' => 'user',
		'password' => 'password',
		'database' => 'dbname', 'hostname' => 'host',
		'port' => 'port',
		'timeout' => 'connect_timeout',
		'service' => 'service');

	//queries
	private $query_enum = 'SELECT
		pg_catalog.pg_get_constraintdef(r.oid) AS constraint
		FROM pg_catalog.pg_class c, pg_catalog.pg_constraint r
		WHERE c.oid=r.conrelid AND c.relname=:table
		AND conname=:field';
}

?>
