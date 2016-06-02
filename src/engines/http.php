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
		global $config;
		$index = '/index.php';
		$request = $this->getRequest();
		$url = $this->getURL($request);
		$secure = $config->get('engine::http', 'secure');
		$timeout = $config->get('engine::http',
				'secure::hsts::timeout');

		DaPortal\Locale::init($this);
		if($this->getDebug())
			$this->log(LOG_DEBUG, 'URL is '.$url);
		if(isset($_SERVER['SCRIPT_NAME'])
				&& substr($_SERVER['SCRIPT_NAME'],
					-strlen($index)) != $index)
		{
			//FIXME might be an invalid address
	 		header('Location: '.dirname($url));
			exit(0);
		}
		if(isset($_SERVER['HTTPS']))
		{
			if($secure >= 2 && !is_numeric($timeout))
				$timeout = 10886400;
			if(is_numeric($timeout))
				//enable HSTS
				header('Strict-Transport-Security: '
					.'max-age='.$timeout);
		}
		else if($secure >= 1)
		{
			//redirect to HTTPS right away
			header('Location: '.$this->getURL($request));
			exit(0);
		}
	}


	//accessors
	//HTTPEngine::getModules
	public function getModules($reset = FALSE)
	{
		global $config;
		$credentials = $this->getCredentials();

		if(!$config->get('engine::http', 'private')
				|| $credentials->getUserID() != 0)
			return parent::getModules($reset);
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
		$idempotent = TRUE;
		$module = FALSE;
		$action = FALSE;
		$id = FALSE;
		$title = FALSE;
		$args = FALSE;
		$type = FALSE;
		if(!isset($_SERVER['REQUEST_METHOD']))
			$request = array();
		else if($_SERVER['REQUEST_METHOD'] == 'GET')
			$request = $_GET;
		else if($_SERVER['REQUEST_METHOD'] == 'HEAD')
		{
			$request = $_GET;
			$this->setVerbose(FALSE);
		}
		else if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$request = $_POST;
			$idempotent = FALSE;
		}
		else
			$request = array();
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
					if($args === FALSE)
						$args = array();
					//convert to an array as really expected
					if(is_array($v))
					{
						$args[$k] = array();
						foreach($v as $k2 => $v2)
							$args[$k][] = $k2;
					}
					else
						$args[$k] = $v;
					break;
			}
		}
		if($module === FALSE)
			$this->request = parent::getRequest();
		else
		{
			$this->request = new Request($module, $action, $id,
				$title, $args);
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

	protected function _getRequestPrivate(Request $request)
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
	public function getURL(Request $request = NULL, $absolute = TRUE)
	{
		global $config;
		$secure = $config->get('engine::http', 'secure');

		$name = isset($_SERVER['SCRIPT_NAME'])
			? ltrim($_SERVER['SCRIPT_NAME'], '/') : '';
		if((!isset($_SERVER['HTTPS']) && $secure) || $absolute)
			$url = $this->_getURLAbsolute($name, $secure);
		else
			$url = basename($name);
		//return if already complete
		if(is_null($request)
				|| ($module = $request->getModule()) === FALSE)
			return $url;
		$url .= '?_module='.rawurlencode($module);
		if(($action = $request->getAction()) !== FALSE)
			$url .= '&_action='.rawurlencode($action);
		if(($id = $request->getID()) !== FALSE)
			$url .= '&_id='.rawurlencode($id);
		if(($title = $request->getTitle()) !== FALSE)
		{
			$title = str_replace(array(' ', '?', '#'), '-',
					$title);
			$url .= '&_title='.rawurlencode($title);
		}
		if($request->isIdempotent()
				&& ($args = $request->getParameters())
				!== FALSE)
		{
			$sep = '&';
			foreach($args as $key => $value)
				$url .= $this->_getURLParameter($key, $value,
						$sep);
		}
		return $url;
	}

	protected function _getURLAbsolute($name, $secure)
	{
		if($secure && !isset($_SERVER['HTTPS']))
		{
			$scheme = 'https';
			$port = 443;
		}
		else if(isset($_SERVER['HTTPS']))
		{
			$scheme = 'https';
			$port = isset($_SERVER['SERVER_PORT'])
				? $_SERVER['SERVER_PORT'] : 443;
		}
		else
		{
			$scheme = 'http';
			$port = isset($_SERVER['SERVER_PORT'])
				? $_SERVER['SERVER_PORT'] : 80;
		}
		$host = isset($_SERVER['SERVER_NAME'])
			? $_SERVER['SERVER_NAME'] : gethostname();
		$url = array('scheme' => $scheme, 'host' => $host,
			'port' => $port, 'path' => $name);
		if(($url = http_build_url($url)) === FALSE)
			//fallback to a relative address
			$url = basename($name);
		return $url;
	}

	protected function _getURLParameter($key, $value, &$sep)
	{
		$ret = '';

		if($value === FALSE)
			return '';
		else if($value === TRUE)
			$value = 1;
		if(is_array($value))
			foreach($value as $v)
			{
				$ret .= $this->_getURLParameter($key."[$v]",
						'on', $sep);
				$sep = '&';
			}
		else if(is_scalar($value))
		{
			$ret = $sep.rawurlencode($key).'='.rawurlencode($value);
			$sep = '&';
		}
		return $ret;
	}


	//useful
	//HTTPEngine::render
	public function render(Response $response)
	{
		if($response instanceof ErrorResponse)
		{
			//render ErrorResponse like a PageResponse dialog
			$page = new PageElement('dialog', array(
					'type' => 'error',
					'text' => $response->getContent()));
			$response = new PageResponse($page,
				$response->getCode());
		}
		$this->_renderCode($response->getCode());
		//XXX escape the headers
		//obtain the current content's type (and default to HTML)
		if(($type = $response->getType()) === FALSE)
		{
			$type = 'text/html';
			$response->setType($type);
		}
		//set the content type and character set
		$header = 'Content-Type: '.$type;
		if(($charset = $response->getCharset()) !== FALSE)
			$header .= '; charset='.$charset;
		header($header);
		//set the disposition
		$disposition = (strncmp('image/', $type, 6) == 0
				|| strncmp('text/', $type, 5) == 0)
			? 'inline' : 'attachment';
		if(($filename = $response->getFilename()) !== FALSE)
			//FIXME escape $filename
			$disposition .= '; filename="'.$filename.'"';
		header('Content-Disposition: '.$disposition);
		//set the length
		if(($length = $response->getLength()) !== FALSE
				&& is_numeric($length))
			header('Content-Length: '.$length);
		//set the modification time
		if(($mtime = $response->getModified()) !== FALSE)
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
		if($this->getVerbose())
			return $response->render($this);
		return 0;
	}

	private function _renderCode($code)
	{
		$reason = 'Internal Server Error';

		switch($code)
		{
			case Response::$CODE_EINVAL:
				$code = 400;
				$reason = 'Bad Request';
				break;
			case Response::$CODE_EACCES:
				$code = 401;
				$reason = 'Unauthorized';
				break;
			case Response::$CODE_EPERM:
				$code = 403;
				$reason = 'Forbidden';
				break;
			case Response::$CODE_ENOENT:
				$code = 404;
				$reason = 'Resource Not Found';
				break;
			case Response::$CODE_SUCCESS:
				return;
			default:
				$code = 500;
				break;
		}
		header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$reason);
	}


	//protected
	//properties
	protected $request = FALSE;
}

?>
