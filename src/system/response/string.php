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
	public function render(Engine $engine)
	{
		print($this->getContent());
		return $this->getCode();
	}
}

?>
