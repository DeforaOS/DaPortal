<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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



//PipeResponse
class PipeResponse extends StreamResponse
{
	//public
	//methods
	//accessors
	//PipeResponse::getLength
	public function getLength()
	{
		return FALSE;
	}


	//PipeResponse::getModified
	public function getModified()
	{
		return FALSE;
	}


	//useful
	//PipeResponse::render
	public function render(Engine $engine)
	{
		$fp = $this->getContent();

		while(!feof($fp))
			print(fread($fp, 8192));
		return $this->getCode();
	}
}

?>
