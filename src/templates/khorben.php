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



//KhorbenTemplate
class KhorbenTemplate extends BasicTemplate
{
	//protected
	//functions
	//KhorbenTemplate::getEntries
	protected function getEntries(Engine $engine = NULL)
	{
		$ret = array();

		//about
		$actions = array();
		$r = new Request('wiki', FALSE, 12, 'About');
		$actions[] = new PageElement('link', array('request' => $r,
				'text' => 'About'));
		$ret[] = array('name' => 'wiki', 'title' => 'About',
			'actions' => $actions);
		//blog
		$actions = array();
		$r = new Request('blog');
		$actions[] = new PageElement('link', array('request' => $r,
				'text' => 'Blog'));
		$ret[] = array('name' => 'blog', 'title' => 'Blog',
			'actions' => $actions);
		//projects
		$actions = array();
		$actions[] = new PageElement('link', array('url' => 'projects',
				'text' => 'Projects'));
		$ret[] = array('title' => 'Projects', 'actions' => $actions);
		//papers
		$actions = array();
		$actions[] = new PageElement('link', array('url' => 'papers',
				'text' => 'Papers'));
		$ret[] = array('title' => 'Papers', 'actions' => $actions);
		//contact
		$actions = array();
		$r = new Request('wiki', FALSE, 13, 'Contact');
		$actions[] = new PageElement('link', array('request' => $r,
				'text' => 'Contact'));
		$ret[] = array('name' => 'wiki', 'title' => 'Contact',
			'actions' => $actions);
		return $ret;
	}


	//BasicTemplate::getMenu
	protected function getMenu(Engine $engine = NULL, $entries = FALSE)
	{
		$menu = new PageElement('menubar');
		if($entries === FALSE)
			$entries = $this->getEntries();
		if($entries === FALSE)
			return $menu;
		foreach($entries as $e)
		{
			if(!is_array($e) || !isset($e['actions'])
					|| !is_array($e['actions']))
				continue;
			foreach($e['actions'] as $link)
			{
				$args['text'] = $link->getProperty('text');
				$args['request'] = $link->getProperty('request');
				$args['url'] = $link->getProperty('url');
				$menu->append('menuitem', $args);
			}
		}
		return $menu;
	}
}

?>
