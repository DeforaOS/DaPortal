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

		//FIXME apparently necessary to avoid trouble
		ob_end_flush();
		if(rewind($fp) === FALSE)
			return $engine->log('LOG_ERR',
					'Could not rewind the stream');
		if(($res = fpassthru($fp)) === FALSE)
			return $engine->log('LOG_ERR',
				'Could not render the stream');
		$engine->log('LOG_WARNING', 'fpassthru() => '.$res);
		return TRUE;
	}


	//protected
	//properties
	protected $stat = FALSE;
}

?>
