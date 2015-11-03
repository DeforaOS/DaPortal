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
		if($response === FALSE)
			$response = new ErrorResponse();
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
		$this->database = new DummyDatabase('dummy');
		$this->ret = 2;
		return $this->database;
	}


	//Engine::getDebug
	public function getDebug()
	{
		return $this->debug;
	}


	//Engine::getDefaultType
	public function getDefaultType()
	{
		return 'text/html';
	}


	//Engine::getModules
	public function getModules($reset = FALSE)
	{
		static $modules = array();
		$database = $this->getDatabase();
		$query = static::$query_modules;

		if($reset !== FALSE)
			$modules = array();
		else if(count($modules) != 0)
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


	//Engine::getVerbose
	public function getVerbose()
	{
		return $this->verbose;
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
		$this->debug = $debug ? TRUE : FALSE;
		if($this->debug)
			set_error_handler(function($errno, $errstr,
					$errfile = FALSE, $errline = FALSE,
					$errcontext = FALSE)
		{
			if(error_reporting() == 0)
				return FALSE;
			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$backtrace = ob_get_contents();
			ob_clean();
			$backtrace = explode("\n", trim($backtrace));
			foreach($backtrace as $b)
				$this->log('LOG_DEBUG', $b);
			return FALSE;
		});
		else
			restore_error_handler();
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
		$message = $this->logMessage($priority, $message);
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
		if(($module = Module::load($this, $module)) === FALSE)
			$ret = new ErrorResponse(_('Could not load the module'),
					Response::$CODE_ENOENT);
		else
			$ret = $module->call($this, $request, $internal);
		if($internal)
			return $ret;
		if(!($ret instanceof Response))
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
		if($ret === FALSE)
			return error_log('Could not load any engine');
		//XXX ignore errors
		static::configLoadEngine($prefix, $name, FALSE);
		$ret->log('LOG_DEBUG', 'Attaching '.get_class($ret)
				.' with priority '.$priority);
		$ret->attach();
		$debug = ($config->get(FALSE, 'debug')
			|| $config->get("engine::$name", 'debug'));
		$ret->setDebug($debug);
		static::_defaultBootstrap();
		return $ret;
	}

	static protected function _defaultBootstrap()
	{
		$dirname = 'bootstrap';

		if(!is_dir($dirname))
			return TRUE;
		if(!is_readable($dirname))
			return FALSE;
		if(($dir = opendir($dirname)) === FALSE)
			return FALSE;
		while(($de = readdir($dir)) !== FALSE)
			if(substr($de, -4) == '.php')
				require("$dirname/$de");
		closedir($dir);
		return TRUE;
	}


	//Engine::configLoad
	//loads the default configuration file
	static public function configLoad($prefix = FALSE, $reset = TRUE)
	{
		$daportalconf = ($prefix !== FALSE)
			? $prefix.'/etc/daportal.conf' : FALSE;

		if(($d = getenv('DAPORTALCONF')) !== FALSE)
			$daportalconf = $d;
		if($daportalconf === FALSE)
		{
			error_log('Could not load any configuration file');
			return FALSE;
		}
		return static::configLoadFilename($daportalconf, $reset);
	}


	//Engine::configLoadEngine
	//loads the default configuration file for a specific engine
	static public function configLoadEngine($prefix = FALSE, $name = FALSE,
			$reset = TRUE)
	{
		$daportalconf = ($prefix !== FALSE && $name !== FALSE)
			? $prefix.'/etc/daportal-'.$name.'.conf' : FALSE;

		if(($d = getenv('DAPORTALCONF')) !== FALSE)
			return FALSE;
		if($daportalconf === FALSE)
		{
			error_log($name.': Could not load configuration file');
			return FALSE;
		}
		if(!file_exists($daportalconf))
			return TRUE;
		return static::configLoadFilename($daportalconf, $reset);
	}


	//Engine::configLoadFilename
	//loads a specific configuration file
	static public function configLoadFilename($filename, $reset = TRUE)
	{
		global $config;

		if($reset)
			$config = new Config();
		if($config->load($filename) === FALSE)
		{
			$error = 'Could not load configuration file';
			error_log($filename.': '.$error);
			return FALSE;
		}
		return TRUE;
	}


	//protected
	//properties
	protected $verbose = 1;
	//queries
	static protected $query_modules = "SELECT module_id AS id, name
		FROM daportal_module
		WHERE enabled='1'
		ORDER BY name ASC";


	//methods
	//Engine::logBacktrace
	protected function logBacktrace($priority = 'LOG_DEBUG')
	{
		$ret = '';
		$sep = '';

		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		for($i = 1, $cnt = count($backtrace); $i < $cnt; $i++)
		{
			$ret .= $sep.'#'.($i - 1).': ';
			if(isset($backtrace[$i]['class']))
				$ret .= $backtrace[$i]['class'];
			if(isset($backtrace[$i]['type']))
				$ret .= $backtrace[$i]['type'];
			if(isset($backtrace[$i]['function']))
				$ret .= $backtrace[$i]['function'].'()';
			$at = '';
			if(isset($backtrace[$i]['file']))
			{
				$at .= '['.$backtrace[$i]['file'];
				if(isset($backtrace[$i]['line']))
					$at .= ':'.$backtrace[$i]['line'];
				$at .= ']';
			}
			else if(isset($backtrace[$i]['line']))
				$at .= 'line '.$backtrace[$i]['line'];
			if(!empty($at))
				$ret .= " called at $at";
			$sep = "\n";
		}
		return $this->log($priority, $ret);
	}


	//Engine::logMessage
	protected function logMessage($priority, $message)
	{
		switch($priority)
		{
			case 'LOG_ALERT':
			case 'LOG_CRIT':
			case 'LOG_EMERG':
				$level = 'Alert';
				break;
			case 'LOG_DEBUG':
				if($this->debug !== TRUE)
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
				if($this->verbose < 2 && $this->debug !== TRUE)
					return FALSE;
				$level = 'Info';
				break;
			default:
				if($this->debug !== TRUE)
					return FALSE;
				$level = 'Unknown';
				break;
		}
		if(!is_string($message))
			$message = var_export($message, TRUE);
		$prefix = $_SERVER['SCRIPT_FILENAME'].": $level: ";
		$message = str_replace("\n", "\n$prefix", $message);
		return $prefix.$message;
	}


	//private
	//properties
	private $auth = FALSE;
	private $database = FALSE;
	private $debug = FALSE;
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
