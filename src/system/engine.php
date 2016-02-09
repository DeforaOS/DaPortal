<?php //$Id$
//Copyright (c) 2011-2016 Pierre Pronchery <khorben@defora.org>
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
	public function render(Response $response)
	{
		if($this->verbose == 0)
			return $response->getCode();
		return $response->render($this);
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
		if(($res = $database->query(NULL, $query)) === FALSE)
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
	public function getURL(Request $request = NULL, $absolute = TRUE)
	{
		if($absolute === FALSE)
			return $_SERVER['SCRIPT_NAME'];
		$filename = $_SERVER['SCRIPT_NAME'][0] == '/'
			? $_SERVER['SCRIPT_NAME']
			: $_SERVER['PWD'].'/'.$_SERVER['SCRIPT_NAME'];
		return realpath($filename);
	}


	//Engine::getVerbose
	public function getVerbose()
	{
		return $this->verbose;
	}


	//Engine::setCredentials
	public function setCredentials(AuthCredentials $credentials = NULL)
	{
		if($this->_attachAuth() === FALSE)
			return FALSE;
		return $this->auth->setCredentials($this, $credentials);
	}


	//Engine::setDebug
	public function setDebug($debug)
	{
		$this->debug = $debug ? TRUE : FALSE;
		if($this->debug)
		{
			set_error_handler(function($errno, $errstr,
					$errfile = FALSE, $errline = FALSE,
					$errcontext = FALSE)
			{
				if((error_reporting() & $errno) == 0)
					return FALSE;
				return $this->logBacktrace();
			});
			//XXX no type hint for compatibility with PHP 7
			set_exception_handler(function($e)
			{
				$this->logException($e, 'LOG_ERR');
				exit(125);
			});
		}
		else
		{
			restore_exception_handler();
			restore_error_handler();
		}
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
		if(($message = $this->logMessage($priority, $message))
				!== FALSE)
			error_log($message, 0);
		return FALSE;
	}


	//Engine::process
	public function process(Request $request, $internal = FALSE)
	{
		//return an empty page if no valid request is provided
		if(($module = $request->getModule()) === FALSE)
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
	static public function attachDefault($prefix = FALSE,
			$sysconfdir = FALSE)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		if($sysconfdir === FALSE && $prefix !== FALSE)
			$sysconfdir = ($prefix == '/usr')
				? '/etc' : $prefix.'/etc';
		//XXX ignore errors
		static::configLoad($sysconfdir, TRUE);
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
	static public function configLoad($sysconfdir = FALSE, $reset = TRUE)
	{
		$daportalconf = ($sysconfdir !== FALSE)
			? $sysconfdir.'/daportal.conf' : FALSE;

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
	static public function configLoadEngine($sysconfdir = FALSE,
			$name = FALSE, $reset = TRUE)
	{
		$daportalconf = ($sysconfdir !== FALSE)
			? $sysconfdir.'/daportal.conf' : FALSE;

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
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		array_shift($backtrace);
		return $this->logTrace($backtrace, $priority);
	}


	//Engine::logException
	protected function logException(Exception $exception,
			$priority = 'LOG_DEBUG')
	{
		$this->logTrace($exception->getTrace());
		$message = "Uncaught exception '".$exception->getMessage()."'";
		if(($code = $exception->getCode()) != 0)
			$message .= " (code $code)";
		$message .= ' in file '.$exception->getFile().':'
			.$exception->getLine();
		return $this->log($priority, $message);
	}


	//Engine::logTrace
	protected function logTrace($trace, $priority = 'LOG_DEBUG')
	{
		$ret = '';
		$sep = '';

		for($cnt = count($trace), $i = $cnt; $i > 0;)
		{
			$ret .= $sep.'#'.(--$i).': ';
			if(isset($trace[$i]['class']))
				$ret .= $trace[$i]['class'];
			if(isset($trace[$i]['type']))
				$ret .= $trace[$i]['type'];
			if(isset($trace[$i]['function']))
				$ret .= $trace[$i]['function'].'()';
			$at = '';
			if(isset($trace[$i]['file']))
			{
				$at .= '['.$trace[$i]['file'];
				if(isset($trace[$i]['line']))
					$at .= ':'.$trace[$i]['line'];
				$at .= ']';
			}
			else if(isset($trace[$i]['line']))
				$at .= 'line '.$trace[$i]['line'];
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
