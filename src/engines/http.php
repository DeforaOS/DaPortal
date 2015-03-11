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
			case 'HTTP/0.9':
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
		$index = '/index.php';
		$request = $this->getRequest();
		$url = $this->getURL($request);

		Locale::init($this);
		if($this->getDebug())
			$this->log('LOG_DEBUG', 'URL is '.$url);
		if(isset($_SERVER['SCRIPT_NAME'])
				&& substr($_SERVER['SCRIPT_NAME'],
					-strlen($index)) != $index)
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

		$request = $this->_getRequestDo();
		if(($private = $config->get('engine::http', 'private')) == 1)
			return $this->_getRequestPrivate($request);
		return $request;
	}

	protected function _getRequestDebug()
	{
		$request = $this->request;

		if(($module = $request->getModule()) !== FALSE)
			header('X-DaPortal-Request-Module: '.$module);
		if(($action = $request->getAction()) !== FALSE)
			header('X-DaPortal-Request-Action: '.$action);
		if(($id = $request->getID()) !== FALSE)
			header('X-DaPortal-Request-ID: '.$id);
		if(($title = $request->getTitle()) !== FALSE)
			header('X-DaPortal-Request-Title: '.$title);
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
		$type = FALSE;
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
					$type = $request[$key];
					break;
				default:
					if($parameters === FALSE)
						$parameters = array();
					$parameters[$k] = $v;
					break;
			}
		}
		if($module === FALSE)
			$this->request = parent::getRequest();
		else
		{
			$this->request = new Request($module, $action, $id,
				$title, $parameters);
			$auth = $this->getAuth();
			$auth->setIdempotent($this, $this->request,
					$idempotent);
		}
		if($type !== FALSE && strlen($type) > 0)
			$this->request->setType($type);
		if($this->getDebug())
			$this->_getRequestDebug();
		return $this->request;
	}

	protected function _getRequestPrivate($request)
	{
		global $config;
		$cred = $this->getCredentials();
		$module = 'user';
		$actions = array('login');

		if(($parameters = $request->getParameters()) === FALSE)
			$parameters = array();
		$parameters['module'] = $request->getModule();
		$parameters['action'] = $request->getAction();
		$parameters['id'] = $request->getID();
		$parameters['title'] = $request->getTitle();
		if(($m = $config->get('engine::http',
				'private::module')) !== FALSE)
			$module = $m;
		if(($a = $config->get('engine::http',
		       		'private::actions')) !== FALSE)
			$actions = explode(',', $a);
		if($cred->getUserID() == 0)
			if($parameters['module'] != $module
					|| !in_array($parameters['action'],
						$actions))
				return new Request($module, $actions[0],
					FALSE, FALSE, $parameters);
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
		if($absolute)
		{
			$url = array('scheme' => isset($_SERVER['HTTPS'])
					? 'https' : 'http',
				'host' => $_SERVER['SERVER_NAME'],
				'port' => $_SERVER['SERVER_PORT'],
				'path' => $name);
			if(($url = http_build_url($url)) === FALSE)
				//fallback to a relative address
				$url = basename($name);
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
				$title = str_replace(array(' ', '?', '#'), '-',
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
	public function render($response)
	{
		if(!($response instanceof Response))
		{
			header($_SERVER['SERVER_PROTOCOL']
					.' 500 Internal server error');
			return FALSE;
		}
		switch($response->getCode())
		{
			case Response::$CODE_EPERM:
				header($_SERVER['SERVER_PROTOCOL']
						.' 403 Permission denied');
				break;
			case Response::$CODE_ENOENT:
				header($_SERVER['SERVER_PROTOCOL']
						.' 404 Resource not found');
				break;
			case 0:
				break;
			default:
				header($_SERVER['SERVER_PROTOCOL']
						.' 500 Internal server error');
				break;
		}
		//XXX escape the headers
		//obtain the current content's type (and default to HTML)
		if(($type = $response->getType($this)) === FALSE)
		{
			$type = 'text/html';
			$response->setType($type);
		}
		//set the content type and character set
		$header = 'Content-Type: '.$type;
		if(($charset = $response->getCharset($this)) !== FALSE)
			$header .= '; charset='.$charset;
		header($header);
		//set the disposition
		$disposition = (strncmp('image/', $type, 6) == 0
				|| strncmp('text/', $type, 5) == 0)
			? 'inline' : 'attachment';
		if(($filename = $response->getFilename($this)) !== FALSE)
			//FIXME escape $filename
			$disposition .= '; filename="'.$filename.'"';
		header('Content-Disposition: '.$disposition);
		//set the length
		if(($length = $response->getLength($this)) !== FALSE
				&& is_numeric($length))
			header('Content-Length: '.$length);
		//set the modification time
		if(($mtime = $response->getModified($this)) !== FALSE)
		{
			$mtime = gmstrftime('%a, %d %b %Y %H:%M:%S', $mtime);
			header('Last-Modified: '.$mtime);
		}
		//disable caching
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Thu,  1 Jan 1970 00:00:00 GMT');
		//optional extra fields
		if(($location = $response->get('location')) !== FALSE)
			header('Location: '.$location);
		return parent::render($response);
	}


	//protected
	//properties
	protected $request = FALSE;
}

?>
