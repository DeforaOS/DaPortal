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


	//accessors
	//ManualModule::getRequest
	public function getRequest($action = FALSE, $parameters = FALSE)
	{
		$id = FALSE;
		$title = FALSE;

		if(is_array($parameters)
				&& isset($parameters['section'])
				&& is_numeric($parameters['section'])
				&& isset($parameters['page']))
		{
			$id = $parameters['section'];
			unset($parameters['section']);
			$title = $parameters['page'];
			unset($parameters['page']);
		}
		return new Request($this->name, $action, $id, $title,
			$parameters);
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


	//ManualModule::getPages
	protected function getPages($engine, $name)
	{
		$ret = array();

		if(($path = $this->configGet('path')) === FALSE)
		{
			$message = 'Path to manual pages not configured';
			return $engine->log('LOG_ERR', $message);
		}
		$path = explode(',', $path);
		foreach($path as $p)
		{
			if(($dir = @opendir($p)) === FALSE)
				continue;
			while(($de = readdir($dir)) !== FALSE)
			{
				if(sscanf($de, 'html%s', $section) != 1)
					continue;
				$filename = $p.'/'.$de.'/'.$name.'.html';
				if(($title = $this->_pagesOpen($filename))
						!== FALSE)
					$ret[] = array(
						'section' => $section,
						'page' => $name,
						'title' => $title);
			}
			closedir($dir);
		}
		return $ret;
	}

	private function _pagesOpen($filename)
	{
		$xml = new DOMDocument();

		//we can hide errors
		if(@$xml->loadHTMLfile($filename, LIBXML_NOENT) !== TRUE)
			return FALSE;
		$title = $xml->getElementsByTagName('title');
		return ($title->length == 1)
			? $title->item(0)->textContent : FALSE;
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
			if($request->getID() !== FALSE
					&& $request->getTitle() !== FALSE)
				return $this->callDisplay($engine, $request);
			if(($section = $request->get('section')) !== FALSE
					&& ($page = $request->get('page'))
					!== FALSE)
				return $this->callPage($engine, $request,
						$section, $page);
		}
		return $this->callPage($engine, $request);
	}


	//ManualModule::callDisplay
	protected function callDisplay($engine, $request)
	{
		$name = $request->getTitle();

		if(($section = $request->getID()) === FALSE || $name === FALSE)
			return $this->callPage($engine, $request);
		return $this->callPage($engine, $request, $section, $name);
	}


	//Manual::callPage
	protected function callPage($engine, $request = FALSE, $section = FALSE,
			$name = FALSE)
	{
		$title = _('Manual browser');

		$form = $this->formPage($request);
		if($section === FALSE)
		{
			if($name === FALSE)
				//XXX let this be configured
				$name = 'intro';
			$res = $this->getPages($engine, $name);
		}
		else
			$res = $this->getPage($engine, $section, $name);
		if($res === FALSE)
		{
			$page = new Page(array('title' => $title));
			$page->append('title', array('stock' => $this->name,
					'text' => $title));
			$page->append($form);
			$page->append('dialog', array('type' => 'error',
					'text' => 'No manual page found'));
			return new PageResponse($page, Response::$CODE_ENOENT);
		}
		if(is_array($res))
		{
			$page = new Page(array('title' => $title));
			$page->append('title', array('stock' => $this->name,
				'text' => $title));
			$page->append($form);
			$columns = array('title' => _('Page'),
				'section' => _('Section'),
				'description' => _('Description'));
			$view = $page->append('treeview', array(
				'columns' => $columns));
			foreach($res as $r)
			{
				$r['description'] = $r['title'];
				$args = array('section' => $r['section'],
					'page' => $r['page']);
				$req = $this->getRequest(FALSE, $args);
				$r['title'] = new PageElement('link', array(
					'request' => $req,
					'text' => $r['page'],
					'title' => $r['title']));
				$view->append('row', $r);
			}
			return new PageResponse($page);
		}
		$title .= _(': ')."$name($section)";
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$page->append($form);
		$page->append('htmlview', array('class' => 'monospace',
				'text' => $this->_pageFormat($engine, $res)));
		$page->append('link', array('stock' => 'back',
				'request' => $this->getRequest(),
				'text' => _('Back to the homepage')));
		return new PageResponse($page);
	}

	private function _pageFormat($engine, $xml)
	{
		$links = $xml->getElementsByTagName('a');
		foreach($links as $link)
		{
			if(($href = $link->attributes->getNamedItem('href'))
					=== NULL)
				continue;
			//FIXME wrong if $page contains a dot
			if(sscanf($href->textContent, '../html%[^/]/%[^.].html',
					$section, $page) != 2)
				continue;
			$args = array('section' => $section, 'page' => $page);
			$request = $this->getRequest(FALSE, $args);
			$link->setAttribute('href', $engine->getURL($request));
		}
		$body = $xml->getElementsByTagName('body');
		return ($body->length == 1)
			? $xml->saveXML($body->item(0)) : $xml->saveXML();
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
