<?php //$Id$
//Copyright (c) 2011-2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/auth.php');
require_once('./system/module.php');
require_once('./system/request.php');


//Engine
abstract class Engine
{
	//public
	//virtual
	//essential
	abstract public function match();
	abstract public function attach();

	//useful
	//Engine::render
	public function render($content)
	{
		if($content instanceof PageElement)
			return $this->_renderPage($content);
		else if(is_resource($content)
				&& get_resource_type($content) == 'stream')
			return $this->_renderStream($content);
		else if(is_string($content))
			return $this->_renderString($content);
		//default
		return $this->_renderPage($content);
	}

	protected function _renderPage($page)
	{
		$type = $this->getType();

		switch($type)
		{
			case 'application/rss+xml':
			case 'application/xml':
			case 'text/csv':
			case 'text/xml':
				break;
			case 'text/html':
			default:
				$template = Template::attachDefault(
					$this);
				if($template === FALSE)
					return FALSE;
				if(($page = $template->render($this,
					$page)) === FALSE)
					return FALSE;
				break;
		}
		$error = 'Could not determine the proper output format';
		if(($output = Format::attachDefault($this, $type)) !== FALSE)
			$output->render($this, $page);
		else
			$this->log('LOG_ERR', $error);
	}

	protected function _renderStream($fp)
	{
		$type = $this->getType();
		$format = FALSE;

		if($type !== FALSE)
			$format = Format::attachDefault($this, $type);
		if($format !== FALSE)
			ob_start();
		while(!feof($fp))
			if(($buf = fread($fp, 65536)) !== FALSE)
				print($buf);
		fclose($fp);
		if($format === FALSE)
			return;
		$data = ob_get_contents();
		ob_end_clean();
		$page = new PageElement('data', array('data' => $data));
		$format->render($this, $page);
	}

	protected function _renderString($string)
	{
		print($string);
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
		require_once('./system/database.php');
		if(($this->database = Database::attachDefault($this))
				!== FALSE)
			return $this->database;
		require_once('./database/dummy.php');
		$this->database = new DummyDatabase;
		$this->ret = 2;
		return $this->database;
	}


	//Engine::getDebug
	public function getDebug()
	{
		return Engine::$debug;
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


	//Engine::getType
	public function getType()
	{
		return $this->type;
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


	//Engine::setType
	public function setType($type)
	{
		$this->type = $type;
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
			case 'LOG_INFO':
			case 'LOG_NOTICE':
			default:
				if(Engine::$debug !== TRUE)
					return FALSE;
				$level = 'Info';
				break;
		}
		if(!is_string($message))
		{
			ob_start();
			var_dump($message); //XXX potentially multi-line
			$message = ob_get_contents();
			ob_end_clean();
		}
		$message = $_SERVER['SCRIPT_FILENAME'].": $level: $message";
		error_log($message, 0);
		return FALSE;
	}


	//Engine::process
	public function process($request, $internal = FALSE)
	{
		if($request === FALSE
				|| ($module = $request->getModule()) === FALSE)
			return FALSE;
		$action = $request->getAction();
		$this->log('LOG_DEBUG', 'Processing'
				.($internal ? ' internal' : '')
				." request: module $module"
				.(($action !== FALSE) ? ", action $action"
					: ''));
		if(($handle = Module::load($this, $module)) === FALSE)
			return FALSE;
		return $handle->call($this, $request, $internal);
	}


	//static
	//useful
	//Engine::attachDefault
	public static function attachDefault()
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		if($config->get(FALSE, 'debug') == '1')
			Engine::$debug = TRUE;
		if(($name = $config->get('engine', 'backend')) !== FALSE)
		{
			$res = require_once('./engines/'.$name.'.php');
			if($res === FALSE)
				return FALSE;
			$name .= 'Engine';
			$ret = new $name();
			$ret->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' (default)');
			$ret->attach();
			return $ret;
		}
		if(($dir = opendir('engines')) === FALSE)
			return FALSE;
		while(($de = readdir($dir)) !== FALSE)
		{
			if(substr($de, -4) != '.php')
				continue;
			$res = require_once('./engines/'.$de);
			if($res === FALSE)
				continue;
			$name = substr($de, 0, strlen($de) - 4);
			$name .= 'Engine';
			$engine = new $name();
			if(($p = $engine->match()) <= $priority)
				continue;
			$ret = $engine;
			$priority = $p;
		}
		closedir($dir);
		if($ret !== FALSE)
		{
			$ret->attach();
			$ret->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
		}
		return $ret;
	}


	//protected
	//properties
	protected static $debug = FALSE;


	//private
	//properties
	private $type = FALSE;
	private $auth = FALSE;
	private $database = FALSE;
	private $ret = 0;


	//methods
	private function _attachAuth()
	{
		if($this->auth !== FALSE)
			return TRUE;
		require_once('./system/auth.php');
		$this->auth = Auth::attachDefault($this);
		return ($this->auth !== FALSE) ? TRUE : FALSE;
	}
}

?>
