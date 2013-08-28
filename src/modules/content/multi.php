<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./modules/content/module.php');


//MultiContentModule
abstract class MultiContentModule extends ContentModule
{
	//protected
	//properties
	protected $content_classes = array();


	//methods
	//essential
	//MultiContentModule::MultiContentModule
	protected function __construct($id, $name, $title)
	{
		//XXX copied from Module::Module()
		$this->id = $id;
		$this->name = $name;
		$this->title = ($title !== FALSE) ? $title : ucfirst($name);
		//set the context explicitly
		//XXX $engine should not be optional
		$this->setContext();
	}


	//accessors
	//MultiContentModule::_get
	//XXX obsolete?
	protected function _get($engine, $id, $title = FALSE, $request = FALSE)
	{
		foreach($this->content_classes as $class)
		{
			$this->content_class = $class;
			if(($res = parent::_get($engine, $id, $title, $request))
					!== FALSE)
				return $res;
		}
		$this->setContext($engine, $request);
		return parent::_get($engine, $id, $title, $request);
	}


	//MultiContentModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
	{
		//the content type has precedence over the request
		if($content !== FALSE)
		{
			$this->content_class = get_class($content);
			return;
		}
		$type = ($request !== FALSE)
			? $request->getParameter('type') : FALSE;
		foreach($this->content_classes as $t => $c)
			if($type == $t)
			{
				$this->content_class = $c;
				return;
			}
		//default to the first content type known
		foreach($this->content_classes as $t => $c)
		{
			$this->content_class = $c;
			return;
		}
	}


	//calls
	//MultiContentModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callDefault($engine, $request);
	}


	//MultiContentModule::callList
	protected function callList($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callList($engine, $request);
	}


	//MultiContentModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callSubmit($engine, $request);
	}


	//MultiContentModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		$this->setContext($engine, $request);
		return parent::callUpdate($engine, $request);
	}


	//forms
	//MultiContentModule::formSubmit
	protected function formSubmit($engine, $request)
	{
		$r = $this->getRequest('submit', array(
				'type' => $request->getParameter('type')));

		$form = new PageElement('form', array('request' => $r));
		//content
		$this->helperSubmitContent($engine, $request, $form);
		//buttons
		$this->helperSubmitButtons($engine, $request, $form);
		return $form;
	}


	//helpers
	//MultiContentModule::helperListToolbar
	protected function helperListToolbar($engine, $page, $request = FALSE)
	{
		//XXX code duplicated from ContentModule
		$cred = $engine->getCredentials();
		$user = ($request !== FALSE)
			? new User($engine, $request->getID(),
				$request->getTitle()) : FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$r = new Request($this->name, 'list', $uid,
			$uid ? $user->getUsername() : FALSE,
			array('type' => $request->getParameter('type')));
		$toolbar = $page->append('toolbar');
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $r));
		$r = $this->getRequest('submit', array(
				'type' => $request->getParameter('type')));
		if($this->canSubmit($engine, $request))
			$toolbar->append('button', array('stock' => 'new',
					'request' => $r,
					'text' => $this->text_content_submit_content));
		if($uid === $cred->getUserID())
		{
			$toolbar->append('button', array('stock' => 'post',
						'text' => _('Publish'),
						'type' => 'submit',
						'name' => 'action',
						'value' => 'post'));
			$toolbar->append('button', array('stock' => 'unpost',
						'text' => _('Unpublish'),
						'type' => 'submit',
						'name' => 'action',
						'value' => 'unpost'));
		}
	}
}

?>
