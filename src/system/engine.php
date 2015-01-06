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



//Engine
abstract class Engine
{
	//public
	//methods
	//essential
	abstract public function match();
	abstract public function attach();


	//useful
	//Engine::render
	public function render($response)
	{
		if($response instanceof Response)
		{
			if($this->verbose == 0)
				return $response->getCode();
			return $response->render($this);
		}
		return $this->log('LOG_ERR', 'Invalid response');
	}


	//methods
	//accessors
	//Engine::getAuth
	public function getAuth()
	{
		return ($this->_attachAuth() !== FALSE) ? $this->auth : FALSE;
	}


	//Engine::getCredentials
	public function getCredentials()
	{
		if($this->_attachAuth() === FALSE)
			return new AuthCredentials;
		return $this->auth->getCredentials($this);
	}


	//Engine::getDatabase
	public function getDatabase()
	{
		if($this->database !== FALSE)
			return $this->database;
		if(($this->database = Database::attachDefault($this))
				!== FALSE)
			return $this->database;
		$this->database = new DummyDatabase;
		$this->ret = 2;
		return $this->database;
	}


	//Engine::getDebug
	public function getDebug()
	{
		return Engine::$debug;
	}


	//Engine::getDefaultType
	public function getDefaultType()
	{
		return 'text/html';
	}


	//Engine::getModules
	public function getModules()
	{
		static $modules = array();
		$database = $this->getDatabase();
		$query = static::$query_modules;

		if(count($modules) != 0)
			return $modules;
		if(($res = $database->query($this, $query)) === FALSE)
			return $modules;
		foreach($res as $r)
			$modules[$r['id']] = $r['name'];
		return $modules;
	}


	//Engine::getRequest
	public function getRequest()
	{
		global $config;

		//return the default request
		return new Request($config->get('defaults', 'module'),
			$config->get('defaults', 'action'),
			$config->get('defaults', 'id'));
	}


	//Engine::getReturn
	public function getReturn()
	{
		return $this->ret;
	}


	//Engine::getURL
	public function getURL($request, $absolute = TRUE)
	{
		return FALSE;
	}


	//Engine::setCredentials
	public function setCredentials($cred)
	{
		if($this->_attachAuth() === FALSE)
			return FALSE;
		return $this->auth->setCredentials($this, $cred);
	}


	//Engine::setDebug
	public function setDebug($debug)
	{
		$this->debug = ($debug !== FALSE) ? TRUE : FALSE;
	}


	//Engine::setVerbose
	public function setVerbose($verbose)
	{
		$this->verbose = $verbose ? 2 : 0;
	}


	//useful
	//Engine::log
	public function log($priority, $message)
	{
		switch($priority)
		{
			case 'LOG_ALERT':
			case 'LOG_CRIT':
			case 'LOG_EMERG':
				$level = 'Alert';
				break;
			case 'LOG_DEBUG':
				if(Engine::$debug !== TRUE)
					return FALSE;
				$level = 'Debug';
				break;
			case 'LOG_ERR':
				$level = 'Error';
				break;
			case 'LOG_WARNING':
				$level = 'Warning';
				break;
			case 'LOG_NOTICE':
				$level = 'Notice';
				break;
			case 'LOG_INFO':
				if($this->verbose < 2
						&& Engine::$debug !== TRUE)
					return FALSE;
				$level = 'Info';
				break;
			default:
				if(Engine::$debug !== TRUE)
					return FALSE;
				$level = 'Unknown';
				break;
		}
		if(!is_string($message))
			var_export($message, TRUE); //XXX potentially multi-line
		$message = $_SERVER['SCRIPT_FILENAME'].": $level: $message";
		error_log($message, 0);
		return FALSE;
	}


	//Engine::process
	public function process($request, $internal = FALSE)
	{
		//return an empty page if no valid request is provided
		if($request === FALSE
				|| ($module = $request->getModule()) === FALSE)
			return new PageResponse(FALSE);
		//preserve the type
		$type = $request->getType();
		//obtain the response
		$action = $request->getAction();
		$this->log('LOG_DEBUG', 'Processing'
				.($internal ? ' internal' : '')
				." request: module $module"
				.(($action !== FALSE) ? ", action $action"
					: ''));
		if(($handle = Module::load($this, $module)) === FALSE)
			//XXX report errors?
			$ret = new PageResponse(FALSE);
		else
			$ret = $handle->call($this, $request, $internal);
		if($internal)
			return $ret;
		//XXX every call should return a response directly instead
		if($ret instanceof PageElement)
			$ret = new PageResponse($ret);
		else if(is_resource($ret))
			$ret = new StreamResponse($ret);
		else if(is_string($ret))
			$ret = new StringResponse($ret);
		else if(!($ret instanceof Response))
			return $this->log('LOG_ERR', 'Unknown response type');
		//check if the request recommends a default type
		if($type === FALSE)
			$type = $request->getType();
		//restore the type if not already enforced
		if($type !== FALSE && $ret->getType() === FALSE)
			$ret->setType($type);
		return $ret;
	}


	//static
	//useful
	//Engine::attachDefault
	static public function attachDefault($prefix = FALSE)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		//XXX ignore errors
		static::configLoad($prefix, TRUE);
		if($config->get(FALSE, 'debug') == '1')
			Engine::$debug = TRUE;
		if(($name = $config->get('engine', 'backend')) !== FALSE)
		{
			$class = $name.'Engine';
			$ret = new $class();
		}
		else if(($dir = opendir('engines')) !== FALSE)
		{
			while(($de = readdir($dir)) !== FALSE)
			{
				if(substr($de, -4) != '.php')
					continue;
				$n = substr($de, 0, strlen($de) - 4);
				$class = $n.'Engine';
				$engine = new $class();
				if(($p = $engine->match()) <= $priority)
					continue;
				$ret = $engine;
				$name = $n;
				$priority = $p;
			}
			closedir($dir);
		}
		if($ret !== FALSE)
		{
			if($config->get("engine::$name", 'debug') == 1)
				Engine::$debug = TRUE;
			$ret->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
			$ret->attach();
		}
		return $ret;
	}


	//Engine::configLoad
	static public function configLoad($prefix = FALSE, $reset = TRUE)
	{
		global $config;
		$ret = TRUE;
		$daportalconf = ($prefix !== FALSE)
			? $prefix.'/etc/daportal.conf' : FALSE;

		if($reset)
			$config = new Config();
		if(($d = getenv('DAPORTALCONF')) !== FALSE)
			$daportalconf = $d;
		if($daportalconf !== FALSE)
			if(($ret = $config->load($daportalconf)) === FALSE)
			{
				$error = 'Could not load configuration file';
				error_log($daportalconf.': '.$error);
			}
		return $ret;
	}


	//protected
	//properties
	static protected $debug = FALSE;
	protected $verbose = 1;
	//queries
	static protected $query_modules = "SELECT module_id AS id, name
		FROM daportal_module
		WHERE enabled='1'
		ORDER BY name ASC";


	//private
	//properties
	private $auth = FALSE;
	private $database = FALSE;
	private $ret = 0;


	//methods
	private function _attachAuth()
	{
		if($this->auth !== FALSE)
			return TRUE;
		$this->auth = Auth::attachDefault($this);
		return ($this->auth !== FALSE) ? TRUE : FALSE;
	}
}

?>
