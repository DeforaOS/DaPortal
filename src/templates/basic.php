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



//BasicTemplate
class BasicTemplate extends Template
{
	//protected
	//properties
	protected $action = FALSE;
	protected $footer = FALSE;
	protected $homepage = FALSE;
	protected $id = FALSE;
	protected $module = FALSE;
	protected $title = FALSE;
	protected $message = FALSE;
	protected $message_title = FALSE;
	protected $message_type = FALSE;


	//methods
	//accessors
	//BasicTemplate::getDefault
	protected function getDefault()
	{
		global $config;

		$title = $config->get(FALSE, 'title');
		$res = new PageElement('title', array('text' => $title));
		return $res;
	}


	//BasicTemplate::getEntries
	protected function getEntries(Engine $engine = NULL)
	{
		if(($modules = $this->getModules()) === FALSE)
			return FALSE;
		$ret = array();
		foreach($modules as $name)
		{
			if(($module = Module::load($this->engine, $name))
					=== FALSE)
				continue;
			$title = $module->getTitle($this->engine);
			$request = new Request($name, 'actions');
			if(($actions = $module->call($this->engine, $request,
					TRUE)) === FALSE)
				continue;
			$ret[$name] = array('name' => $name, 'title' => $title,
				'actions' => $actions);
		}
		usort($ret, array($this, '_getEntriesSort'));
		return $ret;
	}

	private function _getEntriesSort(array $a, array $b)
	{
		return strcmp($a['title'], $b['title']);
	}


	//BasicTemplate::getFooter
	protected function getFooter(Engine $engine = NULL)
	{
		$footer = new PageElement('statusbar');
		$footer->setProperty('id', 'footer');
		if($this->footer !== FALSE)
			$footer->append('htmlview', array(
					'text' => $this->footer));
		return $footer;
	}


	//BasicTemplate::getMenu
	protected function getMenu(Engine $engine = NULL, $entries = FALSE)
	{
		$cred = $this->engine->getCredentials();

		$menu = new PageElement('menubar');
		if($entries === FALSE)
			$entries = $this->getEntries();
		if($entries === FALSE)
			return $menu;
		foreach($entries as $e)
		{
			if(!is_array($e))
				continue;
			$r = new Request($e['name']);
			$menuitem = $menu->append('menuitem', array(
					'text' => $e['title'],
					'request' => $r));
			if(($actions = $e['actions']) === FALSE)
				continue;
			foreach($actions as $a)
			{
				if(!($a instanceof PageElement))
				{
					$menuitem->append('separator');
					continue;
				}
				if(($label = $a->getProperty('label'))
						=== FALSE)
					continue;
				$important = $a->getProperty('important');
				$request = FALSE;
				$stock = FALSE;
				$text = FALSE;
				if(($icon = $a->getProperty('icon')) !== FALSE
						&& $icon instanceof PageElement)
					$stock = $icon->getProperty('stock');
				if($label instanceof PageElement)
				{
					$request = $label->getProperty(
						'request');
					$text = $label->getProperty('text');
				}
				else if(is_string($label))
					$text = $label;
				if($text === FALSE)
					continue;
				$menuitem->append('menuitem', array(
					'important' => $important,
					'request' => $request,
					'stock' => $stock,
					'text' => $text));
			}
		}
		return $menu;
	}


	//BasicTemplate::getModules
	protected function getModules(Engine $engine = NULL)
	{
		return $this->engine->getModules();
	}


	//BasicTemplate::getTitle
	protected function getTitle(Engine $engine = NULL)
	{
		$title = new PageElement('title', array('id' => 'title'));
		$title->append('link', array('text' => $this->title,
					'title' => $this->title,
					'url' => $this->homepage));
		return $title;
	}


	//useful
	//BasicTemplate::match
	protected function match(Engine $engine)
	{
		return 100;
	}


	//BasicTemplate::attach
	protected function attach(Engine $engine)
	{
		global $config;
		$properties = array('action', 'footer', 'homepage', 'id',
			'message', 'message_title', 'message_type', 'module',
			'title');

		parent::attach($engine);
		foreach($properties as $p)
			if($this->$p === FALSE)
				$this->$p = $this->configGet($p);
		if($this->title === FALSE)
			$this->title = $config->get(FALSE, 'title');
	}


	//BasicTemplate::render
	public function render(Engine $engine, PageElement $page)
	{
		$title = $this->title;

		$p = new Page;
		$p->append($this->getTitle());
		$main = $p->append('vbox', array('id' => 'main'));
		$main->append($this->getMenu());
		if($this->message !== FALSE)
			$main->append('dialog', array(
				'type' => $this->message_type,
				'title' => $this->message_title,
				'text' => $this->message));
		$content = $main->append('vbox', array('id' => 'content'));
		if($page === FALSE && $this->module !== FALSE)
		{
			$request = new Request($this->module, $this->action,
				$this->id);
			//XXX
			$page = $this->engine->process($request);
			$page = ($page instanceof PageResponse)
				? $page->getContent() : FALSE;
		}
		if($page !== FALSE)
		{
			if(($t = $page->getProperty('title')) !== FALSE)
				$title = $this->title.': '.$t;
			$content->append($page);
		}
		else if(($element = $this->getDefault()) !== FALSE)
			$content->append($element);
		$p->setProperty('title', $title);
		$p->append($this->getFooter());
		return $p;
	}
}

?>
