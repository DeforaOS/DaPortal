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



//PlainFormat
class PlainFormat extends Format
{
	//protected
	//properties
	protected $engine = FALSE;
	protected $separator = '';


	//methods
	//essential
	//PlainFormat::match
	protected function match(Engine $engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/plain':
				return 100;
			default:
				return 0;
		}
	}


	//PlainFormat::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
		$this->set('wrap', $this->configGet('wrap'));
	}


	//public
	//methods
	//accessors
	//PlainFormat::set
	public function set($name, $value)
	{
		switch($name)
		{
			case 'wrap':
				if($value === FALSE)
					$value = 0;
				else if(!is_numeric($value) || $value < 0)
					return FALSE;
				break;
		}
		return parent::set($name, $value);
	}


	//rendering
	//PlainFormat::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
			$page = new Page;
		$this->engine = $engine;
		if(($wrap = $this->get('wrap')) > 0)
		{
			//XXX it would be more efficient to improve _print()
			ob_start();
			$this->renderElement($page);
			$str = wordwrap(ob_get_contents(), $wrap);
			ob_end_clean();
			print($str);
		}
		else
			$this->renderElement($page);
		$this->engine = FALSE;
	}


	//protected
	//methods
	//printing
	//PlainFormat::print
	protected function _print($string)
	{
		print($string);
	}


	//rendering
	//PlainFormat::renderBlock
	protected function renderBlock($e, $underline = '-')
	{
		if($this->separator != '')
			$this->_print("\n\n");
		if(($title = $e->get('title')) !== FALSE)
		{
			$this->_print("$title\n");
			for($i = 0; $i < strlen($title); $i++)
				$this->_print($underline);
			$this->_print("\n\n");
		}
		$this->separator = '';
		$this->renderInline($e);
		$this->_print("\n\n");
	}


	//PlainFormat::renderChildren
	protected function renderChildren($e)
	{
		if(($children = $e->getChildren()) === FALSE)
			return;
		foreach($children as $c)
			$this->renderElement($c);
	}


	//PlainFormat::renderDialog
	protected function renderDialog($e)
	{
		$underline = '-';

		if(($type = $e->get('type')) === FALSE)
			$type = 'message';
		if(($title = $e->get('title')) === FALSE)
			switch($type)
			{
				case 'error':
					$title = _('Error');
					break;
				case 'question':
					$title = _('Question');
					break;
				case 'warning':
					$title = _('Warning');
					break;
				case 'info':
				case 'message':
				default:
					$title = _('Message');
					break;
			}
		if($this->separator != '')
			$this->_print("\n\n");
		$this->_print("$title\n");
		for($i = 0; $i < strlen($title); $i++)
			$this->_print($underline);
		$this->_print("\n\n");
		$this->separator = '';
		$this->renderInline($e);
		$this->_print("\n\n");
	}


	//PlainFormat::renderElement
	protected function renderElement($e)
	{
		switch($e->getType())
		{
			case 'htmlview':
				//XXX ignore
				return;
			case 'frame':
			case 'hbox':
			case 'menubar':
			case 'statusbar':
			case 'vbox':
				return $this->renderBlock($e);
			case 'dialog':
				return $this->renderDialog($e);
			case 'page':
				return $this->renderBlock($e, '=');
			case 'link':
				return $this->renderLink($e);
			case 'label':
			default:
				return $this->renderInline($e);
		}
	}


	//PlainFormat::renderInline
	protected function renderInline($e)
	{
		if(($text = $e->get('text')) !== FALSE && is_scalar($text)
				&& strlen($text) > 0)
		{
			$this->separator = (substr($text, 0, 1) == "\n")
				? '' : $this->separator;
			$this->_print($this->separator.$text);
			$this->separator = (substr($text, -1) == "\n")
				? '' : ' ';
		}
		$this->renderChildren($e);
	}


	//PlainFormat::renderLink
	protected function renderLink($e)
	{
		if(($url = $e->get('url')) === FALSE
				&& ($r = $e->get('request')) !== FALSE)
			$url = $this->engine->getURL($r);
		if(($text = $e->get('text')) !== FALSE
				&& strlen($text) > 0)
		{
			$this->_print($this->separator.$text);
			$this->separator = ' ';
			if($url !== FALSE)
				$this->_print($this->separator."($url)");
		}
		else if($url !== FALSE)
			$this->_print($this->separator.$url);
		$this->renderChildren($e);
	}
}

?>
