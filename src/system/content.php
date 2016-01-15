<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//Content
class Content
{
	//public
	//methods
	//essential
	//Content::Content
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();

		$this->stock = $module->getName();
		$this->module = $module;
		$this->user_id = $credentials->getUserID();
		$this->username = $credentials->getUsername();
		$this->group_id = $credentials->getGroupID();
		$this->group = $credentials->getGroupname();
		//properties
		if($properties === FALSE)
			$properties = array();
		foreach($properties as $k => $v)
			switch($k)
			{
				//private
				case 'enabled':
				case 'public':
					//boolean values
					$v = $database->isTrue($v);
					//fallback
				case 'content':
				case 'group_id':
				case 'group':
				case 'id':
				case 'title':
				case 'user_id':
				case 'username':
					$this->$k = $v;
					break;
				case 'timestamp':
					$v = $database->formatDate($v);
					$this->$k = $v;
					break;
				case 'groupname':
					//XXX for compatibility
					$this->group = $v;
					break;
				//protected
				default:
					$this->set($k, $v);
					break;
			}
		//translations
		$this->text_content_by = _('Content by');
		$this->text_link = _('Permalink');
		$this->text_more_content = _('More content...');
		$this->text_on = _('on');
		$this->text_open = _('Open');
		$this->text_publish = _('Publish');
		$this->text_submit_content = _('Submit content');
		$this->text_update = _('Update');
	}


	//accessors
	//Content::canAdmin
	public function canAdmin(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$credentials->isAdmin())
			return FALSE;
		return $this->canUpdate($engine, $request, $error);
	}


	//Content::canDelete
	public function canDelete(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $error);
	}


	//Content::canDisable
	public function canDisable(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $error);
	}


	//Content::canDisplay
	public function canDisplay(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return TRUE;
	}


	//Content::canEnable
	public function canEnable(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $error);
	}


	//Content::canPreview
	public function canPreview(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return $this->canDisplay($engine, $request, $error);
	}


	//Content::canPublish
	public function canPublish(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		if($request === NULL)
			return TRUE;
		if($request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		return TRUE;
	}


	//Content::canSubmit
	public function canSubmit(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();
		$properties = $this->properties;

		if($request === NULL)
			return TRUE;
		if($request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		//verify that the fields are set
		$fields = array();
		foreach($this->fields as $k => $v)
			switch($k)
			{
				case 'enabled':
				case 'public':
				case 'content':
				case 'group_id':
				case 'group':
				case 'id':
				case 'timestamp':
				case 'title':
				case 'user_id':
				case 'username':
					break;
				default:
					if(array_key_exists($k, $properties))
						break;
					else if($request->get($k) === FALSE)
						$fields[] = $v;
					break;
			}
		if(count($fields) > 0)
		{
			$error = implode($fields, _(' must be set')."\n")
				._(' must be set');
			return FALSE;
		}
		return TRUE;
	}


	//Content::canUnpublish
	public function canUnpublish(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		if($request === NULL)
			return TRUE;
		if($request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		return TRUE;
	}


	//Content::canUpdate
	public function canUpdate(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(!$credentials->isAdmin())
		{
			$error = _('Only administrators can update content');
			return FALSE;
		}
		if($request === NULL)
			return TRUE;
		if($request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		return TRUE;
	}


	//Content::canUpdateTimestamp
	public function canUpdateTimestamp(Engine $engine,
			Request $request = NULL, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if(!$credentials->isAdmin())
		{
			$error = _('Only administrators can update timestamps');
			return FALSE;
		}
		if($request === NULL)
			return TRUE;
		if($request->isIdempotent())
		{
			$error = _('The request expired or is invalid');
			return FALSE;
		}
		return TRUE;
	}


	//Content::get
	public function get($property)
	{
		if(!array_key_exists($property, $this->properties))
			return FALSE;
		return $this->properties[$property];
	}


	//Content::getColumns
	static public function getColumns()
	{
		return array('icon' => '', 'title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'));
	}


	//Content::getContent
	public function getContent(Engine $engine)
	{
		if($this->content === FALSE)
			return '';
		return $this->content;
	}


	//Content::getDate
	public function getDate($format = FALSE)
	{
		$informat = FALSE;

		if(($timestamp = $this->timestamp) === FALSE)
		{
			$informat = '%Y-%m-%d %H:%M:%S';
			$timestamp = Date::formatTimestamp(time(), $informat);
		}
		return Date::format($timestamp, $format, $informat);
	}


	//Content::getGroup
	public function getGroup()
	{
		return $this->group;
	}


	//Content::getGroupID
	public function getGroupID()
	{
		return $this->group_id;
	}


	//Content::getID
	public function getID()
	{
		return $this->id;
	}


	//Content::getModule
	public function getModule()
	{
		return $this->module;
	}


	//Content::getOrder
	static public function getOrder(Engine $engine, $order = FALSE)
	{
		switch($order)
		{
			case 'timestamp':
				return 'timestamp DESC';
			default:
				return static::$list_order;
		}
	}


	//Content::getProperties
	public function getProperties()
	{
		return $this->properties;
	}


	//Content::getRequest
	public function getRequest($action = FALSE, $parameters = FALSE)
	{
		return new Request($this->module->getName(), $action,
			$this->getID(), $this->getTitle(), $parameters);
	}


	//Content::getTitle
	public function getTitle()
	{
		return $this->title;
	}


	//Content::getUser
	public function getUser(Engine $engine)
	{
		//XXX may fail
		return new User($engine, $this->user_id, $this->username);
	}


	//Content::getUserID
	public function getUserID()
	{
		return $this->user_id;
	}


	//Content::getUsername
	public function getUsername()
	{
		return $this->username;
	}


	//Content::isEnabled
	public function isEnabled()
	{
		return $this->enabled;
	}


	//Content::isPublic
	public function isPublic()
	{
		return $this->public;
	}


	//Content::set
	public function set($property, $value)
	{
		if(!isset($this->fields[$property]))
			return FALSE;
		$this->properties[$property] = $value;
		return TRUE;
	}


	//Content::setContent
	public function setContent(Engine $engine, $content)
	{
		if($content === FALSE)
			$content = '';
		$this->content = $content;
	}


	//Content::setPublic
	public function setPublic($public)
	{
		$this->public = $public ? TRUE : FALSE;
	}


	//Content::setTitle
	public function setTitle($title)
	{
		$this->title = $title;
	}


	//useful
	//Content::delete
	public function delete(Engine $engine)
	{
		$database = $engine->getDatabase();
		$query = static::$query_delete;
		$args = array('content_id' => $this->id);

		if(!$this->canDelete($engine))
			return FALSE;
		return $database->query($engine, $query, $args);
	}


	//Content::disable
	public function disable(Engine $engine)
	{
		$database = $engine->getDatabase();
		$query = static::$query_disable;
		$args = array('content_id' => $this->id);

		if(!$this->canDisable($engine))
			return FALSE;
		return $database->query($engine, $query, $args);
	}


	//Content::enable
	public function enable(Engine $engine)
	{
		$database = $engine->getDatabase();
		$query = static::$query_enable;
		$args = array('content_id' => $this->id);

		if(!$this->canEnable($engine))
			return FALSE;
		return $database->query($engine, $query, $args);
	}


	//Content::display
	public function display(Engine $engine, Request $request = NULL)
	{
		$type = ($request !== NULL) ? $request->get('display') : FALSE;
		$vbox = new PageElement('vbox');

		if($type === FALSE || $type == 'title')
			$vbox->append($this->displayTitle($engine, $request));
		if($type === FALSE || $type == 'toolbar')
			$vbox->append($this->displayToolbar($engine, $request));
		if($type === FALSE || $type == 'metadata')
			$vbox->append($this->displayMetadata($engine,
					$request));
		if($type === FALSE || $type == 'content'
				|| strncmp($type, 'content::', 9) == 0)
			$vbox->append($this->displayContent($engine, $request));
		if($type === FALSE || $type == 'buttons')
			$vbox->append($this->displayButtons($engine, $request));
		return $vbox;
	}


	//Content::displayButtons
	public function displayButtons(Engine $engine, Request $request)
	{
		$hbox = new PageElement('hbox');
		$r = $this->module->getRequest();
		$hbox->append('link', array('stock' => $this->stock_back,
				'request' => $r,
				'text' => $this->text_more_content));
		$r = $this->getRequest();
		$hbox->append('link', array('request' => $r,
				'stock' => $this->stock_link,
				'text' => $this->text_link));
		return $hbox;
	}


	//Content::displayContent
	public function displayContent(Engine $engine, Request $request)
	{
		$text = $this->getContent($engine);

		return new PageElement('label', array('text' => $text));
	}


	//Content::displayList
	static public function displayList(Engine $engine,
			Request $request = NULL, $content = FALSE,
			$limit = FALSE, $offset = 0)
	{
		$view = new PageElement('treeview', array(
				'columns' => static::getColumns(),
				'alternate' => static::$list_alternate));

		if($content === FALSE)
			$content = new ArrayIterator();
		else if(is_array($content))
			$content = new ArrayIterator($content);
		if($limit === FALSE && ($limit = static::$list_limit) === FALSE)
			$limit = $content->count();
		for($i = 0, $content->seek($offset);
			$i < $limit && $content->valid();
			$i++, $content->next())
		{
			$c = $content->current();
			$view->append($c->displayRow($engine, $request));
		}
		return $view;
	}


	//Content::displayMetadata
	public function displayMetadata(Engine $engine, Request $request = NULL)
	{
		$r = new Request('user', FALSE, $this->getUserID(),
			$this->getUsername());

		$meta = new PageElement('label', array(
				'text' => $this->text_content_by.' '));
		$link = $meta->append('link', array('request' => $r,
				'text' => $this->getUsername()));
		$date = $this->getDate();
		$meta->append('label', array(
				'text' => ' '.$this->text_on.' '.$date));
		return $meta;
	}


	//Content::displayRow
	public function displayRow(Engine $engine, Request $request = NULL)
	{
		$r = array();
		$no = new PageElement('image', array('stock' => 'no',
			'size' => 16, 'title' => _('Disabled')));
		$yes = new PageElement('image', array('stock' => 'yes',
			'size' => 16, 'title' => _('Enabled')));

		//title
		$rq = $this->getRequest();
		$link = new PageElement('link', array('request' => $rq,
			'text' => $this->getTitle()));
		$r['title'] = $link;
		$r['enabled'] = $this->isEnabled() ? $yes : $no;
		$r['public'] = $this->isPublic() ? $yes : $no;
		//username
		$rq = new Request('user', FALSE, $this->getUserID(),
			$this->getUsername());
		$link = new PageElement('link', array('request' => $rq,
			'stock' => 'user', 'text' => $this->getUsername()));
		$r['username'] = $link;
		//date
		$r['date'] = $this->getDate();
		//id
		$r['id'] = 'ids['.$this->getID().']';
		return new PageElement('row', $r);
	}


	//Content::displayToolbar
	public function displayToolbar(Engine $engine, Request $request = NULL)
	{
		$credentials = $engine->getCredentials();
		$action = ($request !== NULL) ? $request->getAction() : FALSE;

		$toolbar = new PageElement('toolbar');
		if($credentials->isAdmin())
		{
			$r = $this->module->getRequest('admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		if($action != 'submit' && $this->module->canSubmit($engine,
				NULL, $this))
		{
			$r = $this->module->getRequest('submit');
			$toolbar->append('button', array('request' => $r,
					'stock' => $this->stock_submit,
					'text' => $this->text_submit_content));
		}
		if($this->getID() !== FALSE)
		{
			if($action != 'publish' && !$this->isPublic()
					&& $this->canPublish($engine, NULL,
						$this))
			{
				$r = $this->getRequest('publish');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'publish',
						'text' => $this->text_publish));
			}
			if($action != 'update' && $this->canUpdate($engine,
					NULL, $this))
			{
				$r = $this->getRequest('update');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'update',
						'text' => $this->text_update));
			}
		}
		//FIXME implement
		return $toolbar;
	}


	//Content::displayTitle
	public function displayTitle(Engine $engine, Request $request)
	{
		return new PageElement('title', array(
			'stock' => $this->stock,
			'text' => $this->getTitle()));
	}


	//Content::form
	public function form(Engine $engine, Request $request = NULL)
	{
		return ($this->id !== FALSE)
			? $this->_formUpdate($engine, $request)
			: $this->_formSubmit($engine, $request);
	}

	protected function _formSubmit(Engine $engine, Request $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'placeholder' => _('Title'),
				'value' => $request->get('title')));
		$vbox->append('textview', array('name' => 'content',
				'text' => _('Content: '),
				'value' => $request->get('content')));
		return $vbox;
	}

	protected function _formUpdate(Engine $engine, Request $request)
	{
		$vbox = new PageElement('vbox');
		if(($value = $request->get('title')) === FALSE)
			$value = $this->getTitle();
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'placeholder' => _('Title'),
				'value' => $value));
		$label = $vbox->append('label', array(
				'text' => _('Content: ')));
		if(($value = $request->get('content')) === FALSE)
			$value = $this->getContent($engine);
		$label->append('textview', array('name' => 'content',
				'value' => $value));
		return $vbox;
	}


	//Content::formPreview
	public function formPreview(Engine $engine, Request $request)
	{
		$properties = $this->properties;

		$content = clone $this;
		foreach($this->fields as $k => $v)
		{
			if(($p = $request->get($k)) === FALSE)
				continue;
			switch($k)
			{
				case 'content':
					$content->setContent($engine, $p);
					break;
				case 'title':
					$content->setTitle($p);
					break;
				default:
					$content->set($k, $p);
					break;
			}
		}
		$vbox = new PageElement('vbox');
		$vbox->append('title', array('stock' => 'preview',
				'text' => _('Preview: ').$content->getTitle()));
		$vbox->append($content->displayMetadata($engine, $request));
		$vbox->append($content->displayContent($engine, $request));
		return $vbox;
	}


	//Content::preview
	public function preview(Engine $engine, Request $request = NULL)
	{
		$vbox = new PageElement('vbox');

		$vbox->append($this->previewTitle($engine, $request));
		$vbox->append($this->previewMetadata($engine, $request));
		$vbox->append($this->previewContent($engine, $request));
		$vbox->append($this->previewButtons($engine, $request));
		return $vbox;
	}


	//Content::previewButtons
	public function previewButtons(Engine $engine, Request $request = NULL)
	{
		$hbox = new PageElement('hbox');

		$r = $this->getRequest();
		$hbox->append('button', array('request' => $r,
				'stock' => $this->stock_open,
				'text' => $this->text_open));
		return $hbox;
	}


	//Content::previewContent
	public function previewContent(Engine $engine, Request $request = NULL)
	{
		$content = $this->getContent($engine);
		$length = $this->preview_length;
		$text = ($length <= 0 || strlen($content) < $length)
			? $content : substr($content, 0, $length).'...';

		return new PageElement('label', array('text' => $text));
	}


	//Content::previewMetadata
	public function previewMetadata(Engine $engine, Request $request = NULL)
	{
		return $this->displayMetadata($engine, $request);
	}


	//Content::previewTitle
	public function previewTitle(Engine $engine, Request $request = NULL)
	{
		if($this->id === FALSE)
			return new PageElement('title', array(
					'text' => $this->getTitle()));
		$title = new PageElement('title');
		$title->append('link', array(
				'request' => $this->getRequest(),
				'text' => $this->getTitle()));
		return $title;
	}


	//Content::save
	public function save(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$ret = ($this->id !== FALSE)
			? $this->_saveUpdate($engine, $request, $error)
			: $this->_saveInsert($engine, $request, $error);
		if($ret === FALSE || $request === NULL)
			return $ret;
		//reflect the new properties
		foreach($this->fields as $f)
			$this->set($f, $request->get($f));
		return $ret;
	}

	protected function _saveInsert(Engine $engine, Request $request = NULL,
			&$error)
	{
		$database = $engine->getDatabase();
		$query = static::$query_insert;
		$args = array('module_id' => $this->module->getID(),
			'user_id' => $this->user_id,
			'enabled' => $this->enabled,
			'public' => $this->public);

		if(!$this->canSubmit($engine, $request, $error))
			return FALSE;
		foreach($this->fields as $k => $v)
			switch($k)
			{
				case 'title':
				case 'content':
					$args[$k] = ($this->$k !== FALSE)
						? $this->$k : '';
					break;
			}
		//set the timestamp if necessary
		if($request !== NULL
				&& ($timestamp = $request->get('timestamp'))
				!== FALSE
				&& $this->canUpdateTimestamp($engine, $request))
		{
			$query = static::$query_insert_timestamp;
			$args['timestamp'] = $timestamp;
		}
		//insert the content
		$error = _('Could not insert the content');
		if($database->query($engine, $query, $args) === FALSE)
			return FALSE;
		return ($this->id = $database->getLastID($engine,
				'daportal_content', 'content_id')) !== FALSE
			? TRUE : FALSE;
	}

	protected function _saveUpdate(Engine $engine, Request $request = NULL,
			&$error)
	{
		$database = $engine->getDatabase();
		$query = static::$query_update;
		$args = array('module_id' => $this->module->getID(),
			'content_id' => $this->id,
			'enabled' => $this->enabled,
			'public' => $this->public);

		if(!$this->canUpdate($engine, $request, $error))
			return FALSE;
		foreach($this->fields as $k => $v)
			switch($k)
			{
				case 'title':
				case 'content':
					$args[$k] = ($this->$k !== FALSE)
						? $this->$k : '';
					if($request === NULL)
						break;
					if(($v = $request->get($k)) === FALSE)
						break;
					$args[$k] = $v;
					break;
			}
		//set the timestamp if necessary
		if($request !== NULL
				&& ($timestamp = $request->get('timestamp'))
				!== FALSE
				&& $this->canUpdateTimestamp($engine, $request))
		{
			$query = static::$query_update_timestamp;
			$args['timestamp'] = $timestamp;
		}
		//update the content
		$error = _('Could not update the content');
		//FIXME detect errors!@#$%
		if(($ret = $database->query($engine, $query, $args)) === FALSE)
			return FALSE;
		foreach($args as $k => $v)
			switch($k)
			{
				case 'title':
				case 'content':
					$this->$k = $v;
					break;
			}
		return TRUE;
	}


	//public static
	//Content::countAll
	static public function countAll(Engine $engine, Module $module,
			$user = FALSE)
	{
		if(($res = static::_listAll($engine, $module, FALSE, FALSE,
				FALSE, $user)) === FALSE)
			return FALSE;
		return $res->count();
	}


	//Content::listAll
	static public function listAll(Engine $engine, Module $module,
			$order = FALSE, $limit = FALSE, $offset = FALSE,
			$user = FALSE)
	{
		if(($res = static::_listAll($engine, $module, $order, $limit,
				$offset, $user)) === FALSE)
			return FALSE;
		return static::listFromResults($engine, $module, $res);
	}

	static protected function _listAll(Engine $engine, Module $module,
			$order, $limit, $offset, $user)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();
		$query = static::$query_list;
		$args = array('module_id' => $module->getID());

		if($user instanceof User)
		{
			$query = static::$query_list_user;
			$args['user_id'] = $user->getUserID();
			if(($id = $credentials->getUserID()) != 0
					&& $id == $user->getUserID())
				$query = static::$query_list_user_private;
		}
		else if($user instanceof Group)
		{
			$query = static::$query_list_group;
			$args['group_id'] = $user->getGroupID();
		}
		return static::query($engine, $query, $args, $order, $limit,
				$offset);
	}


	//Content::listFromResults
	static public function listFromResults(Engine $engine, Module $module,
			$results)
	{
		return new ContentResult($engine, $module, static::$class,
			$results);
	}


	//Content::load
	static public function load(Engine $engine, Module $module, $id,
			$title = FALSE)
	{
		if(($res = static::_load($engine, $module, $id, $title))
				=== FALSE)
			return FALSE;
		return static::loadFromProperties($engine, $module, $res);
	}

	static protected function _load(Engine $engine, Module $module, $id,
			$title)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();
		$query = static::$query_load;
		$column = static::$load_title;
		$args = array('module_id' => $module->getID(),
			'user_id' => $credentials->getUserID(),
			'content_id' => $id);
		$from = array('-', '\\');
		$to = array('_', '\\\\');

		if($engine instanceof HTTPFriendlyEngine)
		{
			//XXX friendly links may compress slashes
			$from[] = '/';
			$to[] = '%';
		}
		if(is_string($title))
		{
			$query .= ' AND '.$column.' '.$database->like(FALSE)
				.' :title ESCAPE :escape';
			$args['title'] = str_replace($from, $to, $title);
			//require 32 correct characters in the title
			if(strlen($title) >= 32)
				$args['title'] .= '%';
			$args['escape'] = '\\';
		}
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| $res->count() != 1)
			return FALSE;
		return $res->current();
	}


	//Content::loadFromProperties
	static public function loadFromProperties(Engine $engine,
			Module $module, $properties)
	{
		$class = static::$class;

		return new $class($engine, $module, $properties);
	}


	//Content::loadFromResult
	static public function loadFromResult(Engine $engine, Module $module,
			DatabaseResult $result)
	{
		$class = static::$class;

		return new $class($engine, $module, $result->current());
	}


	//protected
	//properties
	static protected $class = 'Content';
	protected $fields = array('title' => 'Title', 'content' => 'Content');
	static protected $list_alternate = FALSE;
	static protected $list_limit = 20;
	static protected $list_order = 'timestamp DESC';
	static protected $load_title = 'title';
	protected $preview_length = 150;
	//stock icons
	protected $stock = FALSE;
	protected $stock_back = 'back';
	protected $stock_link = 'link';
	protected $stock_open = 'open';
	protected $stock_submit = 'new';
	//strings
	static protected $text_content = 'Content';
	protected $text_content_by = 'Content by';
	protected $text_link = 'Permalink';
	protected $text_more_content = 'More content...';
	protected $text_on = 'on';
	protected $text_open = 'Open';
	protected $text_publish = 'Publish';
	protected $text_submit_content = 'Submit content';
	protected $text_update = 'Update';
	//queries
	//IN:	module_id
	//	content_id
	static protected $query_delete = 'DELETE FROM daportal_content
		WHERE module_id=:module_id AND content_id=:content_id';
	//IN:	module_id
	//	content_id
	static protected $query_disable = "UPDATE daportal_content
		SET enabled='0'
		WHERE module_id=:module_id AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	static protected $query_enable = "UPDATE daportal_content
		SET enabled='1'
		WHERE module_id=:module_id AND content_id=:content_id";
	//IN:	module_id
	//	user_id
	//	title
	//	content
	//	enabled
	//	public
	static protected $query_insert = 'INSERT INTO daportal_content
		(module_id, user_id, title, content, enabled, public)
		VALUES (:module_id, :user_id, :title, :content, :enabled,
			:public)';
	//IN:	module_id
	//	user_id
	//	title
	//	content
	//	enabled
	//	public
	//	timestamp
	static protected $query_insert_timestamp = 'INSERT INTO daportal_content
		(module_id, user_id, title, content, enabled, public, timestamp)
		VALUES (:module_id, :user_id, :title, :content, :enabled,
			:public, :timestamp)';
	//IN:	module_id
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public
		FROM daportal_content_public
		WHERE module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, module_id, module,
		daportal_content_public.user_id AS user_id, username,
		daportal_content_public.group_id AS group_id,
		daportal_content_public.groupname AS groupname, title, content,
		daportal_content_public.enabled AS enabled, public
		FROM daportal_content_public, daportal_user_group,
		daportal_group_enabled
		WHERE module_id=:module_id
		AND daportal_content_public.user_id=daportal_user_group.user_id
		AND daportal_user_group.group_id=daportal_group_enabled.group_id
		AND (daportal_user_group.group_id=:group_id
		OR daportal_content_public.group_id=:group_id)';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public
		FROM daportal_content_public
		WHERE module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT content_id AS id,
		timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public
		FROM daportal_content_enabled
		WHERE module_id=:module_id
		AND user_id=:user_id';
	//FIXME default to $query_list.' AND ...' ?
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public
		FROM daportal_content_enabled
		WHERE module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	//	title
	//	content
	//	enabled
	//	public
	static protected $query_update = 'UPDATE daportal_content
		SET title=:title, content=:content, enabled=:enabled,
		public=:public
		WHERE module_id=:module_id
		AND content_id=:content_id';
	//IN:	module_id
	//	content_id
	//	title
	//	content
	//	enabled
	//	public
	//	timestamp
	static protected $query_update_timestamp = 'UPDATE daportal_content
		SET title=:title, content=:content, enabled=:enabled,
		public=:public, timestamp=:timestamp
		WHERE module_id=:module_id
		AND content_id=:content_id';


	//methods
	//accessors
	//Content::configGet
	protected function configGet($variable)
	{
		global $config;
		$name = $this->getModule()->getName();

		return $config->get('module::'.$name, $variable);
	}


	//Content::setID
	protected function setID($id)
	{
		$this->id = $id;
	}


	//useful
	//Content::query
	static protected function query(Engine $engine, $query, $args = FALSE,
			$order = FALSE, $limit = FALSE, $offset = FALSE)
	{
		$database = $engine->getDatabase();

		if(($order = static::getOrder($engine, $order)) !== FALSE)
			$query .= ' ORDER BY '.$order;
		$query .= $database->limit($limit, $offset);
		return $database->query($engine, $query, $args);
	}


	//private
	//properties
	private $id = FALSE;
	private $timestamp = FALSE;
	private $module;
	private $user_id;
	private $username;
	private $group_id;
	private $group;
	private $title = FALSE;
	private $content = FALSE;
	private $enabled = TRUE;
	private $public = FALSE;
	private $properties = array();
}

?>
