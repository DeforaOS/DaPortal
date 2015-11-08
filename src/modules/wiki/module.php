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



//WikiModule
class WikiModule extends ContentModule
{
	//public
	//methods
	//essential
	//WikiModule::WikiModule
	public function __construct($id, $name, $title = FALSE)
	{
		$this->root = WikiContent::getRoot($name);
		$title = ($title === FALSE) ? _('Wiki') : $title;
		parent::__construct($id, $name);
		$this->content_class = 'WikiContent';
		$this->text_content_admin = _('Wiki administration');
		$this->text_content_by = _('Page by');
		$this->text_content_item = _('Wiki page');
		$this->text_content_items = _('Wiki pages');
		$this->text_content_list_title = _('Wiki pages');
		$this->text_content_list_title_by = _('Wiki pages by');
		$this->text_content_list_title_by_group = _('Wiki pages by group');
		$this->text_content_more_content = _('More wiki pages...');
		$this->text_content_submit_content = _('New wiki page');
		$this->text_content_title = _('Wiki');
	}


	//useful
	//WikiModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		if($internal)
			return parent::call($engine, $request, $internal);
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		switch($action)
		{
			case 'monitor':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return parent::call($engine, $request, $internal);
	}


	//protected
	//methods
	//calls
	//WikiModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$title = $this->text_content_title;

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$page = new Page(array('title' => $title));
		$page->append('title', array('text' => $title,
				'stock' => $this->getName()));
		$vbox = $page->append('vbox');
		//search
		$vbox->append('title', array('text' => _('Search the wiki'),
				'stock' => 'search'));
		$r = new Request('search', 'advanced', FALSE, FALSE,
			array('inmodule' => $this->getName(), 'intitle' => 1));
		$form = $vbox->append('form', array('request' => $r));
		$hbox = $form->append('hbox');
		$hbox->append('entry', array('name' => 'q',
				'text' => _('Look for a page: ')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'search', 'text' => _('Search')));
		$r = new Request('search', 'advanced', FALSE, FALSE,
			array('inmodule' => $this->getName(),
				'incontent' => 1));
		$form = $vbox->append('form', array('request' => $r));
		$hbox = $form->append('hbox');
		$hbox->append('entry', array('name' => 'q',
				'text' => _('Look inside pages: ')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'search', 'text' => _('Search')));
		$vbox->append('title', array('text' => _('Recent changes'),
				'stock' => 'help'));
		//recent changes
		$headlines = $this->callHeadline($engine, FALSE);
		if($headlines instanceof PageResponse)
			$vbox->append($headlines->getContent());
		//page list
		$vbox->append('link', array(
				'request' => $this->getRequest('list'),
				'stock' => $this->getName(),
				'text' => _('List all pages')));
		return new PageResponse($page);
	}


	//WikiModule::callMonitor
	protected function callMonitor($engine, $request)
	{
		$title = _('Wiki monitoring');

		$error = _('Permission denied');
		if(!$this->canAdmin($engine, FALSE, FALSE, $error))
			return new ErrorResponse($error, Response::$CODE_EPERM);
		$page = new Page(array('title' => $title));
		$page->append('title', array('class' => $this->getName(),
				'stock' => 'monitor', 'text' => $title));
		$vbox = $page->append('vbox');
		//disk usage
		$vbox->append('title', array('text' => _('Disk usage')));
		if($this->root === FALSE)
		{
			$error = _('The root folder is not configured');
			$vbox->append('dialog', array('type' => 'warning',
					'text' => $error));
		}
		else
		{
			$text = 'Disk usage for '.$this->root.': ';
			$label = $vbox->append('label', array('text' => $text));
			$free = disk_free_space($this->root);
			$total = disk_total_space($this->root);
			$avail = $total - $free;
			$value = ($avail * 100) / $total;
			$value = sprintf('%.1lf%%', $value);
			$label->append('progress', array('min' => 0,
					'max' => $total,
					'low' => round($total * 0.10),
					'high' => round($total * 0.75),
					'value' => $total - $free,
					'text' => $value));
			$total = round($total / (1024 * 1024));
			$free = round($free / (1024 * 1024));
			$avail = round($avail / (1024 * 1024));
			$text = " $avail / $total MB ($value)";
			$label->append('label', array('text' => $text));
		}
		$request = $this->getRequest('admin');
		$page->append('link', array('request' => $request,
				'stock' => 'admin',
				'text' => _('Wiki administration')));
		return new PageResponse($page);
	}


	//helpers
	//WikiModule::helperActionsAdmin
	protected function helperActionsAdmin($engine, $request)
	{
		$admin = $request->get('admin');

		$ret = parent::helperActionsAdmin($engine, $request);
		if($admin === 0)
			return $ret;
		$r = $this->getRequest('monitor');
		$ret[] = $this->helperAction($engine, 'monitor', $r,
				_('Wiki monitoring'));
		return $ret;
	}


	//private
	//properties
	private $root = FALSE;
}

?>
