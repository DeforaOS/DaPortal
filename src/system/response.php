<?php //$Id$
//Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org>
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



//Response
abstract class Response extends Mutator
{
	//public
	//methods
	//essential
	//Response::Response
	public function __construct($content = FALSE, $code = 0)
	{
		$this->setContent($content);
		$this->setCode($code);
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
	//OUT:	0	Success
	//	integer	Exit value for PHP
	abstract public function render(Engine $engine);


	//properties
	static public $CODE_EUNKNOWN = -1;	//unknown error
	static public $CODE_SUCCESS = 0;	//successful operation
	static public $CODE_EINVAL = 1;		//invalid argument
	static public $CODE_EIO = 2;		//file operation error
	static public $CODE_ENOENT = 3;		//could not load an object
	static public $CODE_ENOSYS = 4;		//operation not implemented
	static public $CODE_EPERM = 5;		//permission denied
	static public $CODE_EROFS = 6;		//idempotence contract breach


	//protected
	//properties
	protected $code = -1;
	protected $content = FALSE;
	protected $filename = FALSE;
	protected $type = FALSE;
}

?>
