<?php //$Id$
//Copyright (c) 2011-2014 Pierre Pronchery <khorben@defora.org>
//This file is part of DaPortal
//
//DaPortal is free software; you can redistribute it and/or modify
//it under the terms of the GNU General Public License version 2 as
//published by the Free Software Foundation.
//
//DaPortal is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with DaPortal; if not, write to the Free Software
//Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA



//SearchModule
class SearchModule extends Module
{
	//public
	//methods
	//SearchModule::call
	public function call($engine, $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->actions($engine, $request);
				default:
					return FALSE;
			}
		switch($action)
		{
			case 'admin':
			case 'advanced':
			case 'default':
			case 'widget':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
		}
		return FALSE;
	}


	//protected
	//properties
	protected $limit = 20;
	protected $query = 'FROM daportal_content_public, daportal_module,
		daportal_user_enabled
		WHERE daportal_content_public.module_id
		=daportal_module.module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id';
	protected $query_fields = 'SELECT content_id AS id, timestamp AS date,
		name AS module, daportal_content_public.user_id AS user_id,
		title, content, username';


	//methods
	//useful
	//SearchModule::actions
	protected function actions($engine, $request)
	{
		if($request->getParameter('admin') !== FALSE)
			return FALSE;
		if($request->getParameter('user') !== FALSE
				|| $request->getParameter('group') !== FALSE)
			return FALSE;
		$ret = array();
		//advanced search
		$r = new Request($this->name, 'advanced');
		$ret[] = $this->helperAction($engine, 'add', $r,
				_('Advanced search'));
		return $ret;
	}


	//SearchModule::appendResult
	protected function appendResult($engine, &$view, &$res)
	{
		$row = $view->append('row');
		$row->setProperty('title', $res['title']);
		$row->setProperty('username', $res['username']);
		$row->setProperty('date', $res['date']);
		$r = new Request($res['module'], 'preview', $res['id'],
				$res['title']);
		if(($r = $engine->process($r)) === FALSE
				|| !($r instanceof PageResponse))
			return;
		$row->setProperty('preview', $r->getContent());
	}


	//calls
	//SearchModule::callAdmin
	protected function callAdmin($engine)
	{
		$cred = $engine->getCredentials();

		if(!$cred->isAdmin())
			return $engine->log('LOG_ERR', 'Permission denied');
		$title = _('Search administration');
		//FIXME implement settings
		return FALSE;
	}


	//SearchModule::callDefault
	protected function callDefault($engine, $request)
	{
		$p = $request->getParameter('page');

		$page = $this->pageSearch($engine, $request);
		if(($limit = $request->getParameter('limit')) === FALSE
				|| !is_numeric($limit) || $limit <= 0
				|| $limit > 100)
			$limit = $this->limit;
		if(($q = $request->getParameter('q')) === FALSE
				|| strlen($q) == 0)
			return $page;
		$count = 0;
		$res = $this->query($engine, $q, FALSE, $count, $limit, $p,
				TRUE, TRUE);
		$results = $page->append('vbox');
		$results->setProperty('id', 'search_results');
		$label = $results->append('label');
		$label->setProperty('text', $count.' result(s)');
		$columns = array('title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'),
			'preview' => _('Preview'));
		$view = $page->append('treeview', array('view' => 'preview',
					'columns' => $columns));
		foreach($res as $r)
			$this->appendResult($engine, $view, $r);
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count);
		return $page;
	}


	//SearchModule::callAdvanced
	protected function callAdvanced($engine, $request)
	{
		$case = $request->getParameter('case') ? '1' : '0';
		$p = $request->getParameter('page');

		if(($limit = $request->getParameter('limit')) === FALSE
				|| !is_numeric($limit) || $limit <= 0
				|| $limit > 100)
			$limit = $this->limit;
		$page = $this->pageSearch($engine, $request, TRUE, $limit);
		if(($q = $request->getParameter('q')) === FALSE
				|| strlen($q) == 0)
			return $page;
		$count = 0;
		$intitle = $request->getParameter('intitle');
		$incontent = $request->getParameter('incontent');
		if($intitle === FALSE && $incontent === FALSE)
			$intitle = $incontent = TRUE;
		$res = $this->query($engine, $q, $case, $count, $limit, $p,
				$intitle, $incontent);
		$results = $page->append('vbox');
		$results->setProperty('id', 'search_results');
		$label = $results->append('label');
		$label->setProperty('text', $count.' result(s)');
		$columns = array('title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'),
			'preview' => _('Preview'));
		$view = $page->append('treeview', array('view' => 'preview',
					'columns' => $columns));
		foreach($res as $r)
			$this->appendResult($engine, $view, $r);
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count);
		return $page;
	}


	//SearchModule::callWidget
	protected function callWidget($engine, $request)
	{
		$form = new PageElement('form', array('idempotent' => TRUE));
		$r = new Request('search');
		$form->setProperty('request', $r);
		$hbox = $form->append('hbox');
		$entry = $hbox->append('entry');
		$entry->setProperty('name', 'q');
		$entry->setProperty('value', _('Search...'));
		$button = $hbox->append('button', array('stock' => 'search',
					'type' => 'submit',
					'text' => _('Search'),
					'autohide' => TRUE));
		return $form;
	}


	//helpers
	//SearchModule::helperAction
	protected function helperAction($engine, $stock, $request, $text)
	{
		$icon = new PageElement('image', array('stock' => $stock));
		$link = new PageElement('link', array('request' => $request,
				'text' => $text));
		return new PageElement('row', array('icon' => $icon,
				'label' => $link));
	}


	//SearchModule::helperPaging
	protected function helperPaging($engine, $request, $page, $limit, $pcnt)
	{
		//XXX copied from ContentModule
		if($pcnt === FALSE || $limit <= 0 || $pcnt <= $limit)
			return;
		if(($pcur = $request->getParameter('page')) === FALSE)
			$pcur = 1;
		$pcnt = ceil($pcnt / $limit);
		$args = $request->getParameters();
		unset($args['page']);
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $args);
		$form = $page->append('form', array('idempotent' => TRUE,
				'request' => $r));
		$hbox = $form->append('hbox');
		//first page
		$hbox->append('link', array('stock' => 'gotofirst',
				'request' => $r, 'text' => ''));
		//previous page
		$a = $args;
		$a['page'] = max(1, $pcur - 1);
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $a);
		$hbox->append('link', array('stock' => 'previous',
				'request' => $r, 'text' => ''));
		//entry
		$hbox->append('entry', array('name' => 'page', 'width' => '4',
				'value' => $pcur));
		$hbox->append('label', array('text' => " / $pcnt"));
		//next page
		$args['page'] = min($pcur + 1, $pcnt);
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $args);
		$hbox->append('link', array('stock' => 'next',
				'request' => $r, 'text' => ''));
		//last page
		$args['page'] = $pcnt;
		$r = new Request($this->name, $request->getAction(),
			$request->getID(), $request->getTitle(), $args);
		$hbox->append('link', array('stock' => 'gotolast',
				'request' => $r, 'text' => ''));
	}


	//SearchModule::pageSearch
	protected function pageSearch($engine, $request, $advanced = FALSE,
			$limit = FALSE)
	{
		$q = $request->getParameter('q');
		$args = $q ? array('q' => $q) : FALSE;
		$case = $request->getParameter('case') ? '1' : '0';

		$page = new Page;
		$page->setProperty('title', _('Search'));
		$title = $page->append('title', array('stock' => 'search'));
		$title->setProperty('text', $q ? _('Search results')
				: _('Search'));
		$form = $page->append('form');
		$r = new Request('search', $advanced ? 'advanced' : FALSE);
		$form->setProperty('request', $r);
		$entry = $form->append('entry');
		$entry->setProperty('text', _('Search query: '));
		$entry->setProperty('name', 'q');
		$entry->setProperty('value', $request->getParameter('q'));
		if($advanced)
		{
			$hbox = $form->append('hbox');
			$label = $hbox->append('label');
			$label->setProperty('text', _('Search in: '));
			$checkbox = $hbox->append('checkbox');
			$checkbox->setProperty('name', 'intitle');
			$checkbox->setProperty('text', _('titles'));
			$checkbox->setProperty('value',
					$request->getParameter('intitle'));
			$checkbox = $hbox->append('checkbox');
			$checkbox->setProperty('name', 'incontent');
			$checkbox->setProperty('text', _('content'));
			$checkbox->setProperty('value',
					$request->getParameter('incontent'));
			$hbox = $form->append('hbox');
			$radio = $hbox->append('radiobutton', array(
					'name' => 'case', 'value' => $case));
			$radio->append('label', array(
					'text' => _('Case-insensitive'),
					'value' => '0'));
			$radio->append('label', array(
					'text' => _('Case-sensitive'),
					'value' => '1'));
			$hbox = $form->append('hbox');
			$combobox = $hbox->append('combobox', array(
					'name' => 'limit', 'value' => $limit,
					'text' => _('Results per page:')));
			foreach(array(10, 20, 50, 100) as $i)
				$combobox->append('label', array('text' => $i,
						'value' => $i));
			$button = $form->append('button', array(
						'type' => 'reset',
						'text' => _('Reset')));
		}
		$button = $form->append('button', array('stock' => 'search',
					'type' => 'submit',
					'text' => _('Search')));
		$link = $page->append('link');
		if($advanced)
		{
			$link->setProperty('stock', 'remove');
			$link->setProperty('text', _('Simpler search...'));
			$link->setProperty('request', new Request('search',
				FALSE, FALSE, FALSE, $args));
		}
		else
		{
			$link->setProperty('stock', 'add');
			$link->setProperty('text', _('Advanced search...'));
			$link->setProperty('request', new Request('search',
				'advanced', FALSE, FALSE, $args));
		}
		return $page;
	}


	//SearchModule::query
	protected function query($engine, $string, $sensitive, &$count, $limit,
			$page, $intitle, $incontent, $user = FALSE,
			$module = FALSE)
	{
		global $config;
		$db = $engine->getDatabase();
		$query = $this->query.' AND (0=1';
		$regexp = $this->configGet('regexp');
		$func = $regexp ? 'regexp' : 'like';
		$string = str_replace('\\', '\\\\', $string);
		$wildcard = $regexp ? '' : '%';

		$q = explode(' ', $string);
		$args = array();
		$i = 0;
		if($intitle && count($q))
			foreach($q as $r)
			{
				$query .= ' OR title '.$db->$func($sensitive)
					." :arg$i";
				if($func == 'like')
				{
					$query .= ' ESCAPE :escape';
					$args['escape'] = '\\';
				}
				$args['arg'.$i++] = $wildcard.$r.$wildcard;
			}
		if($incontent && count($q))
			foreach($q as $r)
			{
				$query .= ' OR content '.$db->$func($sensitive)
					." :arg$i";
				if($func == 'like')
				{
					$query .= ' ESCAPE :escape';
					$args['escape'] = '\\';
				}
				$args['arg'.$i++] = $wildcard.$r.$wildcard;
			}
		$query .= ')';
		$fields = 'SELECT COUNT(*) AS count';
		if(($res = $db->query($engine, $fields.' '.$query, $args))
				=== FALSE || count($res) != 1)
			return $engine->log('LOG_ERR', _('Unable to search'));
		$res = $res->current();
		$count = $res['count'];
		$fields = $this->query_fields;
		$order = 'ORDER BY timestamp DESC';
		//paging
		if($limit > 0)
		{
			$offset = FALSE;
			if(is_numeric($page) && $page > 1)
			{
				$offset = $limit * ($page - 1);
				if($offset >= $count)
					$offset = 0;
			}
			$order .= ' '.$db->offset($limit, $offset);
		}
		if(($res = $db->query($engine, $fields.' '.$query.' '.$order,
					$args)) === FALSE)
			return $engine->log('LOG_ERR', _('Unable to search'));
		return $res;
	}
}

?>
