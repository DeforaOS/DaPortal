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
	//BasicTemplate::getDefaultPage
	protected function getDefaultPage()
	{
		$page = NULL;

		if($this->module !== FALSE)
		{
			$request = new Request($this->module, $this->action,
				$this->id);
			$page = $this->engine->process($request);
			if($page instanceof PageResponse)
				$page = $page->getContent();
		}
		if(is_null($page))
			$page = new PageElement('title', array(
					'text' => $this->title));
		return $page;
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
		$footer = new PageElement('statusbar', array('id' => 'footer'));
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
				if(($label = $a->get('label')) === FALSE)
					continue;
				$important = $a->get('important');
				$request = FALSE;
				$stock = FALSE;
				$text = FALSE;
				if(($icon = $a->get('icon')) !== FALSE
						&& $icon instanceof PageElement)
					$stock = $icon->get('stock');
				if($label instanceof PageElement)
				{
					$request = $label->get('request');
					$text = $label->get('text');
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
			$this->$p = $this->configGet($p);
		if($this->title === FALSE)
			$this->title = $config->get(FALSE, 'title');
	}


	//BasicTemplate::render
	public function render(Engine $engine, PageElement $page = NULL)
	{
		$title = $this->title;

		$p = new Page();
		$p->append($this->getTitle());
		$main = $p->append('vbox', array('id' => 'main'));
		$main->append($this->getMenu());
		if($this->message !== FALSE)
			$main->append('dialog', array(
				'type' => $this->message_type,
				'title' => $this->message_title,
				'text' => $this->message));
		$content = $main->append('vbox', array('id' => 'content'));
		if(is_null($page))
			$page = $this->getDefaultPage();
		if($page instanceof Page
				&& ($t = $page->get('title')) !== FALSE)
			$title .= ': '.$t;
		$content->append($page);
		$p->set('title', $title);
		$p->append($this->getFooter());
		return $p;
	}
}

?>
