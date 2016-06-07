<?php //$Id$
//Copyright (c) 2015-2016 Pierre Pronchery <khorben@defora.org>
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
	public function call(Engine $engine, Request $request, $internal = 0)
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
	//PKIModule::canRevoke
	public function canRevoke(Engine $engine, Request $request = NULL,
			Content $content = NULL, &$error = FALSE)
	{
		return $this->canSubmit($engine, $request, $content, $error);
	}


	//PKIModule::canSubmit
	public function canSubmit(Engine $engine, Request $request = NULL,
			Content $content = NULL, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(!$credentials->isAdmin())
			return FALSE;
		return parent::canSubmit($engine, $request, $content, $error);
	}


	//PKIModule::setContext
	protected function setContext(Engine $engine = NULL,
			Request $request = NULL, Content $content = NULL)
	{
		parent::setContext($engine, $request, $content);
		switch(static::$content_class)
		{
			case static::$content_classes['caclient']:
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
			case static::$content_classes['caserver']:
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
			case static::$content_classes['ca']:
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
	protected function callDefault(Engine $engine, Request $request)
	{
		$title = _('Certification activity');
		$latest = array('ca', 'caserver', 'caclient');

		if($request->getID() !== FALSE)
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
	protected function callLatest(Engine $engine, Request $request)
	{
		$type = $request->get('type');

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

	private function _latestCAs(Engine $engine, Request $request, $type)
	{
		$class = static::$content_classes['ca'];
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

	private function _latestCAClients(Engine $engine, Request $request,
			$type)
	{
		$class = static::$content_classes['caclient'];
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

	private function _latestCAServers(Engine $engine, Request $request,
			$type)
	{
		$class = static::$content_classes['caserver'];
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
	protected function formSubmit(Engine $engine, Request $request)
	{
		$class = static::$content_classes['ca'];
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


	//PKIModule::helperAdminActions
	protected function helperAdminActions(Engine $engine, Request $request)
	{
		$actions = array('revoke');

		if(($ret = parent::helperAdminActions($engine, $request))
				!== FALSE)
			return $ret;
		//additional actions
		foreach($actions as $a)
			if($request->get($a) !== FALSE)
			{
				$helper = 'helper'.$a;
				if(!method_exists($this, $helper))
				{
					$error = $helper.': Unknown helper';
					return $engine->log(LOG_DEBUG, $error);
				}
				return $this->$helper($engine, $request);
			}
		return FALSE;
	}


	//PKIModule::helperAdminToolbar
	protected function helperAdminToolbar(Engine $engine, PageElement $page,
			Request $request = NULL)
	{
		//XXX move this method to the parent
		$actions = array('revoke' => _('Revoke'));

		$toolbar = parent::helperAdminToolbar($engine, $page,
				$request);
		//additional actions
		$toolitems = array();
		foreach($actions as $action => $label)
		{
			$method = 'can'.$action;
			if(method_exists($this, $method)
					&& $this->$method($engine, $request))
				$toolitems[$action] = $label;
		}
		foreach($toolitems as $action => $label)
			$toolbar->append('button', array('stock' => $action,
					'text' => $label, 'type' => 'submit',
					'name' => 'action',
					'value' => $action));
		return $toolbar;
	}


	//PKIModule::helperRevoke
	protected function helperRevoke(Engine $engine, Request $request)
	{
		$cred = $engine->getCredentials();
		$affected = 0;
		$message = _('The certificate(s) revoked successfully');
		$failure = _('Could not revoke certificate(s)');

		$error = _('Permission denied');
		if(!$this->canRevoke($engine, $request, NULL, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$type = 'info';
		if(($ids = $request->get('ids')) === FALSE || !is_array($ids))
			$ids = array();
		//lookup the certificates and revoke
		foreach($ids as $id)
		{
			if(($content = $this->getContent($engine, $id))
					!== FALSE
					&& $content->revoke($engine))
			{
				$affected += $res->getAffectedCount();
				continue;
			}
			$type = 'error';
			$message = $failure;
		}
		return ($affected > 0) ? new PageElement('dialog', array(
				'type' => $type, 'text' => $message)) : FALSE;
	}
}

?>
