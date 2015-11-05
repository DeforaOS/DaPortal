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



//PKIModule
class PKIModule extends MultiContentModule
{
	//public
	//methods
	//essential
	//PKIModule::call
	public function call($engine, $request, $internal = 0)
	{
		if($internal)
			return parent::call($engine, $request, $internal);
		switch(($action = $request->getAction()))
		{
			case 'latest':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return parent::call($engine, $request, $internal);
	}


	//protected
	//properties
	static protected $content_classes = array('ca' => 'CAPKIContent',
		'caclient' => 'CAClientPKIContent',
		'caserver' => 'CAServerPKIContent');


	//methods
	//PKIModule::PKIModule
	protected function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('PKI') : $title;
		$this->content_list_count = 20;
		parent::__construct($id, $name, $title);
	}


	//accessors
	//PKIModule::canSubmit
	public function canSubmit($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(!$credentials->isAdmin())
			return FALSE;
		return parent::canSubmit($engine, $request, $content, $error);
	}


	//PKIModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
	{
		parent::setContext($engine, $request, $content);
		switch($this->content_class)
		{
			case $this->content_classes['caclient']:
				$this->text_content_admin
					= _('CA clients administration');
				$this->text_content_list_title
					= _('CA client list');
				$this->text_content_list_title_by
					= _('CA clients from');
				$this->text_content_list_title_by_group
					= _('CA clients from group');
				$this->text_content_submit_content
					= _('Client certificate request');
				break;
			case $this->content_classes['caserver']:
				$this->text_content_admin
					= _('CA servers administration');
				$this->text_content_list_title
					= _('CA server list');
				$this->text_content_list_title_by
					= _('CA servers from');
				$this->text_content_list_title_by_group
					= _('CA servers from group');
				$this->text_content_submit_content
					= _('Server certificate request');
				break;
			default:
			case $this->content_classes['ca']:
				$this->text_content_admin
					= _('CAs administration');
				$this->text_content_list_title
					= _('Certification Authority list');
				$this->text_content_list_title_by
					= _('Certification Authorities from');
				$this->text_content_list_title_by_group
					= _('Certification Authorities from group');
				$this->text_content_submit_content
					= _('New Certification Authority');
				break;
		}
	}


	//calls
	//PKIModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$title = _('Certification activity');
		$latest = array('ca', 'caserver', 'caclient');

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->getName(),
			'text' => $title));
		$hbox = $page->append('hbox');
		foreach($latest as $l)
		{
			$request = $this->getRequest('latest', array(
				'type' => $l));
			$response = $this->call($engine, $request);
			if($response instanceof PageResponse)
				$hbox->append($response->getContent());
		}
		return new PageResponse($page);
	}


	//PKIModule::callLatest
	protected function callLatest($engine, $request)
	{
		$type = ($request !== FALSE) ? $request->get('type') : FALSE;

		//XXX merge these sub-functions together
		switch($type)
		{
			case 'ca':
				return $this->_latestCAs($engine, $request,
						$type);
			case 'caclient':
				return $this->_latestCAClients($engine,
						$request, $type);
			case 'caserver':
			default:
				return $this->_latestCAServers($engine,
						$request, $type);
		}
	}

	private function _latestCAs($engine, $request, $type)
	{
		$class = $this->content_classes['ca'];
		$title = _('Latest Certification Authorities');

		//list the latest certification authorities
		$page = new Page($title);
		$page->append('title', array('stock' => $this->getName(),
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($bugs = $class::listAll($engine, $this, 'timestamp',
				$this->content_headline_count)) === FALSE)
		{
			$error = _('Could not list Certification Authorities');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		$columns = $class::getColumns();
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($bugs as $b)
			$view->append($b->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More Certification Authorities...'),
			'request' => $this->getRequest('list', array(
				'type' => $type))));
		return new PageResponse($page);
	}

	private function _latestCAClients($engine, $request, $type)
	{
		$class = $this->content_classes['caclient'];
		$title = _('Latest CA clients');

		//list the latest CA clients
		$page = new Page($title);
		$page->append('title', array('stock' => $this->getName(),
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($bugs = $class::listAll($engine, $this, 'timestamp',
				$this->content_headline_count)) === FALSE)
		{
			$error = _('Could not list CA clients');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		$columns = $class::getColumns();
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($bugs as $b)
			$view->append($b->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More CA clients...'),
			'request' => $this->getRequest('list', array(
				'type' => $type))));
		return new PageResponse($page);
	}

	private function _latestCAServers($engine, $request, $type)
	{
		$class = $this->content_classes['caserver'];
		$title = _('Latest CA servers');

		//list the latest CA servers
		$page = new Page($title);
		$page->append('title', array('stock' => $this->getName(),
				'text' => $title));
		$vbox = $page->append('vbox');
		if(($bugs = $class::listAll($engine, $this, 'timestamp',
				$this->content_headline_count)) === FALSE)
		{
			$error = _('Could not list CA servers');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		$columns = $class::getColumns();
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($bugs as $b)
			$view->append($b->displayRow($engine, $request));
		$vbox->append('link', array('stock' => 'more',
			'text' => _('More CA servers...'),
			'request' => $this->getRequest('list', array(
				'type' => $type))));
		return new PageResponse($page);
	}


	//PKIModule::formSubmit
	protected function formSubmit($engine, $request)
	{
		$class = $this->content_classes['ca'];
		$parent = $class::load($engine, $this, $request->getID(),
				$request->getTitle());

		if($parent !== FALSE)
			$r = $parent->getRequest('submit', array(
				'type' => $request->get('type')));
		else
			$r = $this->getRequest('submit', array(
					'type' => $request->get('type')));
		$form = new PageElement('form', array('request' => $r));
		//content
		$this->helperSubmitContent($engine, $request, $form);
		//buttons
		$this->helperSubmitButtons($engine, $request, $form);
		return $form;
	}
}

?>
