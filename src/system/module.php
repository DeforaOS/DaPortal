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



//Module
abstract class Module
{
	//public
	//methods
	//accessors
	//Module::getID
	public function getID()
	{
		return $this->id;
	}


	//Module::getName
	public function getName()
	{
		return $this->name;
	}


	//Module::getRequest
	public function getRequest($action = FALSE, $parameters = FALSE)
	{
		return new Request($this->name, $action, FALSE, FALSE,
				$parameters);
	}


	//Module::getTitle
	public function getTitle(Engine $engine = NULL)
	{
		return $this->title;
	}


	//static
	//useful
	//Module::load
	static public function load(Engine $engine, $name)
	{
		if($name === FALSE)
			return FALSE;
		if(($id = self::_loadID($engine, $name)) === FALSE)
			return FALSE;
		$module = $name.'Module';
		if(($ret = new $module($id, $name)) == NULL)
			return $engine->log(LOG_ERR,
					$name.': Could not load module');
		return $ret;
	}

	static protected function _loadID(Engine $engine, $name)
	{
		static $modules = FALSE;
		$db = $engine->getDatabase();

		//load the list of modules if necessary
		if($modules === FALSE)
			$modules = $engine->getModules();
		if(($ret = array_search($name, $modules)) === FALSE)
			return $engine->log(LOG_DEBUG,
					$name.': Module not available');
		return $ret;
	}


	//virtual
	public abstract function call(Engine $engine, Request $request,
			$internal = 0);


	//protected
	//properties
	protected $id = FALSE;
	protected $name = FALSE;
	protected $title = FALSE;


	//methods
	//essential
	//Module::Module
	protected function __construct($id, $name, $title = FALSE)
	{
		$this->id = $id;
		$this->name = $name;
		$this->title = ($title !== FALSE) ? $title : ucfirst($name);
	}


	//accessors
	//Module::configGet
	protected function configGet($variable)
	{
		global $config;

		return $config->get('module::'.$this->name, $variable);
	}


	//useful
	//helpers
	//Module::helperApply
	protected function helperApply(Engine $engine, Request $request,
			$query, $args, $success, $failure, $key = FALSE)
	{
		$db = $engine->getDatabase();
		$affected = 0;

		//check the parameters
		if($args === FALSE)
			$args = array();
		if($key === FALSE)
			//must be specified
			return FALSE;
		if($request->isIdempotent())
			//must be safe
			return FALSE;
		$type = 'info';
		$message = $success;
		if(($ids = $request->get('ids')) === FALSE || !is_array($ids))
			$ids = array();
		foreach($ids as $id)
		{
			$a = $args;
			$a[$key] = $id;
			if(($res = $db->query($engine, $query, $a)) !== FALSE)
			{
				$affected += $res->getAffectedCount();
				continue;
			}
			$type = 'error';
			$message = $failure;
		}
		return ($affected > 0) ? new PageElement('dialog', array(
				'type' => $type, 'text' => $message)) : FALSE;
	}
}

?>
