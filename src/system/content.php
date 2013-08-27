<?php //$Id$
//Copyright (c) 2012-2013 Pierre Pronchery <khorben@defora.org>
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

		if($request === FALSE)
			return TRUE;
		$error = _('The request expired or is invalid');
		if($request->isIdempotent())
			return FALSE;
		//verify that the fields are set
		$error = '';
		foreach($this->fields as $k => $v)
			if($this->get($k) !== FALSE)
				continue;
			else if($request->getParameter($k) === FALSE)
				$error .= "$v must be set\n";
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


	//Content::get
	public function get($property)
	{
		if(!isset($this->properties[$property]))
			return FALSE;
		return $this->properties[$property];
	}


	//Content::getContent
	public function getContent($engine)
	{
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

		return $database->query($engine, $query, $args);
	}


	//Content::display
	public function display($engine, $request)
	{
		$page = new Page(array('title' => $this->getTitle()));
		$page->append($this->displayTitle($engine, $request));
		$vbox = $page->append('vbox');
		$vbox->append($this->displayToolbar($engine, $request));
		$vbox->append($this->displayMetadata($engine, $request));
		$vbox->append($this->displayContent($engine, $request));
		$vbox->append($this->displayButtons($engine, $request));
		return $page;
	}


	//Content::displayButtons
	public function displayButtons($engine, $request)
	{
		$hbox = new PageElement('hbox');
		$r = new Request($this->module->getName());
		$hbox->append('link', array('stock' => 'back',
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


	//Content::displayToolbar
	public function displayToolbar($engine, $request)
	{
		$credentials = $engine->getCredentials();
		$module = $this->module->getName();

		$toolbar = new PageElement('toolbar');
		if($credentials->isAdmin($engine))
		{
			$r = new Request($module, 'admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		if($this->module->canSubmit($engine))
		{
			$r = new Request($module, 'submit');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'new',
					'text' => $this->text_submit_content));
		}
		if($this->getID() !== FALSE)
		{
			if(!$this->isPublic() && $this->canPublish($engine))
			{
				$r = $this->getRequest('publish');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'publish',
						'text' => $this->text_publish));
			}
			if($this->canUpdate($engine))
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
				'value' => $request->getParameter('title')));
		$vbox->append('textview', array('name' => 'content',
				'text' => _('Content: '),
				'value' => $request->getParameter('content')));
		return $vbox;
	}

	protected function _formUpdate($engine, $request)
	{
		$vbox = new PageElement('vbox');
		if(($value = $request->getParameter('title')) === FALSE)
			$value = $this->getTitle();
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $value));
		$label = $vbox->append('label', array(
				'text' => _('Content: ')));
		if(($value = $request->getParameter('content')) === FALSE)
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
			if(($p = $request->getParameter($k)) === FALSE)
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
		$length = $this->preview_length;

		$text = ($length <= 0 || strlen($this->content) < $length)
			? $this->content
			: substr($this->content, 0, $length).'...';

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
		foreach($this->fields as $f)
			$this->set($f, $request->getParameter($f));
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
		$error = _('Could not insert the content');
		if($database->query($engine, $query, $args) === FALSE)
			return FALSE;
		$this->id = $database->getLastID($engine, 'daportal_content',
				'content_id');
		return ($this->id !== FALSE);
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
					if(($v = $request->getParameter($k))
							=== FALSE)
						break;
					$args[$k] = $v;
					break;
			}
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
	//Content::count
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

		if($user !== FALSE)
		{
			$query = $class::$query_list_user_count;
			$args['user_id'] = $user->getUserID();
		}
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return $res[0][0];
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

		if($user !== FALSE)
		{
			$query = $class::$query_list_user;
			$args['user_id'] = $user->getUserID();
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

		if(is_string($title))
		{
			$query .= ' AND title '.$database->like(FALSE)
				.' :title';
			$args['title'] = str_replace('-', '_', $title);
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
	protected $stock_link = 'link';
	protected $stock_open = 'open';
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
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content_public.enabled AS enabled, public,
		content
		FROM daportal_content_public, daportal_user_enabled
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id';
	//IN:	module_id
	static protected $query_list_count = 'SELECT COUNT(*)
		FROM daportal_content_public
		WHERE daportal_content_public.module_id=:module_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		title, daportal_content.enabled AS enabled, public, content
		FROM daportal_content, daportal_user_enabled
		WHERE daportal_content.module_id=:module_id
		AND daportal_content.user_id
		=daportal_user_enabled.user_id
		AND daportal_content.user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_count = 'SELECT COUNT(*)
		FROM daportal_content
		WHERE daportal_content.module_id=:module_id
		AND user_id=:user_id';
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
	private $module = FALSE;
	private $user_id = FALSE;
	private $username = FALSE;
	private $group_id = FALSE;
	private $group = FALSE;
	private $title = FALSE;
	private $content = FALSE;
	private $enabled = TRUE;
	private $public = FALSE;
	private $properties = array();
}


//MultiContent
class MultiContent extends Content
{
	//public
	//methods
	//useful
	//MultiContent::displayToolbar
	public function displayToolbar($engine, $request)
	{
		$credentials = $engine->getCredentials();
		$module = $this->getModule()->getName();

		if($this->type === FALSE)
			return parent::displayToolbar($engine, $request);
		//FIXME code duplication
		$toolbar = new PageElement('toolbar');
		if($credentials->isAdmin($engine))
		{
			$r = new Request($module, 'admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		if($this->getModule()->canSubmit($engine))
		{
			$r = new Request($module, 'submit', FALSE, FALSE,
				array('type' => $this->type));
			$toolbar->append('button', array('request' => $r,
					'stock' => 'new',
					'text' => $this->text_submit_content));
		}
		if($this->getID() !== FALSE)
		{
			if(!$this->isPublic() && $this->canPublish($engine))
			{
				$r = $this->getRequest('publish');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'publish',
						'text' => $this->text_publish));
			}
			if($this->canUpdate($engine))
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


	//MultiContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		$database = $engine->getDatabase();

		if($database->transactionBegin($engine) === FALSE)
			return FALSE;
		if(($ret = parent::save($engine, $request, $error)) === FALSE)
			$database->transactionRollback($engine);
		else if($database->transactionCommit($engine) === FALSE)
			return FALSE;
		return $ret;
	}


	//protected
	//methods
	//accessors
	//MultiContent::getType
	protected function getType()
	{
		return $this->type;
	}


	//MultiContent::setType
	protected function setType($type)
	{
		$this->type = $type;
	}


	//private
	//properties
	private $type = FALSE;
}

?>
