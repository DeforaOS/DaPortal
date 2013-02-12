<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./formats/plain.php');


//XMLFormat
class XMLFormat extends PlainFormat
{
	//protected
	//properties
	protected $print = FALSE;


	//methods
	//essential
	//XMLFormat::match
	protected function match($engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/xml':
				return 100;
			default:
				return 0;
		}
	}


	//public
	//methods
	//rendering
	//XMLFormat::render
	public function render($engine, $page, $filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
			$page = new Page;
		$this->engine = $engine;
		print("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
		print("<root>\n");
		$this->renderElement($page);
		print("</root>\n");
		$this->engine = FALSE;
	}


	//protected
	//methods
	//printing
	//XMLFormat::escape
	protected function escape($text)
	{
		$from = array('<', '>', '&');
		$to = array('&lt;', '&gt;', '&amp;');

		return str_replace($from, $to, $text);
	}


	//XMLFormat::print
	protected function _print($string)
	{
		if($this->print)
			print($string);
	}


	//rendering
	//XMLFormat::renderElement
	protected function renderElement($e)
	{
		switch($e->getType())
		{
			case 'treeview':
				return $this->renderTreeview($e);
			default:
				return parent::renderElement($e);
		}
	}


	//XMLFormat::renderTreeview
	protected function renderTreeview($e)
	{
		$this->print = TRUE;
		if(($columns = $e->getProperty('columns')) === FALSE)
			$columns = array('title' => 'Title');
		$keys = array_keys($columns);
		$children = $e->getChildren($e);
		foreach($children as $c)
		{
			if($c->getType() != 'row')
				continue;
			$this->_print("\t<entry>\n");
			foreach($keys as $k)
				if(($e = $c->getProperty($k)) === FALSE)
					continue;
				else if(is_string($e))
					$this->_print("\t\t<$k>"
							.$this->escape($e)
							."</$k>\n");
				else
				{
					$this->_print("\t\t<$k>");
					//XXX needs escaping
					$this->renderElement($e);
					$this->_print("</$k>\n");
				}
			$this->_print("\t</entry>\n");
		}
		$this->print = FALSE;
	}
}

?>
