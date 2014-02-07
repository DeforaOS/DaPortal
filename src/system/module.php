<?php //$Id$
//Copyright (c) 2011-2014 Pierre Pronchery <khorben@defora.org>
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


	//Module::getTitle
	public function getTitle($engine)
	{
		return $this->title;
	}


	//static
	//useful
	//Module::load
	static public function load($engine, $name)
	{
		if($name === FALSE)
			return FALSE;
		if(($id = Module::_loadID($engine, $name)) === FALSE)
			return FALSE;
		$module = $name.'Module';
		if(!class_exists($module))
		{
			$engine->log('LOG_DEBUG', 'Loading module '.$name);
			if(strchr($name, '_') !== FALSE
					|| strchr($name, '.') !== FALSE
					|| strchr($name, '/') !== FALSE)
				return $engine->log('LOG_DEBUG',
						'Invalid module '.$name);
			$path = './modules/'.$name.'/module.php';
			if(!is_readable($path))
				return $engine->log('LOG_ERR',
						'Unreadable module '.$name);
			$res = include_once($path);
			if($res === FALSE)
				return $engine->log('LOG_DEBUG',
						'Unknown module '.$name);
			if(!class_exists($module))
				return $engine->log('LOG_ERR',
						'Undefined module '.$name);
		}
		if(($ret = new $module($id, $name)) == NULL)
			return $engine->log('LOG_ERR',
					'Uninstanciable module '.$name);
		return $ret;
	}

	static protected function _loadID($engine, $name)
	{
		static $modules = FALSE;
		$db = $engine->getDatabase();

		//load the list of modules if necessary
		if($modules === FALSE)
			$modules = $engine->getModules();
		if(($ret = array_search($name, $modules)) === FALSE)
			return $engine->log('LOG_DEBUG', 'Module '.$name
					.' is not available');
		return $ret;
	}


	//virtual
	public abstract function call($engine, $request, $internal = 0);


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


	//ContentModule::getRequest
	protected function getRequest($action = FALSE, $parameters = FALSE)
	{
		return new Request($this->name, $action, FALSE, FALSE,
				$parameters);
	}
}

?>
