<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//CSVFormat
class CSVFormat extends PlainFormat
{
	//protected
	//properties
	protected $print = FALSE;
	protected $titles = FALSE;


	//methods
	//essential
	//CSVFormat::match
	protected function match(Engine $engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/csv':
				return 100;
			default:
				return 0;
		}
	}


	//CSVFormat::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
		//configuration
		$this->titles = $this->configGet('titles') ? TRUE : FALSE;
	}


	//public
	//methods
	//rendering
	//CSVFormat::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
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
	protected function renderElement(PageElement $e)
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
	protected function renderTreeview(PageElement $e)
	{
		$sep = '';

		$this->print = TRUE;
		if(($columns = $e->get('columns')) === FALSE)
			$columns = array('title' => 'Title');
		$keys = array_keys($columns);
		if(count($keys) == 0)
			return;
		if($this->titles)
		{
			//the first line names each column
			foreach($keys as $k)
			{
				$this->_print($sep.$columns[$k]);
				$sep = ',';
			}
			$this->_print("\n");
		}
		$children = $e->getChildren($e);
		foreach($children as $c)
		{
			if($c->getType() != 'row')
				continue;
			//print each column of the current row
			$sep = '';
			foreach($keys as $k)
			{
				$this->_print($sep);
				if(($e = $c->get($k)) !== FALSE)
				{
					if(is_scalar($e))
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
