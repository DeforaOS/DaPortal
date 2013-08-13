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
		$database = $engine->getDatabase();

		$this->class = get_class();
		$this->stock = $module->getName();
		$this->module = $module;
		if($properties === FALSE)
			$properties = array();
		foreach($properties as $k => $v)
			switch($k)
			{
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
			}
		$this->properties = $properties;
		$this->text_content_by = _('Content by');
		$this->text_link = _('Permalink');
		$this->text_more_content = _('More content...');
		$this->text_on = _('on');
		$this->text_open = _('Open');
		$this->text_post = _('Publish');
		$this->text_submit = _('Submit content');
		$this->text_update = _('Update');
	}


	//accessors
	//Content::canAdmin
	public function canAdmin($engine, $request = FALSE, &$error = FALSE)
	{
		global $config;
		$credentials = $engine->getCredentials();

		//FIXME also verify that the fields are set (if not idempotent)
		if($credentials->isAdmin())
			return TRUE;
		$error = _('Permission denied');
		return FALSE;
	}


	//Content::canPost
	public function canPost($engine, $request = FALSE, &$error = FALSE)
	{
		global $config;
		$credentials = $engine->getCredentials();

		$error = _('Permission denied');
		if($credentials->getUserID() == 0)
			return FALSE;
		if($credentials->isAdmin())
			return TRUE;
		$moderate = $config->get('module::'.$this->module->getName(),
				'moderate');
		return ($moderate === FALSE || $moderate == 0) ? TRUE : FALSE;
	}


	//Content::canPreview
	public function canPreview($engine, $request = FALSE, &$error = FALSE)
	{
		return TRUE;
	}


	//Content::canSubmit
	public function canSubmit($engine, $request = FALSE, &$error = FALSE)
	{
		global $config;
		$credentials = $engine->getCredentials();

		//FIXME also verify that the fields are set (if not idempotent)
		if($credentials->getUserID() > 0)
			return TRUE;
		if($config->get('module::'.$this->module->getName(),
				'anonymous'))
			return TRUE;
		$error = _('Permission denied');
		return FALSE;
	}


	//Content::canUnpost
	public function canUnpost($engine, $request = FALSE, &$error = FALSE)
	{
		global $config;
		$credentials = $engine->getCredentials();

		if($credentials->isAdmin())
			return TRUE;
		$error = _('Permission denied');
		if($credentials->getUserID() == 0)
			return FALSE;
		return FALSE;
	}


	//Content::canUpdate
	public function canUpdate($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		if($credentials->isAdmin())
			return TRUE;
		//FIXME really implement
		$error = _('Permission denied');
		return FALSE;
	}


	//Content::get
	public function get($property)
	{
		if(!is_array($this->properties)
				|| !isset($this->properties[$property]))
			return FALSE;
		return $this->properties[$property];
	}


	//Content::getContent
	public function getContent()
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
		if(!is_array($this->properties))
			$this->properties = array($property => $value);
		else
			$this->properties[$property] = $value;
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
		$text = $this->getContent();

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
		if($this->canSubmit($engine, $request))
		{
			$r = new Request($module, 'submit');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'new',
					'text' => $this->text_submit));
		}
		if($this->getID() !== FALSE)
		{
			if(!$this->isPublic() && $this->canPost($engine,
					$request))
			{
				$r = new Request($module, 'publish',
					$this->getID(), $this->getTitle());
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'post',
						'text' => $this->text_post));
			}
			if($this->canUpdate($engine, $request))
			{
				$r = new Request($module, 'update',
					$this->getID(), $this->getTitle());
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
		return new PageElement('textview', array('name' => 'content',
				'text' => _('Content: '),
				'value' => $request->getParameter('content')));
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
			$value = $this->getContent();
		$label->append('textview', array('name' => 'content',
				'value' => $value));
		return $vbox;
	}


	//Content::formPreview
	public function formPreview($engine, $request)
	{
		$class = $this->class;
		$properties = $this->properties;

		foreach($this->fields as $f)
		{
			if(($p = $request->getParameter($f)) !== FALSE)
				$properties[$f] = $p;
			if($f == 'title')
				$properties[$f] = _('Preview: ')
					.$properties[$f];
		}
		$content = new $class($engine, $this->module, $properties);
		return $content->display($engine, $request);
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
	public function save($engine)
	{
		return ($this->id !== FALSE) ? $this->_saveUpdate($engine)
			: $this->_saveInsert($engine);
	}

	protected function _saveInsert($engine)
	{
		$credentials = $engine->getCredentials();
		$database = $engine->getDatabase();
		$query = $this->query_insert;
		$args = array('module_id' => $this->module->getID(),
			'user_id' => $credentials->getUserID(),
			'title' => $this->title,
			'content' => $this->content,
			'enabled' => $this->enabled,
			'public' => $this->public);

		//FIXME detect errors!@#$%
		if($database->query($engine, $query, $args) === FALSE)
			return FALSE;
		$this->id = $database->getLastID($engine, 'daportal_content',
				'content_id');
		return ($this->id !== FALSE) ? TRUE : FALSE;
	}

	protected function _saveUpdate($engine)
	{
		$database = $engine->getDatabase();
		$query = $this->query_update;
		$args = array('module_id' => $this->module->getID(),
			'content_id' => $this->id,
			'title' => $this->title,
			'content' => $this->content,
			'enabled' => $this->enabled,
			'public' => $this->public);

		//FIXME detect errors!@#$%
		return $database->query($engine, $query, $args);
	}


	//public static
	//Content::count
	static public function countAll($engine, $module)
	{
		$class = get_class();
		return $class::_countAll($engine, $module, $class);
	}

	static protected function _countAll($engine, $module, $class)
	{
		$database = $engine->getDatabase();
		$query = $class::$query_list_count;
		$args = array('module_id' => $module->getID());

		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return $res[0][0];
	}


	//Content::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $order = FALSE)
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
			$order, $class);
	}

	static protected function _listAll($engine, $module, $limit, $offset,
			$order, $class)
	{
		$ret = array();
		$vbox = new PageElement('vbox');
		$database = $engine->getDatabase();
		$query = $class::$query_list;
		$args = array('module_id' => $module->getID());

		if($order !== FALSE)
			$query .= ' ORDER BY '.$order;
		if($limit !== FALSE || $offset !== FALSE)
			$query .= $database->offset($limit, $offset);
		if(($res = $database->query($engine, $query, $args)) === FALSE)
			return FALSE;
		for($i = 0, $cnt = count($res); $i < $cnt; $i++)
			$ret[] = new $class($engine, $module, $res[$i]);
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
		$res = $res[0];
		return new $class($engine, $module, $res);
	}


	//protected
	//properties
	protected $class = FALSE;
	protected $fields = array('title', 'content');
	static protected $list_order = 'timestamp DESC';
	protected $preview_length = 150;
	//stock icons
	protected $stock = FALSE;
	protected $stock_link = 'link';
	protected $stock_open = 'open';
	//translations
	protected $text_content_by = 'Content by';
	protected $text_link = 'Permalink';
	protected $text_more_content = 'More content...';
	protected $text_on = 'on';
	protected $text_open = 'Open';
	protected $text_post = 'Publish';
	protected $text_submit = 'Submit content';
	protected $text_update = 'Update';
	//queries
	//IN:	content_id
	protected $query_delete = 'DELETE FROM daportal_content
		WHERE content_id=:content_id';
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
	protected function getModule()
	{
		return $this->module;
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
	private $enabled = FALSE;
	private $public = FALSE;
	private $properties = FALSE;
}

?>
