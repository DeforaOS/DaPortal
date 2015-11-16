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



//DeforaOSTemplate
class DeforaOSTemplate extends BasicTemplate
{
	//protected
	//methods
	//accessors
	//DeforaOSTemplate::getMenu
	protected function getMenu(Engine $engine = NULL, $entries = FALSE)
	{
		$cred = $this->engine->getCredentials();

		//obtain the parent menu
		if($entries === FALSE)
			$entries = $this->getEntries();
		if(($menu = parent::getMenu($engine, $entries)) === FALSE)
			return FALSE;
		$vbox = new PageElement('vbox', array('id' => 'menu'));
		$vbox->append($menu);
		//add some widgets
		//search widget
		$request = new Request('search', 'widget');
		if(($widget = $this->engine->process($request)) !== FALSE)
			$vbox->append($widget->getContent());
		//user widget
		$request = new Request('user', 'widget');
		if(($widget = $this->engine->process($request)) !== FALSE)
			$vbox->append($widget->getContent());
		return $vbox;
	}


	//DeforaOSTemplate::getTitle
	protected function getTitle(Engine $engine = NULL)
	{
		$title = new PageElement('title', array('id' => 'title'));
		$link = $title->append('link', array('url' => $this->homepage,
				'text' => ''));
		$link->append('image', array('source' => $this->logo));
		return $title;
	}


	//useful
	//DeforaOSTemplate::match
	protected function match(Engine $engine)
	{
		return 0;
	}


	//properties
	protected $logo = 'themes/DeforaOS.png';
}

?>
