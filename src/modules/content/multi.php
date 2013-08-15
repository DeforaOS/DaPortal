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
	//FIXME:
	//- can*() should not need setContext()
	//- make it more generic => can($action)
	//MultiContentModule::canAdmin
	protected function canAdmin($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		if($content === FALSE)
			$this->setContext($engine, $request, $content);
		return parent::canAdmin($engine, $request, $content, $error);
	}


	//MultiContentModule::canPost
	protected function canPost($engine, $request = FALSE, $content = FALSE,
			&$error = FALSE)
	{
		if($content === FALSE)
			$this->setContext($engine, $request, $content);
		return parent::canPost($engine, $request, $content, $error);
	}


	//MultiContentModule::canPreview
	protected function canPreview($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		if($content === FALSE)
			$this->setContext($engine, $request, $content);
		return parent::canPreview($engine, $request, $content, $error);
	}


	//MultiContentModule::canSubmit
	protected function canSubmit($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		if($content === FALSE)
			$this->setContext($engine, $request, $content);
		return parent::canSubmit($engine, $request, $content, $error);
	}


	//MultiContentModule::canUnpost
	protected function canUnpost($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		if($content === FALSE)
			$this->setContext($engine, $request, $content);
		return parent::canUnpost($engine, $request, $content, $error);
	}


	//MultiContentModule::canUpdate
	protected function canUpdate($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		if($content === FALSE)
			$this->setContext($engine, $request, $content);
		return parent::canUpdate($engine, $request, $content, $error);
	}


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
	protected function callList($engine, $request)
	{
		$this->setContext($engine, $request);
		return parent::callList($engine, $request);
	}
}

?>
