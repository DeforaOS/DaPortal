<?php //$Id$
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/mutator.php');
//XXX for PageResponse (and StreamResponse)
require_once('./system/format.php');
require_once('./system/page.php');
require_once('./system/template.php');


//Response
abstract class Response extends Mutator
{
	//public
	//methods
	//essential
	//Response::Response
	public function __construct($content, $code = 0)
	{
		$this->setCode($code);
		$this->setContent($content);
	}


	//accessors
	//Response::getCharset
	public function getCharset()
	{
		global $config;

		return $config->get('defaults', 'charset');
	}


	//Response::getCode
	public function getCode()
	{
		return $this->code;
	}


	//Response::getContent
	public function getContent()
	{
		return $this->content;
	}


	//Response::getFilename
	public function getFilename()
	{
		return $this->filename;
	}


	//Response::getLength
	public function getLength()
	{
		return FALSE;
	}


	//Response::getModified
	public function getModified()
	{
		return FALSE;
	}


	//Response::getType
	public function getType()
	{
		return $this->type;
	}


	//Response::setCode
	public function setCode($code)
	{
		if(!is_integer($code))
			return FALSE;
		$this->code = $code;
		return TRUE;
	}


	//Response::setContent
	public function setContent($content)
	{
		$this->content = $content;
		return TRUE;
	}


	//Response::setFilename
	public function setFilename($filename)
	{
		if($filename !== FALSE && (!is_string($filename)
					|| strlen($filename) <= 0))
			return FALSE;
		$this->filename = $filename;
		return TRUE;
	}


	//Response::setType
	public function setType($type)
	{
		$this->type = $type;
		return TRUE;
	}


	//useful
	//Response::render
	abstract public function render($engine);


	//properties
	static public $CODE_SUCCESS = 0;
	static public $CODE_EINVAL = 1;
	static public $CODE_EIO = 2;
	static public $CODE_ENOENT = 3;
	static public $CODE_EPERM = 4;


	//protected
	//properties
	protected $code = 0;
	protected $content = FALSE;
	protected $filename = FALSE;
	protected $type = FALSE;
}


//PageResponse
class PageResponse extends Response
{
	//public
	//methods
	//accessors
	//PageResponse::get
	public function get($name)
	{
		if(($ret = parent::get($name)) !== FALSE)
			return $ret;
		//XXX fallback on page properties
		//XXX an engine is required
		if(($page = $this->getContent(FALSE)) !== FALSE)
			return $page->getProperty($name);
		return FALSE;
	}


	//useful
	//PageResponse::render
	public function render($engine)
	{
		$page = $this->getContent();
		$type = $this->getType();

		switch($type)
		{
			case 'text/html':
				$template = Template::attachDefault($engine);
				if($template === FALSE)
					break;
				if(($p = $template->render($engine, $page))
						!== FALSE)
					$page = $p;
				break;
		}
		$error = 'Could not determine the proper output format';
		if(($output = Format::attachDefault($engine, $type)) !== FALSE)
			$output->render($engine, $page);
		else
		{
			$engine->log('LOG_ERR', $error);
			return Response::$CODE_EIO;
		}
		return $this->getCode();
	}
}


//StreamResponse
class StreamResponse extends Response
{
	//public
	//methods
	//accessors
	//StreamResponse::getLength
	public function getLength()
	{
		if(isset($this->stat['size']))
			return $this->stat['size'];
		return FALSE;
	}


	//StreamResponse::getModified
	public function getModified()
	{
		if(isset($this->stat['mtime']))
			return $this->stat['mtime'];
		return FALSE;
	}


	//StreamResponse::setContent
	public function setContent($content)
	{
		$this->stat = fstat($content);
		return parent::setContent($content);
	}


	//useful
	//StreamResponse::render
	public function render($engine)
	{
		$fp = $this->getContent();

		//XXX fpassthru() would be better but allocates too much memory
		while(!feof($fp))
			if(($buf = fread($fp, 65536)) !== FALSE)
				print($buf);
		fclose($fp);
		return TRUE;
	}


	//protected
	//properties
	protected $stat = FALSE;
}


//StringResponse
class StringResponse extends Response
{
	//public
	//methods
	//accessors
	//StringResponse::getContent
	public function getContent()
	{
		if(!is_string($this->content))
			return '';
		return $this->content;
	}


	//StringResponse::getLength
	public function getLength()
	{
		return strlen($this->getContent());
	}


	//StringResponse::setContent
	public function setContent($content)
	{
		//the content has to be a string
		if(!is_string($content))
			return FALSE;
		return parent::setContent($content);
	}


	//useful
	//StringResponse::render
	public function render($engine)
	{
		print($this->getContent());
		return TRUE;
	}
}

?>
