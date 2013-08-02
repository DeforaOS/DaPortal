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
//FIXME:
//- also define the forms here
class Content
{
	//public
	//methods
	//essential
	//Content::Content
	public function __construct($engine, $module, $properties)
	{
		$database = $engine->getDatabase();

		$this->module_id = $module->getID();
		$this->module = $module->getName();
		foreach($properties as $k => $v)
			switch($k)
			{
				case 'enabled':
				case 'public':
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
	}


	//accessors
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
	public function getRequest()
	{
		return new Request($this->module, FALSE, $this->getID(),
				$this->getTitle());
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
		$text = $this->getContent();

		return new PageElement('label', array('text' => $text));
	}


	//Content::preview
	public function preview($engine, $request = FALSE)
	{
		$vbox = new PageElement('vbox');
		$text = strlen($this->content) < 100 ? $this->content
			: substr($this->content, 0, 100).'...';

		$vbox->append('label', array('text' => $text));
		return $vbox;
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
		$args = array('module_id' => $this->module_id,
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
		$args = array('module_id' => $this->module_id,
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
			$offset = FALSE)
	{
		$class = get_class();
		return $class::_listAll($engine, $module, $limit, $offset,
			$class);
	}

	static protected function _listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $class)
	{
		$ret = array();
		$vbox = new PageElement('vbox');
		$database = $engine->getDatabase();
		$query = $class::$query_list;
		$args = array('module_id' => $module->getID());

		if($limit !== FALSE || $offset !== FALSE)
			$query .= $database->offset($limit, $offset);
		if(($res = $database->query($engine, $query, $args)) === FALSE)
			return;
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
	//translations
	static protected $text_content_by = 'Content by';
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


	//private
	//properties
	private $id = FALSE;
	private $timestamp = FALSE;
	private $module_id = FALSE;
	private $module = FALSE;
	private $user_id = FALSE;
	private $username = FALSE;
	private $group_id = FALSE;
	private $group = FALSE;
	private $title = FALSE;
	private $content = FALSE;
	private $enabled = FALSE;
	private $public = FALSE;
}

?>
