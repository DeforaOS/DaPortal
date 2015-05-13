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
	//accessors
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
	public function formatDate($engine, $date, $outformat = FALSE,
			$informat = FALSE)
	{
		$informats = array('%Y-%m-%dT%H:%M:%S', '%Y-%m-%d %H:%M:%S');

		if($informat !== FALSE)
			$informats = array($informat);
		foreach($informats as $informat)
		{
			if(($tm = strptime($date, $informat)) === FALSE)
				continue;
			$timestamp = gmmktime($tm['tm_hour'], $tm['tm_min'],
					$tm['tm_sec'], $tm['tm_mon'] + 1,
					$tm['tm_mday'], $tm['tm_year'] + 1900);
			if($outformat === FALSE)
				$outformat = '%d/%m/%Y %H:%M:%S';
			return strftime($outformat, $timestamp);
		}
		return $date; //XXX better suggestions welcome
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
	public function transactionBegin($engine)
	{
		return $this->query($engine, 'BEGIN');
	}


	//Database::transactionCommit
	public function transactionCommit($engine)
	{
		return $this->query($engine, 'COMMIT');
	}


	//Database::transactionRollback
	public function transactionRollback($engine)
	{
		return $this->query($engine, 'ROLLBACK');
	}


	//static
	//Database::attachDefault
	public static function attachDefault($engine)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		if(($name = $config->get('database', 'backend')) !== FALSE)
		{
			$name .= 'Database';
			$ret = new $name();
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
			$name .= 'Database';
			$db = new $name();
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
	abstract public function getLastID($engine, $table, $field);

	abstract public function enum($engine, $table, $field);
	abstract public function query($engine, $query, &$parameters = FALSE,
			$async = FALSE);


	//protected
	//properties
	//profiling
	protected $profile = FALSE;

	//queries
	static protected $query_sql_profile = 'INSERT INTO daportal_sql_profile
		(time, query) VALUES (:time, :query)';


	//methods
	//virtual
	abstract protected function match($engine);
	abstract protected function attach($engine);

	abstract protected function escape($string);


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
	protected function profileStart($engine)
	{
		if($this->profile === FALSE)
			$this->profile = microtime(TRUE);
		return TRUE;
	}


	//Database::profileStop
	protected function profileStop($engine, $query)
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
		if($this->query($engine, static::$query_sql_profile, $args)
				=== FALSE)
			$engine->log('LOG_ERR', $error.' (SQL error)');
		$this->profile = FALSE;
		return TRUE;
	}
}

?>
