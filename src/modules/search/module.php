<?php //$Id$
//Copyright (c) 2011-2016 Pierre Pronchery <khorben@defora.org>
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



//SearchModule
class SearchModule extends Module
{
	//public
	//methods
	//useful
	//SearchModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
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
			case 'advanced':
			case 'default':
			case 'widget':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
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
	//IN:	module_id
	static protected $query_module = 'SELECT content_id AS id,
		timestamp AS date, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public
		FROM daportal_content_public
		WHERE module_id=:module_id';


	//methods
	//accessors
	//SearchModule::getLimit
	protected function getLimit(Engine $engine, Request $request = NULL)
	{
		if($request !== NULL
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
	protected function getPage(Engine $engine, Request $request = NULL)
	{
		if($request !== NULL && ($p = $request->get('page')) !== FALSE
				&& is_numeric($p) && $p >= 1)
			return $p;
		return 1;
	}


	//useful
	//SearchModule::actions
	protected function actions(Engine $engine, Request $request)
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
	protected function appendResult(Engine $engine, &$view, &$res)
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
	//SearchModule::callDefault
	protected function callDefault(Engine $engine, Request $request)
	{
		$case = FALSE;
		$limit = $this->getLimit($engine, $request);

		$page = $this->pageSearch($engine, $request, FALSE, $limit);
		if(($q = $request->get('q')) === FALSE || strlen($q) == 0)
			return new PageResponse($page);
		$time = microtime(TRUE);
		if(($res = $this->query($engine, $q, $case, TRUE, TRUE))
				=== FALSE)
		{
			$error = _('Unable to search');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		else if($res === TRUE)
			return new PageResponse($page);
		$time = ceil((microtime(TRUE) - $time) * 1000);
		return $this->helperResults($engine, $request, $page, $res,
				$limit, $time);
	}


	//SearchModule::callAdvanced
	protected function callAdvanced(Engine $engine, Request $request)
	{
		$case = $request->get('case') ? '1' : '0';
		$limit = $this->getLimit($engine, $request);

		$page = $this->pageSearch($engine, $request, TRUE, $limit);
		if(($q = $request->get('q')) === FALSE || strlen($q) == 0)
			return new PageResponse($page);
		$intitle = $request->get('intitle');
		$incontent = $request->get('incontent');
		if($intitle === FALSE && $incontent === FALSE)
			$intitle = $incontent = TRUE;
		$module = $request->get('inmodule');
		$time = microtime(TRUE);
		if(($res = $this->query($engine, $q, $case, $intitle,
				$incontent, FALSE, $module)) === FALSE)
		{
			$error = _('Unable to search');
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
			return new PageResponse($page,
				Response::$CODE_EUNKNOWN);
		}
		else if($res === TRUE)
			return new PageResponse($page);
		$time = ceil((microtime(TRUE) - $time) * 1000);
		return $this->helperResults($engine, $request, $page, $res,
				$limit, $time);
	}


	//SearchModule::callWidget
	protected function callWidget(Engine $engine, Request $request)
	{
		$form = new PageElement('form', array('idempotent' => TRUE));

		$form->set('request', $this->getRequest());
		$hbox = $form->append('hbox');
		$entry = $hbox->append('entry', array('name' => 'q',
				'text' => '', 'placeholder' => _('Search...')));
		$button = $hbox->append('button', array('stock' => 'search',
					'type' => 'submit',
					'text' => _('Search'),
					'autohide' => TRUE));
		return new PageResponse($form);
	}


	//helpers
	//SearchModule::helperAction
	protected function helperAction(Engine $engine, $stock,
			Request $request, $text)
	{
		$icon = new PageElement('image', array('stock' => $stock));
		$link = new PageElement('link', array('request' => $request,
				'text' => $text));
		return new PageElement('row', array('icon' => $icon,
				'label' => $link));
	}


	//SearchModule::helperPaging
	protected function helperPaging(Engine $engine, Request $request,
			PageElement $page, $limit, $pcnt, $pcur)
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


	//SearchModule::helperResults
	protected function helperResults(Engine $engine, Request $request,
			PageElement $page, $res, $limit, $time = FALSE)
	{
		$p = $this->getPage($engine, $request);
		$count = count($res);
		$columns = array('title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'),
			'preview' => _('Preview'));

		if(($offset = ($p - 1) * $limit) >= $count)
		{
			$p = 1;
			$offset = 0;
		}
		$results = $page->append('vbox');
		$results->set('id', 'search_results');
		$label = $results->append('label');
		$text = $count.' result(s)';
		if($time !== FALSE)
			$text .= ' in '.$time.' ms';
		$label->set('text', $text);
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
		return new PageResponse($page);
	}


	//SearchModule::pageSearch
	protected function pageSearch(Engine $engine, Request $request,
			$advanced = FALSE, $limit = FALSE)
	{
		$q = $request->get('q');
		$title = $q ? _('Search results') : _('Search');
		$args = $q ? array('q' => $q) : FALSE;
		$case = $request->get('case') ? '1' : '0';

		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'search',
				'text' => $title));
		$form = $page->append('form');
		$r = $this->getRequest($advanced ? 'advanced' : FALSE);
		$form->set('request', $r);
		$entry = $form->append('entry');
		$entry->set('text', _('Search query: '));
		$entry->set('name', 'q');
		$entry->set('value', $request->get('q'));
		if($advanced)
			$this->_pageSearchAdvanced($engine, $request, $form,
					$limit, $case);
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

	private function _pageSearchAdvanced(Engine $engine, Request $request,
			PageElement $form, $limit, $case)
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
		//modules
		$list = $engine->getModules();
		$modules = array();
		foreach($list as $m)
		{
			$module = Module::load($engine, $m);
			if($module instanceof ContentModule)
				$modules[] = $m;
		}
		if(count($modules))
		{
			$hbox->append('label', array('text' => _('module: ')));
			$value = $request->get('inmodule');
			$combobox = $hbox->append('combobox', array(
					'name' => 'inmodule',
					'value' => $value));
			$combobox->append('label', array('text' => _('Any')));
			asort($modules);
			foreach($modules as $m)
				$combobox->append('label', array(
						'text' => ucfirst($m),
						'value' => $m));
		}
		$hbox = $form->append('hbox');
		$radio = $hbox->append('radiobutton', array('name' => 'case',
				'value' => $case));
		$radio->append('label', array('text' => _('Case-insensitive'),
				'value' => '0'));
		$radio->append('label', array('text' => _('Case-sensitive'),
				'value' => '1'));
		$hbox = $form->append('hbox');
		$combobox = $hbox->append('combobox', array(
				'name' => 'limit', 'value' => $limit,
				'text' => _('Results per page:')));
		foreach(array(10, 20, 50, 100) as $i)
			$combobox->append('label', array('text' => $i,
					'value' => $i));
		$button = $form->append('button', array('type' => 'reset',
				'text' => _('Reset')));
	}


	//SearchModule::query
	protected function query(Engine $engine, $string, $case, $intitle,
			$incontent, $user = FALSE, $module = FALSE)
	{
		global $config;
		$db = $engine->getDatabase();
		$module = ($module !== FALSE) ? Module::load($engine, $module)
			: FALSE;
		$query = ($module !== FALSE) ? static::$query_module.' AND (0=1'
			: static::$query.' AND (0=1';
		$regexp = $this->configGet('regexp');
		$func = $regexp ? 'regexp' : 'like';
		$wildcard = $regexp ? '' : '%';

		$string = str_replace('\\', '\\\\', trim($string));
		if($string == '')
			return TRUE;
		$q = explode(' ', $string);
		$args = ($module !== FALSE)
			? array('module_id' => $module->getID()) : array();
		$i = 0;
		if($intitle && count($q))
			foreach($q as $r)
			{
				$query .= $case
					? ' OR title '.$db->$func()." :arg$i"
					: ' OR LOWER(title) '
						.$db->$func()." LOWER(:arg$i)";
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
				$query .= $case
					? ' OR content '.$db->$func()." :arg$i"
					: ' OR LOWER(content) '
						.$db->$func()." LOWER(:arg$i)";
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
