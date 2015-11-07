<?php //$Id$
//Copyright (c) 2012-2015 Pierre Pronchery <khorben@defora.org>
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



//XHTML11Format
class XHTML11Format extends HTMLFormat
{
	//protected
	//methods
	//essential
	//XHTML11Format::match
	protected function match(Engine $engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/html':
				return 90;
			default:
				return 0;
		}
	}


	//XHTML11Format::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
		$version = '1.0';
		$encoding = 'UTF-8';
		$dtd = '"-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"';

		parent::attach($engine, $type);
		$this->doctype = "<?xml version=\"$version\""
			." encoding=\"".$this->encoding."\"?>\n";
		$this->doctype .= "<!DOCTYPE html PUBLIC $dtd>\n";
		//for escaping
		if(!defined('ENT_XHTML'))
			define('ENT_XHTML', ENT_HTML401);
	}


	//escaping
	//XHTML11Format::escape
	protected function escape($text)
	{
		return htmlspecialchars($text,
				ENT_COMPAT | ENT_XHTML | ENT_NOQUOTES,
				$this->encoding);
	}


	//XHTML11Format::escapeAttribute
	protected function escapeAttribute($text)
	{
		return htmlspecialchars($text, ENT_COMPAT | ENT_XHTML,
				$this->encoding);
	}
}

?>
