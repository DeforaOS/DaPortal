<?php //$Id$
//Copyright (c) 2013-2015 Pierre Pronchery <khorben@defora.org>
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



//XMLFormat
class XMLFormat extends PlainFormat
{
	//protected
	//properties
	protected $encoding = FALSE;
	protected $print = FALSE;


	//methods
	//essential
	//XMLFormat::match
	protected function match(Engine $engine, $type = FALSE)
	{
		switch($type)
		{
			case 'application/xml':
			case 'text/xml':
				return 100;
			default:
				return 0;
		}
	}


	//XMLFormat::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
		global $config;

		if(($this->encoding = $config->get('defaults', 'charset'))
				=== FALSE)
			$this->encoding = ini_get('default_charset');
		if($this->encoding == '')
			$this->encoding = FALSE;
		//for escaping
		if(!defined('ENT_HTML401'))
			define('ENT_HTML401', 0);
		if(!defined('ENT_XML'))
			define('ENT_XML', ENT_HTML401);
	}


	//public
	//methods
	//rendering
	//XMLFormat::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
			$page = new Page;
		$this->engine = $engine;
		print('<?xml version="1.0"');
		if($this->encoding !== FALSE)
			print(' encoding="'
				.$this->escapeAttribute($this->encoding).'"');
		print("?>\n");
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
		return htmlspecialchars($text,
				ENT_COMPAT | ENT_XML | ENT_NOQUOTES,
				$this->encoding);
	}


	//XMLFormat::escapeAttribute
	protected function escapeAttribute($text)
	{
		return htmlspecialchars($text, ENT_COMPAT | ENT_XML,
				$this->encoding);
	}


	//XMLFormat::print
	protected function _print($string)
	{
		if($this->print)
			print($string);
	}


	//rendering
	//XMLFormat::renderData
	protected function renderData(PageElement $e)
	{
		$from = ']]>';
		$to = ']]]]><![CDATA[>';

		print('<![CDATA[');
		print(str_replace($from, $to, $e->getProperty('data')));
		print(']]>');
	}


	//XMLFormat::renderElement
	protected function renderElement(PageElement $e)
	{
		switch($e->getType())
		{
			case 'data':
				return $this->renderData($e);
			case 'treeview':
				return $this->renderTreeview($e);
			default:
				return parent::renderElement($e);
		}
	}


	//XMLFormat::renderInline
	protected function renderInline(PageElement $e)
	{
		if(($text = $e->getProperty('text')) !== FALSE)
		{
			$this->_print($this->escape($this->separator.$text));
			$this->separator = ' ';
		}
		$this->renderChildren($e);
	}


	//XMLFormat::renderLink
	protected function renderLink(PageElement $e)
	{
		$this->_print('<link>');
		if(($text = $e->getProperty('text')) !== FALSE
				&& strlen($text) > 0)
			$this->_print('<text>'.$this->escape($text).'</text>');
		if(($url = $e->getProperty('url')) === FALSE
				&& ($r = $e->getProperty('request')) !== FALSE)
		{
			$this->_print('<request>');
			if(($module = $r->getModule()) !== FALSE)
				$this->_print('<module>'.$this->escape($module)
						.'</module>');
			if(($action = $r->getAction()) !== FALSE)
				$this->_print('<action>'.$this->escape($action)
						.'</action>');
			if(($id = $r->getID()) !== FALSE)
				$this->_print('<id>'.$this->escape($id)
						.'</id>');
			if(($title = $r->getTitle()) !== FALSE)
				$this->_print('<title>'.$this->escape($title)
						.'</title>');
			$this->_print('</request>');
			$url = $this->engine->getURL($r);
		}
		if($url !== FALSE)
			$this->_print('<url>'.$this->escape($url).'</url>');
		$this->renderChildren($e);
		$this->_print('</link>');
	}


	//XMLFormat::renderTreeview
	protected function renderTreeview(PageElement $e)
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
				else if($e === TRUE)
					$this->_print("\t\t<$k/>\n");
				else if(is_string($e) || is_integer($e))
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
