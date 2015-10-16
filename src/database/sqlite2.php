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



//SQLite2Database
class SQLite2Database extends Database
{
	//SQLite2Database::~SQLite2Database
	function __destruct()
	{
		if($this->handle === FALSE)
			return;
		sqlite_close($this->handle);
	}


	//public
	//methods
	//accessors
	//SQLite2Database::getLastID
	public function getLastID($engine, $table, $field)
	{
		if($this->handle === FALSE)
			return FALSE;
		//FIXME return the real last ID for $table_$field
		return sqlite_last_insert_rowid($this->handle);
	}


	//useful
	//SQLite2Database::enum
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


	//SQLite2Database::query
	public function query($engine, $query, &$parameters = FALSE,
			$async = FALSE)
	{
		global $config;

		if($this->handle === FALSE)
			return FALSE;
		if(($query = $this->prepare($query, $parameters)) === FALSE)
			return FALSE;
		if($config->get('database', 'debug'))
			$engine->log('LOG_DEBUG', $query);
		$error = FALSE;
		if(($res = sqlite_query($this->handle, $query, SQLITE_ASSOC,
					$error)) === FALSE)
		{
			if($error !== FALSE)
				$engine->log('LOG_DEBUG', $error);
			return FALSE;
		}
		return new SQLite2DatabaseResult($res);
	}


	//functions
	//SQLite2Database::_date_trunc
	public function _date_trunc($where, $value)
	{
		if($where == 'month')
			return substr($value, 0, 8).'01';
		//FIXME really implement
		return $value;
	}


	//protected
	//methods
	//SQLite2Database::match
	protected function match($engine)
	{
		if($this->configGet('filename') !== FALSE)
			return 100;
		return 0;
	}


	//SQLite2Database::attach
	protected function attach($engine)
	{
		if(($filename = $this->configGet('filename')) === FALSE)
			return $engine->log('LOG_ERR',
					'Database filename not defined');
		if(($this->handle = sqlite_open($filename, 0666, $error))
				=== FALSE)
			return $engine->log('LOG_ERR',
					'Could not open database: '.$error);
		$this->engine = $engine;
		$func = array($this, '_date_trunc');
		sqlite_create_function($this->handle, 'date_trunc', $func);
		//default the LIKE keyword to case-sensitive
		$query = 'PRAGMA case_sensitive_like=1';
		$this->query($engine, $query);
		return TRUE;
	}


	//SQLite2Database::escape
	protected function escape($string)
	{
		if(is_bool($string))
			return $string ? "'1'" : "'0'";
		return "'".sqlite_escape_string($string)."'";
	}


	//public
	//useful
	//SQLite2Database::regexp
	public function regexp($case = TRUE, $pattern = FALSE)
	{
		$func = array($this, '_regexp_callback');

		if(!$this->func_regexp)
		{
			$this->func_regexp = TRUE;
			sqlite_create_function($this->handle, 'regexp', $func);
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


	//SQLite2Database::transactionBegin
	public function transactionBegin($engine)
	{
		return parent::transactionBegin($engine);
	}

	protected function _beginTransaction($engine)
	{
		return $this->query($engine, 'BEGIN');
	}


	//private
	//properties
	private $handle = FALSE;
	private $case;
	//functions
	private $func_regexp = FALSE;
}

?>
