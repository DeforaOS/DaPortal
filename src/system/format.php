<?php //$Id$
//Copyright (c) 2012-2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/page.php');


//Format
abstract class Format
{
	//public
	//methods
	//static
	//Format::attachDefault
	public static function attachDefault($engine, $type = FALSE)
	{
		global $config;
		$ret = FALSE;
		$priority = 0;

		$name = FALSE;
		if($type !== FALSE)
			$name = $config->get("format::$type", 'backend');
		if($name === FALSE)
			$name = $config->get('format', 'backend');
		if($name !== FALSE)
		{
			$res = require_once('./formats/'.$name.'.php');
			if($res === FALSE)
				return FALSE;
			$class = $name.'Format';
			$ret = new $class($name);
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' (default)');
			$ret->attach($engine, $type);
			return $ret;
		}
		if(($dir = opendir('formats')) === FALSE)
			return FALSE;
		while(($de = readdir($dir)) !== FALSE)
		{
			if(substr($de, -4) != '.php')
				continue;
			require_once('./formats/'.$de);
			$name = substr($de, 0, strlen($de) - 4);
			$class = $name.'Format';
			$format = new $class($name);
			if(($p = $format->match($engine, $type)) <= $priority)
				continue;
			$ret = $format;
			$priority = $p;
		}
		closedir($dir);
		if($ret != FALSE)
		{
			$engine->log('LOG_DEBUG', 'Attaching '.get_class($ret)
					.' with priority '.$priority);
			$ret->attach($engine, $type);
		}
		else
			$engine->log('LOG_ERR', 'Could not attach a formatting'
					.' backend for '.$type);
		return $ret;
	}


	//accessors
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}


	//virtual
	abstract public function render($engine, $page, $filename = FALSE);


	//protected
	//properties
	protected $name = FALSE;
	protected $parameters = array();


	//methods
	//essential
	//Format::Format
	protected function __construct($name)
	{
		$this->name = $name;
	}


	//virtual
	abstract protected function match($engine, $type = FALSE);
	abstract protected function attach($engine, $type = FALSE);


	//accessors
	//Format::configGet
	protected function configGet($variable)
	{
		global $config;

		return $config->get('format::'.$this->name, $variable);
	}


	//Format::getParameter
	protected function getParameter($name)
	{
		if(!isset($this->parameters[$name]))
			return FALSE;
		return $this->parameters[$name];
	}
}


//FormatElements
abstract class FormatElements extends Format
{
	//public
	//methods
	//FormatElements::render
	public function render($engine, $page, $filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
		{
			$p = new Page();
			$this->renderPage($p);
		}
		if($page->getType() == 'page')
			$this->renderPage($page);
		else
		{
			$title = $page->getProperties('title');
			$p = new Page(array('title' => $title));
			$p->append($page);
			$this->renderPage($p);
		}
	}


	//protected
	//methods
	//useful
	//FormatElements::renderElement
	protected function renderElement($e)
	{
		switch($e->getType())
		{
			case 'button':
				return $this->renderButton($e);
			case 'checkbox':
				return $this->renderCheckbox($e);
			case 'combobox':
				return $this->renderCombobox($e);
			case 'data':
				return $this->renderData($e);
			case 'dialog':
				return $this->renderDialog($e);
			case 'entry':
				return $this->renderEntry($e);
			case 'expander':
				return $this->renderExpander($e);
			case 'filechooser':
				return $this->renderFileChooser($e);
			case 'form':
				return $this->renderForm($e);
			case 'frame':
				return $this->renderFrame($e);
			case 'hbox':
				return $this->renderHbox($e);
			case 'htmledit':
				return $this->renderHtmledit($e);
			case 'htmlview':
				return $this->renderHtmlview($e);
			case 'iconview':
				return $this->renderIconview($e);
			case 'image':
				return $this->renderImage($e);
			case 'label':
				return $this->renderLabel($e);
			case 'link':
				return $this->renderLink($e);
			case 'menubar':
				return $this->renderMenubar($e);
			case 'radiobutton':
				return $this->renderRadioButton($e);
			case 'page':
				return $this->renderPage($e);
			case 'progress':
				return $this->renderProgress($e);
			case 'statusbar':
				return $this->renderStatusbar($e);
			case 'textview':
				return $this->renderTextview($e);
			case 'title':
				return $this->renderTitle($e);
			case 'toolbar':
				return $this->renderToolbar($e);
			case 'treeview':
				return $this->renderTreeview($e);
			case 'vbox':
				return $this->renderVbox($e);
			default:
				return $this->renderLabel($e);
		}
	}


	//abstract
	//useful
	abstract protected function renderButton($e);
	abstract protected function renderCheckbox($e);
	abstract protected function renderCombobox($e);
	abstract protected function renderData($e);
	abstract protected function renderDialog($e);
	abstract protected function renderEntry($e);
	abstract protected function renderExpander($e);
	abstract protected function renderFileChooser($e);
	abstract protected function renderForm($e);
	abstract protected function renderFrame($e);
	abstract protected function renderHbox($e);
	abstract protected function renderHtmledit($e);
	abstract protected function renderHtmlview($e);
	abstract protected function renderIconview($e);
	abstract protected function renderImage($e);
	abstract protected function renderLabel($e);
	abstract protected function renderLink($e);
	abstract protected function renderMenubar($e);
	abstract protected function renderPage($e);
	abstract protected function renderProgress($e);
	abstract protected function renderRadioButton($e);
	abstract protected function renderStatusbar($e);
	abstract protected function renderTextview($e);
	abstract protected function renderTitle($e);
	abstract protected function renderToolbar($e);
	abstract protected function renderTreeview($e);
	abstract protected function renderVbox($e);
}

?>
