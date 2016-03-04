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



//StreamResponse
class StreamResponse extends Response
{
	//public
	//methods
	//accessors
	//StreamResponse::getCharset
	public function getCharset()
	{
		return FALSE;
	}


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
	public function render(Engine $engine)
	{
		$fp = $this->getContent();

		if(($pos = ftell($fp)) !== FALSE && $pos != 0
				&& rewind($fp) === FALSE)
		{
			$engine->log(LOG_ERR, 'Could not rewind the stream');
			return Response::$CODE_EIO;
		}
		if(($res = fpassthru($fp)) === FALSE)
		{
			$engine->log(LOG_ERR, 'Could not render the stream');
			return Response::$CODE_EIO;
		}
		return $this->getCode();
	}


	//protected
	//properties
	protected $stat = FALSE;
}

?>
