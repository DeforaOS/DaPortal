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



//JSONFormat
class JSONFormat extends PlainFormat
{
	//protected
	//properties
	protected $print = FALSE;
	protected $id;


	//methods
	//essential
	//JSONFormat::match
	protected function match($engine, $type = FALSE)
	{
		switch($type)
		{
			case 'application/json':
				return 100;
			default:
				return 0;
		}
	}


	//JSONFormat::attach
	protected function attach($engine, $type = FALSE)
	{
	}


	//public
	//methods
	//rendering
	//JSONFormat::render
	public function render($engine, $page, $filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
			$page = new Page;
		$this->id = 1;
		$this->engine = $engine;
		$this->separator = '';
		$this->_print('{', TRUE);
		$this->renderElement($page);
		$this->_print('}', TRUE);
		$this->engine = FALSE;
	}


	//protected
	//methods
	//printing
	//JSONFormat::escapeString
	protected function escapeString($string)
	{
		return str_replace('"', '\"', $string);
	}


	//JSONFormat::print
	protected function _print($string, $force = FALSE)
	{
		if($force || $this->print)
			print($string);
	}


	//rendering
	//JSONFormat::renderElement
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


	//JSONFormat::renderTreeview
	protected function renderTreeview($e)
	{
		$this->print = TRUE;
		if(($columns = $e->get('columns')) === FALSE)
			$columns = array('title' => 'Title');
		$keys = array_keys($columns);
		if(count($keys) == 0)
			return;
		$this->_print('"rows'.$this->id++.'":[');
		$sep1 = '';
		$children = $e->getChildren($e);
		foreach($children as $c)
		{
			if($c->getType() != 'row')
				continue;
			$this->_print("$sep1\n\t{");
			$sep1 = ',';
			//print each column of the current row
			$sep2 = '';
			foreach($keys as $k)
			{
				$this->_print("$sep2\n\t\t\""
					.$this->escapeString($k)
					."\":\"");
				$sep2 = ',';
				if(($e = $c->get($k)) !== FALSE)
				{
					if(is_scalar($e))
						$this->_print(
							$this->escapeString($e)
						);
					else
						$this->renderElement($e);
				}
				$this->_print('"');
			}
			$this->_print("\n\t}");
		}
		$this->_print("\n]");
		$this->print = FALSE;
	}
}

?>
