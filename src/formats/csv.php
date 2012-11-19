<?php //$Id$
//Copyright (c) 2012 Pierre Pronchery <khorben@defora.org>
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


//CSVFormat
class CSVFormat extends PlainFormat
{
	//protected
	//properties
	protected $print = FALSE;


	//methods
	//essential
	//CSVFormat::match
	protected function match($engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/csv':
				return 100;
			default:
				return 0;
		}
	}


	//public
	//methods
	//rendering
	//CSVFormat::render
	public function render($engine, $page, $filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
			$page = new Page;
		$this->engine = $engine;
		$this->separator = '';
		$this->renderElement($page);
		$this->engine = FALSE;
	}


	//protected
	//methods
	//printing
	//CSVFormat::print
	protected function _print($string)
	{
		if($this->print)
			print($string);
	}


	//rendering
	//CSVFormat::renderElement
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


	//CSVFormat::renderTreeview
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
			$sep = '';
			foreach($keys as $k)
			{
				$this->_print($sep);
				if(($e = $c->getProperty($k)) !== FALSE)
				{
					if(is_string($e))
						$this->_print($e);
					else
						$this->renderElement($e);
				}
				$sep = ',';
				$this->separator = '';
			}
			$this->_print("\n");
		}
		$this->print = FALSE;
	}
}

?>
