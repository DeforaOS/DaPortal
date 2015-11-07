<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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



//HTTPFriendlyEngine
class HTTPFriendlyEngine extends HTTPEngine
{
	//public
	//methods
	//essential
	//HTTPFriendlyEngine::match
	public function match()
	{
		if(($score = parent::match()) != 100)
			return $score;
		return $score + 1;
	}


	//accessors
	//HTTPFriendlyEngine::getRequest
	public function getRequest()
	{
		return parent::getRequest();
	}

	protected function _getRequestDo()
	{
		//XXX hack to avoid testing twice for idempotence
		if($this->request !== FALSE)
			return $this->request;
		if(!isset($_SERVER['PATH_INFO']))
			return parent::_getRequestDo();
		$path = explode('/', $_SERVER['PATH_INFO']);
		if(!is_array($path) || count($path) < 2)
			return parent::_getRequestDo();
		//the first element is empty
		array_shift($path);
		//the second element is the module name
		if(($module = array_shift($path)) == '')
			return parent::_getRequestDo();
		$action = FALSE;
		$id = FALSE;
		$title = FALSE;
		$type = FALSE;
		$extension = FALSE;
		$args = FALSE;
		if(count($path) > 0)
		{
			//the third element is the action (or the ID directly)
			$id = array_shift($path);
			if(!is_numeric($id))
			{
				//there is an action before the ID
				$action = $id;
				$id = FALSE;
				if(count($path) > 0 && is_numeric($path[0]))
					$id = array_shift($path);
			}
		}
		if(count($path) > 0)
			$title = implode('/', $path);
		//arguments
		//XXX is there a function to do this directly?
		$query = explode('&', $_SERVER['QUERY_STRING']);
		foreach($query as $q)
		{
			$q = explode('=', $q);
			if(count($q) < 2)
				continue;
			if($args === FALSE)
				$args = array();
			$q0 = urldecode(array_shift($q));
			$q1 = urldecode(implode('=', $q));
			if($title === FALSE && $q0 == '_title')
				$title = $q1;
			else if($q0 == '_type')
				$type = $q1;
			else if(($pos = strpos($q0, '[')) !== FALSE
					&& $pos > 0 && substr($q0, -1) == ']')
			{
				//convert to an array as really expected
				$key = substr($q0, 0, $pos);
				$value = substr($q0, $pos + 1, -1);
				if(!isset($args[$key]))
					$args[$key] = array($value);
				else
					$args[$key][] = $value;
			}
			else
				$args[$q0] = $q1;
		}
		$this->request = new Request($module, $action, $id, $title,
			$args);
		if($type !== FALSE && strlen($type) > 0)
			$this->request->setType($type);
		if($this->getDebug())
			$this->_getRequestDebug();
		return $this->request;
	}


	//HTTPFriendlyEngine::getURL
	public function getURL(Request $request, $absolute = TRUE)
	{
		global $config;

		//FIXME do not include parameters for a POST request
		//use the kicker if defined
		if(($kicker = $config->get($this->section, 'kicker'))
				!== FALSE)
			$name = dirname($_SERVER['SCRIPT_NAME']).'/'.$kicker;
		else
			$name = $_SERVER['SCRIPT_NAME'];
		$name = ltrim($name, '/');
		if($absolute)
		{
			$url = array('scheme' => isset($_SERVER['HTTPS'])
					? 'https' : 'http',
				'host' => isset($_SERVER['SERVER_NAME'])
					? $_SERVER['SERVER_NAME']
					: gethostname(),
				'port' => isset($_SERVER['SERVER_PORT'])
					? $_SERVER['SERVER_PORT']: 80,
				'path' => $name);
			if(($url = http_build_url($url)) === FALSE)
				//fallback to a relative address
				$url = basename($name);
		}
		else
			//prepare a relative address
			$url = basename($name);
		//return if already complete
		if(($module = $request->getModule()) === FALSE)
			return $url;
		//handle the main parameters
		$url .= '/'.$module;
		if(($action = $request->getAction()) !== FALSE)
			$url .= '/'.$action;
		if(($id = $request->getID()) !== FALSE)
			$url .= '/'.$id;
		if(($title = $request->getTitle()) !== FALSE)
		{
			if($action === FALSE && $id === FALSE)
				$url .= '/default';
			$title = str_replace(array(' ', '?', '#'),
					array('%20', '%3F', '-'), $title);
			if($config->get($this->section, 'lowercase'))
				$title = strtolower($title);
			$url .= '/'.$title;
		}
		//handle arguments
		if($request->isIdempotent()
				&& ($args = $request->getParameters())
				!== FALSE)
		{
			$sep = '?';
			foreach($args as $key => $value)
				$url .= $this->_getURLParameter($key, $value,
						$sep);
		}
		return $url;
	}


	//private
	//properties
	private $section = 'engine::httpfriendly';
}

?>
