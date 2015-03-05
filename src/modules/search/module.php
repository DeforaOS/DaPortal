<?php //$Id$
//Copyright (c) 2011-2015 Pierre Pronchery <khorben@defora.org>
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
	//useful
	//SearchModule::call
	public function call($engine, $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->actions($engine,
							$request);
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
	protected $limit = FALSE;
	//queries
	static protected $query = 'SELECT content_id AS id, timestamp AS date,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public
		FROM daportal_content_public
		WHERE 1=1';


	//methods
	//accessors
	//SearchModule::getLimit
	protected function getLimit($engine, $request = FALSE)
	{
		if($request !== FALSE
				&& ($limit = $request->get('limit')) !== FALSE
				&& is_numeric($limit)
				&& $limit > 0 && $limit <= 500)
			return $limit;
		if($this->limit === FALSE
				&& ($limit = $this->configGet('limit'))
					!== FALSE && is_numeric($limit)
				&& $limit > 0 && $limit <= 500)
			$this->limit = $limit;
		else
			$this->limit = 20;
		return $this->limit;
	}


	//SearchModule::getPage
	protected function getPage($engine, $request = FALSE)
	{
		if($request !== FALSE && ($p = $request->get('page')) !== FALSE
				&& is_numeric($p) && $p >= 1)
			return $p;
		return 1;
	}


	//useful
	//SearchModule::actions
	protected function actions($engine, $request)
	{
		if($request->get('admin') !== FALSE)
			return FALSE;
		if($request->get('user') !== FALSE
				|| $request->get('group') !== FALSE)
			return FALSE;
		$ret = array();
		//advanced search
		$ret[] = $this->helperAction($engine, 'add',
				$r = $this->getRequest('advanced'),
				_('Advanced search'));
		return $ret;
	}


	//SearchModule::appendResult
	protected function appendResult($engine, &$view, &$res)
	{
		$row = $view->append('row');
		$row->set('title', $res['title']);
		$row->set('username', $res['username']);
		$row->set('date', $res['date']);
		if(($module = Module::load($engine, $res['module'])) === FALSE)
			return;
		if(($content = $module->getContent($engine, $res['id']))
				=== FALSE)
			return;
		$row->set('preview', $content->preview($engine));
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
		$case = FALSE;
		$p = $this->getPage($engine, $request);
		$limit = $this->getLimit($engine, $request);

		$page = $this->pageSearch($engine, $request, FALSE, $limit);
		if(($q = $request->get('q')) === FALSE || strlen($q) == 0)
			return $page;
		if(($res = $this->query($engine, $q, $case, TRUE, TRUE))
				=== FALSE)
		{
			$error = _('Unable to search');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return $page;
		}
		else if($res === TRUE)
			return $page;
		$count = count($res);
		if(($offset = ($p - 1) * $limit) >= $count)
		{
			$p = 1;
			$offset = 0;
		}
		$results = $page->append('vbox');
		$results->set('id', 'search_results');
		$label = $results->append('label');
		$label->set('text', $count.' result(s)');
		$columns = array('title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'),
			'preview' => _('Preview'));
		$view = $page->append('treeview', array('view' => 'preview',
					'columns' => $columns));
		$res->seek($offset);
		for($i = 0; $i++ < $limit && $res->valid(); $res->next())
			if(($r = $res->current()) === FALSE)
				break;
			else
				$this->appendResult($engine, $view, $r);
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count,
				$p);
		return $page;
	}


	//SearchModule::callAdvanced
	protected function callAdvanced($engine, $request)
	{
		$case = $request->get('case') ? '1' : '0';
		$p = $this->getPage($engine, $request);
		$limit = $this->getLimit($engine, $request);

		$page = $this->pageSearch($engine, $request, TRUE, $limit);
		if(($q = $request->get('q')) === FALSE || strlen($q) == 0)
			return $page;
		$intitle = $request->get('intitle');
		$incontent = $request->get('incontent');
		if($intitle === FALSE && $incontent === FALSE)
			$intitle = $incontent = TRUE;
		if(($res = $this->query($engine, $q, $case, $intitle,
				$incontent)) === FALSE)
		{
			$error = _('Unable to search');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return $page;
		}
		else if($res === TRUE)
			return $page;
		$count = count($res);
		if(($offset = ($p - 1) * $limit) >= $count)
		{
			$p = 1;
			$offset = 0;
		}
		$results = $page->append('vbox');
		$results->set('id', 'search_results');
		$label = $results->append('label');
		$label->set('text', $count.' result(s)');
		$columns = array('title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'),
			'preview' => _('Preview'));
		$view = $page->append('treeview', array('view' => 'preview',
					'columns' => $columns));
		for($i = 0, $res->seek($offset); $i++ < $limit
					&& ($r = $res->current()) !== FALSE;
				$res->next())
			$this->appendResult($engine, $view, $r);
		//output paging information
		$this->helperPaging($engine, $request, $page, $limit, $count,
				$p);
		return $page;
	}


	//SearchModule::callWidget
	protected function callWidget($engine, $request)
	{
		$form = new PageElement('form', array('idempotent' => TRUE));

		$form->set('request', $this->getRequest());
		$hbox = $form->append('hbox');
		$entry = $hbox->append('entry');
		$entry->set('name', 'q');
		$entry->set('value', _('Search...'));
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
	protected function helperPaging($engine, $request, $page, $limit, $pcnt,
			$pcur)
	{
		//XXX copied from ContentModule
		if($pcnt <= $limit)
			return;
		$pcnt = ceil($pcnt / $limit);
		$args = $request->getParameters();
		unset($args['page']);
		$r = $this->getRequest($request->getAction(), $args);
		$form = $page->append('form', array('idempotent' => TRUE,
				'request' => $r));
		$hbox = $form->append('hbox');
		//first page
		$hbox->append('link', array('stock' => 'gotofirst',
				'request' => $r, 'text' => ''));
		//previous page
		$args['page'] = max(1, $pcur - 1);
		$r = $this->getRequest($request->getAction(), $args);
		$hbox->append('link', array('stock' => 'previous',
				'request' => $r, 'text' => ''));
		//entry
		$hbox->append('entry', array('name' => 'page', 'width' => '4',
				'value' => $pcur));
		$hbox->append('label', array('text' => " / $pcnt"));
		//next page
		$args['page'] = min($pcur + 1, $pcnt);
		$r = $this->getRequest($request->getAction(), $args);
		$hbox->append('link', array('stock' => 'next',
				'request' => $r, 'text' => ''));
		//last page
		$args['page'] = $pcnt;
		$r = $this->getRequest($request->getAction(), $args);
		$hbox->append('link', array('stock' => 'gotolast',
				'request' => $r, 'text' => ''));
	}


	//SearchModule::pageSearch
	protected function pageSearch($engine, $request, $advanced = FALSE,
			$limit = FALSE)
	{
		$q = $request->get('q');
		$args = $q ? array('q' => $q) : FALSE;
		$case = $request->get('case') ? '1' : '0';

		$page = new Page;
		$page->set('title', _('Search'));
		$title = $page->append('title', array('stock' => 'search'));
		$title->set('text', $q ? _('Search results')
				: _('Search'));
		$form = $page->append('form');
		$r = $this->getRequest($advanced ? 'advanced' : FALSE);
		$form->set('request', $r);
		$entry = $form->append('entry');
		$entry->set('text', _('Search query: '));
		$entry->set('name', 'q');
		$entry->set('value', $request->get('q'));
		if($advanced)
		{
			$hbox = $form->append('hbox');
			$label = $hbox->append('label');
			$label->set('text', _('Search in: '));
			$checkbox = $hbox->append('checkbox');
			$checkbox->set('name', 'intitle');
			$checkbox->set('text', _('titles'));
			$checkbox->set('value', $request->get('intitle'));
			$checkbox = $hbox->append('checkbox');
			$checkbox->set('name', 'incontent');
			$checkbox->set('text', _('content'));
			$checkbox->set('value', $request->get('incontent'));
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
			$link->set('stock', 'remove');
			$link->set('text', _('Simpler search...'));
			$link->set('request', $this->getRequest(FALSE, $args));
		}
		else
		{
			$link->set('stock', 'add');
			$link->set('text', _('Advanced search...'));
			$link->set('request', $this->getRequest('advanced',
					$args));
		}
		return $page;
	}


	//SearchModule::query
	protected function query($engine, $string, $case, $intitle, $incontent,
			$user = FALSE, $module = FALSE)
	{
		global $config;
		$db = $engine->getDatabase();
		$query = static::$query.' AND (0=1';
		$regexp = $this->configGet('regexp');
		$func = $regexp ? 'regexp' : 'like';
		$wildcard = $regexp ? '' : '%';

		$string = str_replace('\\', '\\\\', trim($string));
		if($string == '')
			return TRUE;
		$q = explode(' ', $string);
		$args = array();
		$i = 0;
		if($intitle && count($q))
			foreach($q as $r)
			{
				$query .= ' OR title '.$db->$func($case)
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
				$query .= ' OR content '.$db->$func($case)
					." :arg$i";
				if($func == 'like')
				{
					$query .= ' ESCAPE :escape';
					$args['escape'] = '\\';
				}
				$args['arg'.$i++] = $wildcard.$r.$wildcard;
			}
		$query .= ') ORDER BY timestamp DESC';
		//paging
		if(($res = $db->query($engine, $query, $args)) === FALSE)
			return $engine->log('LOG_ERR', 'Unable to search');
		return $res;
	}
}

?>
