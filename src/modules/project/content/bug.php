<?php //$Id$
//Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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



//BugProjectContent
class BugProjectContent extends MultiContent
{
	//public
	//methods
	//essential
	//BugProjectContent::BugProjectContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields['project_id'] = 'project ID';
		$this->fields['bug_id'] = 'bug #';
		$this->fields['state'] = 'state';
		$this->fields['type'] = 'type';
		$this->fields['priority'] = 'priority';
		$this->fields['assigned'] = 'assigned';
		if($properties === FALSE)
			$properties = array();
		if(!isset($properties['state']))
			$properties['state'] = 'New';
		parent::__construct($engine, $module, $properties);
		if(($project_id = $this->get('project_id')) !== FALSE)
			$this->project = ProjectContent::load($engine,
					$module, $project_id);
		//translations
		$this->text_content_by = _('Bug report from');
		$this->text_content_list_title = _('Bug reports');
		$this->text_more_content = _('More reports...');
		$this->text_submit = _('Report bug...');
		self::$priorities['Urgent'] = _('Urgent');
		self::$priorities['High'] = _('High');
		self::$priorities['Medium'] = _('Medium');
		self::$priorities['Low'] = _('Low');
		self::$states['Assigned'] = _('Assigned');
		self::$states['Closed'] = _('Closed');
		self::$states['Fixed'] = _('Fixed');
		self::$states['Implemented'] = _('Implemented');
		self::$states['New'] = _('New');
		self::$states['Re-opened'] = _('Re-opened');
		self::$types['Major'] = _('Major');
		self::$types['Minor'] = _('Minor');
		self::$types['Functionality'] = _('Functionality');
		self::$types['Feature'] = _('Feature');
	}


	//accessors
	//BugProjectContent::getBugReplies
	public function getBugReplies($engine)
	{
		return BugReplyProjectContent::listByBugID($engine,
				$this->getModule(), $this->get('bug_id'));
	}


	//BugProjectContent::getRequest
	public function getRequest($action = FALSE, $parameters = FALSE)
	{
		return new Request($this->getModule()->getName(), $action,
			$this->getID(), parent::getTitle(), $parameters);
	}


	//BugProjectContent::getTitle
	public function getTitle()
	{
		$title = ($this->project !== FALSE)
			? $this->project->getTitle().'/' : '';
		return $title.'#'.$this->get('bug_id').': '.parent::getTitle();
	}


	//BugProjectContent::set
	public function set($name, $value)
	{
		switch($name)
		{
			case 'priority':
				if(!array_key_exists($value, self::$priorities))
					return FALSE;
				break;
			case 'state':
				if(!array_key_exists($value, self::$states))
					return FALSE;
				break;
			case 'bug_type':
				//XXX workaround for the MultiContent class
				$name = 'type';
			case 'type':
				if(!array_key_exists($value, self::$types))
					return FALSE;
				break;
		}
		return parent::set($name, $value);
	}


	//useful
	//BugProjectContent::display
	public function display($engine, $request)
	{
		$ret = parent::display($engine, $request);
		//FIXME list the replies above the buttons
		$ret->append($this->displayReplies($engine, $request));
		return $ret;
	}


	//BugProjectContent::displayContent
	public function displayContent($engine, $request)
	{
		$text = HTML::format($engine, $this->getContent($engine));
		return new PageElement('htmlview', array('text' => $text));
	}


	//BugProjectContent::displayReplies
	public function displayReplies($engine, $request)
	{
		$error = _('Could not list the replies');

		if(($replies = $this->getBugReplies($engine)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$columns = array('title' => _('Title'),
			'username' => _('Username'), 'date' => _('Date'),
			'preview' => _('Preview'));
		$view = new PageElement('treeview', array('view' => 'preview',
				'columns' => $columns));
		foreach($replies as $r)
			$view->append('row', array(
					'username' => $r->getUsername(),
					'date' => $r->getDate($engine),
					'preview' => $r->displayContent($engine,
							$request)));
		return $view;
	}


	//BugProjectContent::displayToolbar
	public function displayToolbar($engine, $request = FALSE)
	{
		if($this->project !== FALSE)
			return $this->project->displayToolbar($engine,
					$request);
		return FALSE;
	}


	//BugProjectContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->getParameter('title')));
		$vbox->append('textview', array('name' => 'content',
				'text' => _('Description: '),
				'value' => $request->getParameter('content')));
		//type
		$combobox = $vbox->append('combobox', array('name' => 'bug_type',
				'text' => _('Type: '),
				'value' => $request->get('type')));
		foreach(self::$types as $value => $text)
			$combobox->append('label', array('value' => $value,
				'text' => $text));
		//priority
		$combobox = $vbox->append('combobox', array('name' => 'priority',
				'text' => _('Priority: '),
				'value' => $request->get('priority')));
		foreach(self::$priorities as $value => $text)
			$combobox->append('label', array('value' => $value,
				'text' => $text));
		return $vbox;
	}


	//BugProjectContent::loadFromBugID
	static public function loadFromBugID($engine, $module, $bug_id)
	{
		//XXX code duplication
		$database = $engine->getDatabase();
		$query = static::$query_load_by_bug_id;
		$args = array('module_id' => $module->getID(),
			'bug_id' => $bug_id);
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
			$query .= ' AND title '.$database->like(FALSE)
				.' :title ESCAPE :escape';
			$args['title'] = str_replace($from, $to, $title);
			$args['escape'] = '\\';
		}
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| $res->count() != 1)
			return FALSE;
		return static::loadFromResult($engine, $module, $res);
	}


	//protected
	//properties
	protected $project = FALSE;
	//static
	static protected $class = 'BugProjectContent';
	static protected $list_order = 'bug_id DESC';
	static protected $priorities = array('Urgent' => 'Urgent',
		'High' => 'High', 'Medium' => 'Medium', 'Low' => 'Low');
	static protected $states = array('New' => 'New',
		'Assigned' => 'Assigned', 'Closed' => 'Closed',
		'Fixed' => 'Fixed', 'Implemented' => 'Implemented',
		'Re-opened' => 'Re-opened');
	static protected $types = array('Major' => 'Major', 'Minor' => 'Minor',
		'Functionality' => 'Functionality', 'Feature' => 'Feature');
	//queries
	//IN:	module_id
	static protected $query_list = "SELECT bug.content_id AS id,
		bug.timestamp AS timestamp, bug.module_id AS module_id, module,
		bug.user_id AS user_id, bug.username AS username,
		bug.group_id AS group_id, bug.groupname AS groupname,
		bug.title AS title, bug.content AS content,
		bug.enabled AS enabled, bug.public AS public,
		bug_id, state, type, priority,
		daportal_project.project_id AS project_id,
		project.title AS project
		FROM daportal_content_public bug, daportal_bug,
		daportal_content_public project, daportal_project
		WHERE bug.content_id=daportal_bug.content_id
		AND daportal_bug.project_id=daportal_project.project_id
		AND project.content_id=daportal_project.project_id";
	//IN:	module_id
	//	user_id
	static protected $bug_query_list_user = "SELECT bug_id,
		bug.content_id AS id, bug.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		bug.title AS title, bug.enabled AS enabled, state, type,
		priority, daportal_project.project_id AS project_id,
		project.title AS project, daportal_bug.public AS public
		FROM daportal_content_public bug, daportal_module,
		daportal_user_enabled, daportal_bug,
		daportal_content_public project, daportal_project
		WHERE bug.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND bug.user_id=daportal_user_enabled.user_id
		AND bug.content_id=daportal_bug.content_id
		AND daportal_bug.project_id=daportal_project.project_id
		AND project.content_id=daportal_project.project_id
		AND daportal_content_public.user_id=:user_id";
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT
		daportal_content_enabled.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, bug_id, project_id, state,
		type, priority, assigned
		FROM daportal_content_enabled, daportal_bug
		WHERE daportal_content_enabled.content_id
		=daportal_bug.content_id
		AND module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND daportal_content_enabled.content_id=:content_id";
	//IN:	module_id
	//	bug_id
	static protected $query_load_by_bug_id = 'SELECT
		daportal_content_public.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, bug_id, project_id, state,
		type, priority, assigned
		FROM daportal_content_public, daportal_bug
		WHERE daportal_content_public.content_id=daportal_bug.content_id
		AND module_id=:module_id
		AND bug_id=:bug_id';
}

?>
