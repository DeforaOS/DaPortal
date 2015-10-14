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



//ManualModule
class ManualModule extends Module
{
	//public
	//methods
	//essential
	//ManualModule::call
	public function call($engine, $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->$action($engine,
							$request);
				default:
					return FALSE;
			}
		switch($action)
		{
			case 'default':
			case 'display':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//protected
	//methods
	//accessors
	//ManualModule::getPage
	protected function getPage($engine, $section, $name)
	{
		if(($path = $this->configGet('path')) === FALSE)
		{
			$message = 'Path to manual pages not configured';
			return $engine->log('LOG_ERR', $message);
		}
		if(strpos($section, '/') !== FALSE
				|| strpos($name, '/') !== FALSE)
			return FALSE;
		$path = explode(',', $path);
		$xml = new DOMDocument();
		foreach($path as $p)
		{
			$filename = $p.'/html'.$section.'/'.$name.'.html';
			//we can ignore errors
			if(@$xml->loadHTMLfile($filename, LIBXML_NOENT)
					=== TRUE)
				return $xml;
		}
		return FALSE;
	}


	//useful
	//actions
	//ManualModule::actions
	protected function actions($engine, $request)
	{
		return array();
	}


	//calls
	//ManualModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$title = _('Manual browser');

		if($request !== FALSE)
		{
			if($request->getID() !== FALSE)
				return $this->callDisplay($engine, $request);
			if(($section = $request->get('section')) !== FALSE
					&& ($page = $request->get('page'))
					!== FALSE)
				return $this->callPage($engine, $request,
						$section, $page);
		}
		$page = new Page(array('title' => $title));
		//title
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$form = $this->formPage($request);
		$page->append($form);
		return new PageResponse($page);
	}


	//ManualModule::callDisplay
	protected function callDisplay($engine, $request)
	{
		$name = $request->getTitle();

		if(($section = $request->getID()) === FALSE || $name === FALSE)
			return $this->callDefault($engine);
		return $this->callPage($engine, $request, $section, $name);
	}


	//Manual::callPage
	protected function callPage($engine, $request, $section, $name)
	{
		$title = _('Manual browser');

		$form = $this->formPage($request);
		if(($xml = $this->getPage($engine, $section, $name)) === FALSE)
		{
			$page = new Page(array('title' => $title));
			$page->append('title', array('stock' => $this->name,
					'text' => $title));
			$page->append($form);
			$page->append('dialog', array('type' => 'error',
					'text' => 'No manual page found'));
			return new PageResponse($page, Response::$CODE_ENOENT);
		}
		$title .= _(': ')."$name($section)";
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$page->append($form);
		$body = $xml->getElementsByTagName('body');
		$xml = ($body->length >= 1) ? $xml->saveXML($body->item(0))
			: $xml->saveXML();
		$page->append('htmlview', array('class' => 'monospace',
				'text' => $xml));
		return new PageResponse($page);
	}


	//forms
	protected function formPage($request)
	{
		$r = $this->getRequest();
		//FIXME supply examples for the list of sections

		$form = new PageElement('form', array('request' => $r,
				'idempotent' => TRUE));
		$box = $form->append('hbox');
		$section = $box->append('combobox', array(
				'text' => _('Section: '),
				'name' => 'section',
				'value' => $request->get('section')));
		for($i = 1; $i <= 9; $i++)
			$section->append('label', array('value' => $i,
					'text' => $i));
		$box->append('entry', array('text' => _('Page: '),
				'name' => 'page',
				'value' => $request->get('page')));
		$box->append('button', array('type' => 'submit',
				'text' => _('Search')));
		return $form;
	}
}

?>
