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



require_once('./system/engine.php');
require_once('./system/format.php');
require_once('./system/locale.php');
require_once('./system/page.php');
require_once('./system/template.php');


//HTTPEngine
class HTTPEngine extends Engine
{
	//public
	//methods
	//essential
	//HTTPEngine::match
	public function match()
	{
		if(!isset($_SERVER['SERVER_PROTOCOL']))
			return -1;
		switch($_SERVER['SERVER_PROTOCOL'])
		{
			case 'HTTP/1.1':
			case 'HTTP/1.0':
				return 100;
			default:
				if(strncmp($_SERVER['SERVER_PROTOCOL'], 'HTTP/',
							5) == 0)
					return 0;
				break;
		}
		return -1;
	}


	//HTTPEngine::attach
	public function attach()
	{
		$request = $this->getRequest();
		$url = $this->getURL($request);

		Locale::init($this);
		if($this->getDebug())
			$this->log('LOG_DEBUG', 'URL is '.$url);
		if(isset($_SERVER['SCRIPT_NAME'])
				&& substr($_SERVER['SCRIPT_NAME'], -10)
				!= '/index.php')
		{
			//FIXME might be an invalid address
	 		header('Location: '.dirname($url));
			exit(0);
		}
	}


	//accessors
	//HTTPEngine::getModules
	public function getModules()
	{
		global $config;
		$credentials = $this->getCredentials();

		if(!$config->get('engine::http', 'private')
				|| $credentials->getUserID() != 0)
			return parent::getModules();
		if(($module = $config->get('engine::http', 'private::module'))
				=== FALSE)
			return array();
		return array($module);
	}


	//HTTPEngine::getRequest
	public function getRequest()
	{
		global $config;

		if(($private = $config->get('engine::http', 'private'))
				== 1)
			return $this->_getRequestPrivate();
		return $this->_getRequestDo();
	}

	protected function _getRequestDo()
	{
		global $config;

		//XXX hack to avoid testing twice for idempotence
		if($this->request !== FALSE)
			return $this->request;
		$request = array();
		$idempotent = TRUE;
		$module = FALSE;
		$action = FALSE;
		$id = FALSE;
		$title = FALSE;
		$parameters = FALSE;
		if($_SERVER['REQUEST_METHOD'] == 'GET')
			$request = $_GET;
		else if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$request = $_POST;
			$idempotent = FALSE;
		}
		//collect the parameters
		foreach($request as $key => $value)
		{
			$k = get_magic_quotes_gpc() ? stripslashes($key) : $key;
			//FIXME is this sufficient when not a string? (uploads)
			$v = (is_string($value) && get_magic_quotes_gpc())
				? stripslashes($value) : $value;
			switch($k)
			{
				case '_module':
					$module = $request[$key];
					break;
				case '_action':
					$action = $request[$key];
					break;
				case '_id':
					$id = $request[$key];
					break;
				case '_title':
					$title = $request[$key];
					break;
				case '_type':
					$this->setType($request[$key]);
					break;
				default:
					if($parameters === FALSE)
						$parameters = array();
					$parameters[$k] = $v;
					break;
			}
		}
		if($module === FALSE)
			return parent::getRequest();
		$this->request = new Request($module, $action, $id, $title,
			$parameters);
		$auth = $this->getAuth();
		$auth->setIdempotent($this, $this->request, $idempotent);
		return $this->request;
	}

	protected function _getRequestPrivate()
	{
		global $config;
		$cred = $this->getCredentials();
		$module = 'user';
		$actions = array('login');

		if(($m = $config->get('engine::http',
				'private::module')) !== FALSE)
			$module = $m;
		if(($a = $config->get('engine::http',
		       		'private::actions')) !== FALSE)
			$actions = explode(',', $a);
		$request = $this->_getRequestDo();
		if($cred->getUserID() == 0)
			if($request->getModule() != $module
					|| !in_array($request->getAction(),
						$actions))
				return new Request($module, $actions[0]);
		return $request;
	}


	//HTTPEngine::getURL
	public function getURL($request, $absolute = TRUE)
	{
		//FIXME do not include parameters for a POST request
		if($request === FALSE)
			return FALSE;
		$name = isset($_SERVER['SCRIPT_NAME'])
			? ltrim($_SERVER['SCRIPT_NAME'], '/') : '';
		$port = $_SERVER['SERVER_PORT'];
		if($absolute)
		{
			$url = $_SERVER['SERVER_NAME'];
			if(isset($_SERVER['HTTPS']))
			{
				if($port != 443)
					$url .= ':'.$port;
				$url = 'https://'.$url;
			}
			else if($port != 80)
				$url = 'http://'.$url.':'.$port;
			else
				$url = 'http://'.$url;
			$url .= '/'.$name;
		}
		else
			$url = basename($name);
		if(($module = $request->getModule()) !== FALSE)
		{
			$url .= '?_module='.urlencode($module);
			if(($action = $request->getAction()) !== FALSE)
				$url .= '&_action='.urlencode($action);
			if(($id = $request->getID()) !== FALSE)
				$url .= '&_id='.urlencode($id);
			if(($title = $request->getTitle()) !== FALSE)
			{
				$title = str_replace(array(' ', '?'), '-',
					$title);
				$url .= '&_title='.urlencode($title);
			}
			if($request->isIdempotent()
					&& ($args = $request->getParameters())
					!== FALSE)
				foreach($args as $key => $value)
				{
					if($value === FALSE)
						continue;
					$url .= '&'.urlencode($key)
						.'='.urlencode($value);
				}
		}
		return $url;
	}


	//useful
	//HTTPEngine::render
	public function render($page)
	{
		global $config;
		//XXX escape the headers
		$charset = $config->get('defaults', 'charset');

		//render HTML by default
		if($this->getType() === FALSE)
			$this->setType('text/html');
		//mention the content type
		$header = 'Content-Type: '.$this->getType();
		if($charset !== FALSE)
			$header .= '; charset='.$charset;
		header($header);
		//disable caching
		header('Cache-Control: no-cache, must-revalidate');
		return parent::render($page);
	}

	protected function _renderPage($page)
	{
		if($page !== FALSE)
		{
			if(($location = $page->getProperty('location'))
					!== FALSE)
			header('Location: '.$location); //XXX escape
		}
		return parent::_renderPage($page);
	}

	protected function _renderStream($fp)
	{
		$type = $this->getType();

		$disposition = (strncmp('image/', $type, 6) == 0)
			? 'inline' : 'attachment';
		//FIXME also set the filename
		header('Content-Disposition: '.$disposition);
		if(($st = fstat($fp)) !== FALSE)
		{
			header('Content-Length: '.$st['size']);
			$lastm = gmstrftime('%a, %d %b %Y %H:%M:%S',
					$st['mtime']);
			header('Last-Modified: '.$lastm);
		}
		return parent::_renderStream($fp);
	}

	protected function _renderString($string)
	{
		header('Content-Length: '.strlen($string));
		return parent::_renderString($string);
	}


	//protected
	//properties
	protected $request = FALSE;
}

?>
