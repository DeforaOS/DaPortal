<?php //$Id$
//Copyright (c) 2015-2016 Pierre Pronchery <khorben@defora.org>
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



//PgSQLPoolDatabase
class PgSQLPoolDatabase extends PgSQLDatabase
{
	//public
	//accessors
	//PgSQLPoolDatabase::getLastID
	public function getLastID(Engine $engine = NULL, $table, $field)
	{
		//force the master to perform this query
		$sequence = $this->getSequence($table, $field);
		$query = $this->query_currval;
		$args = array('sequence' => $sequence);
		if(($res = parent::query($this->engine, $query, $args))
				=== FALSE
				|| count($res) != 1)
			return FALSE;
		$res = $res->current();
		return $res['currval'];
	}


	//useful
	//PgSQLPoolDatabase::query
	public function query(Engine $engine = NULL,
			$query, $parameters = FALSE, $async = FALSE)
	{
		//every transaction goes to us (the master)
		if($this->inTransaction()
				//only SELECT statements go to the slaves
				|| strncasecmp($query, 'SELECT', 6) != 0
				//there may be no slave available either
				|| ($slave = $this->getDatabaseSlave())
					== FALSE)
			return parent::query($this->engine, $query, $parameters,
					$async);
		return $slave->query($this->engine, $query, $parameters,
				$async);
	}


	//protected
	//methods
	//PgSQLPoolDatabase::match
	protected function match(Engine $engine)
	{
		global $config;

		if(!function_exists('pg_connect'))
			return 0;
		//do not bother if there is are no slaves
		if(($slaves = $config->get('database::'.$this->name, 'slaves'))
				=== FALSE
				|| strlen(trim($slaves)) == 0)
			return 0;
		return parent::match($engine) + 1;
	}


	//PgSQLPoolDatabase::attach
	protected function attach(Engine $engine)
	{
		global $config;
		$section = 'database::'.$this->name;

		if($this->_attachMaster($engine, $config, $section) === FALSE)
			return $engine->log(LOG_ERR,
					'Could not open database master');
		if($this->_attachSlaves($engine, $config, $section) === FALSE)
			$engine->log(LOG_WARNING,
					'Could not open any database slave');
		return TRUE;
	}

	private function _attachMaster(Engine $engine, Config $config, $section)
	{
		if(($master = $config->get($section, 'master')) === FALSE
				|| strlen($master) == 0)
			return parent::attach($engine);
		return $this->_attachConfig($engine, $config,
				"$section::$master");
	}

	private function _attachSlaves(Engine $engine, Config $config, $section)
	{
		$this->slaves = new \ArrayIterator();
		if(($slaves = $config->get($section, 'slaves')) === FALSE)
			return FALSE;
		$slaves = explode(',', $slaves);
		foreach($slaves as $s)
		{
			$slave = new PgSQLDatabase('pgsql');
			if($slave->_attachConfig($engine, $config,
					"$section::$s", TRUE))
				$this->slaves->append($slave);
			else
				$engine->log(LOG_WARNING, $s.": Could not"
						.' open database slave');
		}
		return ($this->slaves->count() > 0) ? TRUE : FALSE;
	}


	//PgSQLPoolDatabase::escape
	protected function escape($string)
	{
		//prefer slaves for this
		return $this->getDatabase()->escape($string);
	}


	//private
	//properties
	private $slaves;


	//methods
	//PgSQLPoolDatabase::getDatabase
	protected function getDatabase()
	{
		if(($slave = $this->getDatabaseSlave()) !== FALSE)
			return $slave;
		//fallback on ourselves
		return $this;
	}


	//PgSQLPoolDatabase::getDatabaseSlave
	protected function getDatabaseSlave()
	{
		if($this->slaves->count() == 0)
			return FALSE;
		$this->slaves->next();
		if(!$this->slaves->valid())
			$this->slaves->rewind();
		return $this->slaves->current();
	}
}

?>
