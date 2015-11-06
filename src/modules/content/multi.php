<?php //$Id$
//Copyright (c) 2013-2015 Pierre Pronchery <khorben@defora.org>
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



//MultiContentModule
abstract class MultiContentModule extends ContentModule
{
	//public
	//methods
	//accessors
	//MultiContentModule::getContent
	public function getContent($engine, $id, $title = FALSE,
			$request = FALSE)
	{
		//XXX this works only if the ID namespace is not ambiguous
		foreach(static::$content_classes as $class)
		{
			$this->content_class = $class;
			if(($res = parent::getContent($engine, $id, $title,
					FALSE)) !== FALSE)
				return $res;
		}
		$this->setContext($engine, $request);
		return parent::getContent($engine, $id, $title, $request);
	}


	//MultiContentModule::getContentClass
	static public function getContentClass($class)
	{
		if(isset(static::$content_classes[$class]))
			return static::$content_classes[$class];
		return FALSE;
	}


	//protected
	//properties
	static protected $content_classes = array();


	//methods
	//essential
	//MultiContentModule::MultiContentModule
	protected function __construct($id, $name, $title)
	{
		Module::__construct($id, $name, $title);
		//autoload sub-classes
		foreach(static::$content_classes as $class)
		{
			$c = strtolower($class);
			$len = strlen($name) + 7;
			if(strlen($c) <= $len)
				continue;
			if(substr($c, -$len) != strtolower($name).'content')
				continue;
			$c = substr($c, 0, strlen($c) - $len);
			$filename = './modules/'.strtolower($name).'/content/'
				.$c.'.php';
			autoload($class, $filename);
		}
		//set the context explicitly
		//XXX $engine should not be optional
		$this->setContext();
	}


	//accessors
	//MultiContentModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
	{
		//the content type has precedence over the request
		if($content !== FALSE)
			$this->content_class = get_class($content);
		else if($request !== FALSE
				&& ($t = $request->get('type')) !== FALSE
				&& isset(static::$content_classes[$t]))
			$this->content_class = static::$content_classes[$t];
		else
			//default to the first content type known
			foreach(static::$content_classes as $t => $c)
			{
				$this->content_class = $c;
				break;
			}
	}


	//calls
	//MultiContentModule::callAdmin
	protected function callAdmin($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callAdmin($engine, $request);
	}


	//MultiContentModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callDefault($engine, $request);
	}


	//MultiContentModule::callGroup
	protected function callGroup($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callGroup($engine, $request);
	}


	//MultiContentModule::callHeadline
	protected function callHeadline($engine, $request = FALSE)
	{
		$this->setContext($engine, $request);
		return parent::callHeadline($engine, $request);
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
				'type' => $request->get('type')));

		$form = new PageElement('form', array('request' => $r));
		//content
		$this->helperSubmitContent($engine, $request, $form);
		//buttons
		$this->helperSubmitButtons($engine, $request, $form);
		return $form;
	}


	//helpers
	//MultiContentModule::helperActionsAdmin
	protected function helperActionsAdmin($engine, $request)
	{
		$ret = array();

		if($request->get('admin') === 0)
			return $ret;
		foreach(static::$content_classes as $t => $c)
		{
			$r = $this->getRequest('admin', array('type' => $t));
			$this->setContext($engine, $r); /* XXX */
			$ret[] = $this->helperAction($engine, 'admin', $r,
					$this->text_content_admin);
		}
		return $ret;
	}


	//MultiContentModule::helperActionsGroup
	protected function helperActionsGroup($engine, $request, $group)
	{
		$ret = array();

		//group's content
		foreach(static::$content_classes as $t => $c)
		{
			$r = new Request($this->name, 'group', $group->getGroupID(),
					$group->getGroupname(), array('type' => $t));
			$this->setContext($engine, $r);
			$ret[] = $this->helperAction($engine, $this->name, $r,
				$this->text_content_list_title_by_group
				.' '.$group->getGroupname());
		}
		return $ret;
	}


	//MultiContentModule::helperActionsList
	protected function helperActionsList($engine, $request, $user)
	{
		$ret = array();

		foreach(static::$content_classes as $t => $c)
		{
			$r = new Request($this->name, 'list',
				$user->getUserID(), $user->getUsername(), array(
					'type' => $t));
			$this->setContext($engine, $r); /* XXX */
			$ret[] = $this->helperAction($engine, $this->name, $r,
					$this->text_content_list_title_by
					.' '.$user->getUsername());
		}
		return $ret;
	}


	//MultiContentModule::helperActionsSubmit
	protected function helperActionsSubmit($engine, $request)
	{
		$ret = array();

		foreach(static::$content_classes as $t => $c)
		{
			$r = $this->getRequest('submit', array('type' => $t));
			$this->setContext($engine, $r); /* XXX */
			$ret[] = $this->helperAction($engine,
					$this->stock_content_new,
					$r, $this->text_content_submit_content);
		}
		return $ret;
	}


	//MultiContentModule::helperListToolbar
	protected function helperListToolbar($engine, $page, $request = FALSE)
	{
		//XXX code duplicated from ContentModule
		$class = $this->content_class;
		$cred = $engine->getCredentials();
		$user = ($request !== FALSE)
			? User::lookup($engine, $request->getTitle(),
				$request->getID()) : FALSE;
		$type = ($request !== FALSE) ? $request->get('type') : FALSE;

		if($user === FALSE || ($uid = $user->getUserID()) == 0)
			$uid = FALSE;
		$r = new Request($this->name, 'list', $uid,
			$uid ? $user->getUsername() : FALSE,
			array('type' => $type));
		$toolbar = $page->append('toolbar');
		$toolbar->append('button', array('stock' => 'refresh',
				'text' => _('Refresh'),
				'request' => $r));
		$r = $this->getRequest('submit', array('type' => $type));
		$content = new $class($engine, $this);
		if($this->canSubmit($engine, $request, $content))
			$toolbar->append('button', array('stock' => 'new',
					'request' => $r,
					'text' => $this->text_content_submit_content));
		if($uid !== FALSE && $uid === $cred->getUserID()
				&& $this->canPublish($engine, $request))
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
