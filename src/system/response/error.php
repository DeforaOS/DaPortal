<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
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



//ErrorResponse
class ErrorResponse extends Response
{
	//public
	//methods
	//accessors
	//ErrorResponse::setCode
	public function setCode($code)
	{
		if($code == 0)
			$code = Response::$CODE_EUNKNOWN;
		return parent::setCode($code);
	}


	//ErrorResponse::setContent
	public function setContent($content)
	{
		if(!is_string($content))
			$content = 'Unknown error';
		return parent::setContent($content);
	}


	//useful
	//ErrorResponse::render
	public function render(Engine $engine)
	{
		$engine->log(LOG_ERR, $this->getContent());
		return $this->getCode();
	}
}

?>
