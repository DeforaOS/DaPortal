<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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
	public function __construct($engine, $module, $properties = FALSE)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();

		$this->class = get_class();
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
				case 'content':
				case 'group_id':
				case 'group':
				case 'id':
				case 'timestamp':
				case 'title':
				case 'user_id':
				case 'username':
					$this->$k = $v;
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
	public function canAdmin($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Permission denied');
		if(!$credentials->isAdmin())
			return FALSE;
		return $this->canUpdate($engine, $request, $error);
	}


	//Content::canDelete
	public function canDelete($engine, $request = FALSE, &$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $content, $error);
	}


	//Content::canDisable
	public function canDisable($engine, $request = FALSE, &$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $content, $error);
	}


	//Content::canEnable
	public function canEnable($engine, $request = FALSE, &$error = FALSE)
	{
		return $this->canAdmin($engine, $request, $content, $error);
	}


	//Content::canPreview
	public function canPreview($engine, $request = FALSE, &$error = FALSE)
	{
		return TRUE;
	}


	//Content::canPublish
	public function canPublish($engine, $request = FALSE, &$error = FALSE)
	{
		if($request === FALSE)
			return TRUE;
		$error = _('The request expired or is invalid');
		return !$request->isIdempotent();
	}


	//Content::canSubmit
	public function canSubmit($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();
		$sep = '';

		if($request === FALSE)
			return TRUE;
		$error = _('The request expired or is invalid');
		if($request->isIdempotent())
			return FALSE;
		//verify that the fields are set
		$error = '';
		foreach($this->fields as $k => $v)
			//XXX not so elegant
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
					if(array_key_exists($k, $this->properties))
						break;
					else if($request->get($k) === FALSE)
					{
						$error .= $sep.$v.' must be set';
						$sep = "\n";
					}
					break;
			}
		if(strlen($error) > 0)
			return FALSE;
		return TRUE;
	}


	//Content::canUnpublish
	public function canUnpublish($engine, $request = FALSE, &$error = FALSE)
	{
		if($request === FALSE)
			return TRUE;
		$error = _('The request expired or is invalid');
		return !$request->isIdempotent();
	}


	//Content::canUpdate
	public function canUpdate($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Only administrators can update content');
		if(!$credentials->isAdmin())
			return FALSE;
		if($request === FALSE)
			return TRUE;
		$error = _('The request expired or is invalid');
		if($request->isIdempotent())
			return FALSE;
		return TRUE;
	}


	//Content::canUpdateTimestamp
	public function canUpdateTimestamp($engine, $request = FALSE,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('Only administrators can update timestamps');
		if(!$credentials->isAdmin())
			return FALSE;
		if($request === FALSE)
			return TRUE;
		$error = _('The request expired or is invalid');
		if($request->isIdempotent())
			return FALSE;
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
	public function getContent($engine)
	{
		if($this->content === FALSE)
			return '';
		return $this->content;
	}


	//Content::getDate
	public function getDate($engine, $format = FALSE)
	{
		$database = $engine->getDatabase();

		if(($timestamp = $this->timestamp) === FALSE)
			$timestamp = strftime('%d/%m/%Y %H:%M:%S', time());
		return $database->formatDate($engine, $timestamp, $format);
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
	public function getUser($engine)
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
	public function setContent($engine, $content)
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
	public function delete($engine)
	{
		$database = $engine->getDatabase();
		$query = $this->query_delete;
		$args = array('content_id' => $this->id);

		if(!$this->canDelete($engine))
			return FALSE;
		return $database->query($engine, $query, $args);
	}


	//Content::disable
	public function disable($engine)
	{
		$database = $engine->getDatabase();
		$query = $this->query_disable;
		$args = array('content_id' => $this->id);

		if(!$this->canDisable($engine))
			return FALSE;
		return $database->query($engine, $query, $args);
	}


	//Content::enable
	public function enable($engine)
	{
		$database = $engine->getDatabase();
		$query = $this->query_enable;
		$args = array('content_id' => $this->id);

		if(!$this->canEnable($engine))
			return FALSE;
		return $database->query($engine, $query, $args);
	}


	//Content::display
	public function display($engine, $request)
	{
		$type = ($request !== FALSE) ? $request->get('type') : FALSE;

		$page = new Page(array('title' => $this->getTitle()));
		if($type === FALSE || $type == 'title')
			$page->append($this->displayTitle($engine, $request));
		$vbox = $page->append('vbox');
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
		return $page;
	}


	//Content::displayButtons
	public function displayButtons($engine, $request)
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
	public function displayContent($engine, $request)
	{
		$text = $this->getContent($engine);

		return new PageElement('label', array('text' => $text));
	}


	//Content::displayMetadata
	public function displayMetadata($engine, $request)
	{
		$r = new Request('user', FALSE, $this->getUserID(),
			$this->getUsername());

		$meta = new PageElement('label', array(
				'text' => $this->text_content_by.' '));
		$link = $meta->append('link', array('request' => $r,
				'text' => $this->getUsername()));
		$date = $this->getDate($engine);
		$meta->append('label', array(
				'text' => ' '.$this->text_on.' '.$date));
		return $meta;
	}


	//Content::displayRow
	public function displayRow($engine, $request = FALSE)
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
		$r['date'] = $this->getDate($engine);
		//id
		$r['id'] = 'content_id:'.$this->getID();
		return new PageElement('row', $r);
	}


	//Content::displayToolbar
	public function displayToolbar($engine, $request)
	{
		$credentials = $engine->getCredentials();

		$toolbar = new PageElement('toolbar');
		if($credentials->isAdmin($engine))
		{
			$r = $this->module->getRequest('admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		if($this->module->canSubmit($engine, FALSE, $this))
		{
			$r = $this->module->getRequest('submit');
			$toolbar->append('button', array('request' => $r,
					'stock' => $this->stock_submit,
					'text' => $this->text_submit_content));
		}
		if($this->getID() !== FALSE)
		{
			if(!$this->isPublic() && $this->canPublish($engine,
					FALSE, $this))
			{
				$r = $this->getRequest('publish');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'publish',
						'text' => $this->text_publish));
			}
			if($this->canUpdate($engine, FALSE, $this))
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
	public function displayTitle($engine, $request)
	{
		return new PageElement('title', array(
			'stock' => $this->stock,
			'text' => $this->getTitle()));
	}


	//Content::form
	public function form($engine, $request = FALSE)
	{
		return ($this->id !== FALSE)
			? $this->_formUpdate($engine, $request)
			: $this->_formSubmit($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->get('title')));
		$vbox->append('textview', array('name' => 'content',
				'text' => _('Content: '),
				'value' => $request->get('content')));
		return $vbox;
	}

	protected function _formUpdate($engine, $request)
	{
		$vbox = new PageElement('vbox');
		if(($value = $request->get('title')) === FALSE)
			$value = $this->getTitle();
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
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
	public function formPreview($engine, $request)
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
	public function preview($engine, $request = FALSE)
	{
		$vbox = new PageElement('vbox');

		$vbox->append($this->previewTitle($engine, $request));
		$vbox->append($this->previewMetadata($engine, $request));
		$vbox->append($this->previewContent($engine, $request));
		$vbox->append($this->previewButtons($engine, $request));
		return $vbox;
	}


	//Content::previewButtons
	public function previewButtons($engine, $request = FALSE)
	{
		$hbox = new PageElement('hbox');

		$r = $this->getRequest();
		$hbox->append('button', array('request' => $r,
				'stock' => $this->stock_open,
				'text' => $this->text_open));
		return $hbox;
	}


	//Content::previewContent
	public function previewContent($engine, $request = FALSE)
	{
		$content = $this->getContent($engine);
		$length = $this->preview_length;

		$text = ($length <= 0 || strlen($content) < $length)
			? $content : substr($content, 0, $length).'...';

		return new PageElement('label', array('text' => $text));
	}


	//Content::previewMetadata
	public function previewMetadata($engine, $request = FALSE)
	{
		return $this->displayMetadata($engine, $request);
	}


	//Content::previewTitle
	public function previewTitle($engine, $request = FALSE)
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
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		$ret = ($this->id !== FALSE)
			? $this->_saveUpdate($engine, $request, $error)
			: $this->_saveInsert($engine, $request, $error);
		if($ret === FALSE || $request === FALSE)
			return $ret;
		//reflect the new properties
		foreach($this->fields as $f)
			$this->set($f, $request->get($f));
		return $ret;
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = $this->query_insert;
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
					$args[$k] = $this->$k;
					break;
			}
		//set the timestamp if necessary
		if($request !== FALSE
				&& ($timestamp = $request->get('timestamp'))
				!== FALSE
				&& $this->canUpdateTimestamp($engine, $request))
		{
			$query = $this->query_insert_timestamp;
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

	protected function _saveUpdate($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = $this->query_update;
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
					$args[$k] = $this->$k;
					if($request === FALSE)
						break;
					if(($v = $request->get($k)) === FALSE)
						break;
					$args[$k] = $v;
					break;
			}
		//set the timestamp if necessary
		if($request !== FALSE
				&& ($timestamp = $request->get('timestamp'))
				!== FALSE
				&& $this->canUpdateTimestamp($engine, $request))
		{
			$query = $this->query_update_timestamp;
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
	static public function countAll($engine, $module, $user = FALSE)
	{
		$class = get_class();
		return $class::_countAll($engine, $module, $user, $class);
	}

	static protected function _countAll($engine, $module, $user, $class)
	{
		$database = $engine->getDatabase();
		$query = $class::$query_list_count;
		$args = array('module_id' => $module->getID());

		if($user !== FALSE && $user instanceof User)
		{
			$query = $class::$query_list_user_count;
			$args['user_id'] = $user->getUserID();
		}
		else if($user !== FALSE && $user instanceof Group)
		{
			$query = $class::$query_list_group_count;
			$args['group_id'] = $user->getGroupID();
		}
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return $res[0]['count'];
	}


	//Content::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $order = FALSE, $user = FALSE)
	{
		//XXX ugly hack
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = $class::$list_order;
				break;
		}
		return $class::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}

	static protected function _listAll($engine, $module, $limit, $offset,
			$order, $user, $class)
	{
		$ret = array();
		$vbox = new PageElement('vbox');
		$database = $engine->getDatabase();
		$query = $class::$query_list;
		$args = array('module_id' => $module->getID());

		if($user !== FALSE && $user instanceof User)
		{
			$query = $class::$query_list_user;
			$args['user_id'] = $user->getUserID();
		}
		else if($user !== FALSE && $user instanceof Group)
		{
			$query = $class::$query_list_group;
			$args['group_id'] = $user->getGroupID();
		}
		if($order !== FALSE)
			$query .= ' ORDER BY '.$order;
		if($limit !== FALSE || $offset !== FALSE)
			$query .= $database->offset($limit, $offset);
		if(($res = $database->query($engine, $query, $args)) === FALSE)
			return FALSE;
		while(($r = array_shift($res)) != NULL)
			$ret[] = new $class($engine, $module, $r);
		return $ret;
	}


	//Content::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		return Content::_load($engine, $module, $id, $title,
				'Content');
	}

	static protected function _load($engine, $module, $id, $title, $class)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();
		$query = Content::$query_load;
		$args = array('module_id' => $module->getID(),
			'user_id' => $credentials->getUserID(),
			'content_id' => $id);
		$from = array('-', '\\');
		$to = array('_', '\\\\');

		if(is_string($title))
		{
			$query .= ' AND title '.$database->like(FALSE)
				.' :title ESCAPE :escape';
			$args['title'] = str_replace($from, $to, $title);
			$args['escape'] = '\\';
		}
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return new $class($engine, $module, $res[0]);
	}


	//protected
	//properties
	protected $class = FALSE;
	protected $fields = array('title' => 'Title', 'content' => 'Content');
	static protected $list_order = 'timestamp DESC';
	protected $preview_length = 150;
	//stock icons
	protected $stock = FALSE;
	protected $stock_back = 'back';
	protected $stock_link = 'link';
	protected $stock_open = 'open';
	protected $stock_submit = 'new';
	//strings
	protected $text_content_by = 'Content by';
	protected $text_link = 'Permalink';
	protected $text_more_content = 'More content...';
	protected $text_on = 'on';
	protected $text_open = 'Open';
	protected $text_publish = 'Publish';
	protected $text_submit_content = 'Submit content';
	protected $text_update = 'Update';
	//queries
	//IN:	content_id
	protected $query_delete = 'DELETE FROM daportal_content
		WHERE content_id=:content_id';
	//IN:	content_id
	protected $query_disable = "UPDATE daportal_content
		SET enabled='0'
		WHERE content_id=:content_id";
	//IN:	content_id
	protected $query_enable = "UPDATE daportal_content
		SET enabled='1'
		WHERE content_id=:content_id";
	//IN:	module_id
	//	user_id
	//	title
	//	content
	//	enabled
	//	public
	protected $query_insert = 'INSERT INTO daportal_content
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
	protected $query_insert_timestamp = 'INSERT INTO daportal_content
		(module_id, user_id, title, content, enabled, public, timestamp)
		VALUES (:module_id, :user_id, :title, :content, :enabled,
			:public, :timestamp)';
	//IN:	module_id
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content_public.enabled AS enabled, public,
		content
		FROM daportal_content_public, daportal_user_enabled
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id';
	//IN:	module_id
	static protected $query_list_count = 'SELECT COUNT(*) AS count
		FROM daportal_content_public
		WHERE daportal_content_public.module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content_enabled.enabled AS enabled, public,
		content
		FROM daportal_content_enabled, daportal_user_enabled,
		daportal_user_group, daportal_group_enabled
		WHERE daportal_content_enabled.user_id
		=daportal_user_enabled.user_id
		AND daportal_user_enabled.user_id=daportal_user_group.user_id
		AND daportal_user_group.group_id=daportal_group_enabled.group_id
		AND daportal_content_enabled.module_id=:module_id
		AND (daportal_user_group.group_id=:group_id
		OR daportal_user_enabled.group_id=:group_id)';
	//IN:	module_id
	//	group_id
	static protected $query_list_group_count = 'SELECT COUNT(*) AS count
		FROM daportal_content_enabled, daportal_user_enabled,
		daportal_user_group, daportal_group_enabled
		WHERE daportal_content_enabled.user_id
		=daportal_user_enabled.user_id
		AND daportal_user_enabled.user_id=daportal_user_group.user_id
		AND daportal_user_group.group_id=daportal_group_enabled.group_id
		AND daportal_content_enabled.module_id=:module_id
		AND (daportal_user_group.group_id=:group_id
		OR daportal_user_enabled.group_id=:group_id)';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content_enabled.enabled AS enabled, public,
		content
		FROM daportal_content_enabled, daportal_user_enabled
		WHERE daportal_content_enabled.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_enabled.module_id=:module_id
		AND daportal_content_enabled.user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_count = 'SELECT COUNT(*) AS count
		FROM daportal_content_enabled, daportal_user_enabled
		WHERE daportal_content_enabled.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_enabled.module_id=:module_id
		AND daportal_content_enabled.user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT content_id AS id, timestamp,
		daportal_module.module_id AS module_id, name AS module,
		daportal_user.user_id AS user_id, username,
		title, content, daportal_content.enabled AS enabled, public
		FROM daportal_content, daportal_module, daportal_user
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user.user_id
		AND daportal_content.enabled='1'
		AND (daportal_content.public='1'
			OR daportal_content.user_id=:user_id)
		AND content_id=:content_id";
	//IN:	module_id
	//	content_id
	//	title
	//	content
	//	enabled
	//	public
	protected $query_update = 'UPDATE daportal_content
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
	protected $query_update_timestamp = 'UPDATE daportal_content
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


	//Content::getModule
	protected function getModule()
	{
		return $this->module;
	}


	//Content::setID
	protected function setID($id)
	{
		$this->id = $id;
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
