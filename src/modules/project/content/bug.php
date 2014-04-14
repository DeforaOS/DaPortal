<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/content.php');


//BugProjectContent
class BugProjectContent extends Content
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
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		//translations
		$this->text_content_by = _('Bug report from');
		$this->text_content_list_title = _('Bug reports');
		$this->text_more_content = _('More reports...');
		$this->text_submit = _('Report bug...');
	}


	//static
	//methods
	//BugProjectContent::countAll
	static public function countAll($engine, $module, $user = FALSE)
	{
		$class = get_class();
		$class::$query_list_count = $class::$bug_query_list_count;
		$class::$query_list_user_count
			= $class::$bug_query_list_user_count;

		return $class::_countAll($engine, $module, $user, $class);
	}


	//BugProjectContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $user = FALSE, $order = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'bug_id DESC';
				break;
		}
		$class::$query_list = $class::$bug_query_list;
		$class::$query_list_user = $class::$bug_query_list_user;
		return $class::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}


	//BugProjectContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		$class = get_class();

		$class::$query_load = $class::$bug_query_load;
		return $class::_load($engine, $module, $id, $title, $class);
	}


	//protected
	//queries
	//IN:	module_id
	static protected $bug_query_list = "SELECT bug_id,
		bug.content_id AS id, bug.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		bug.title AS title, bug.enabled AS enabled, state, type,
		priority, daportal_project.project_id AS project_id,
		project.title AS project, bug.public AS public
		FROM daportal_content_public bug, daportal_module,
		daportal_user_enabled, daportal_bug,
		daportal_content_public project, daportal_project
		WHERE bug.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND bug.user_id=daportal_user_enabled.user_id
		AND bug.content_id=daportal_bug.content_id
		AND daportal_bug.project_id=daportal_project.project_id
		AND project.content_id=daportal_project.project_id";
	//IN:	module_id
	static protected $bug_query_list_count = "SELECT COUNT(*) AS count
		FROM daportal_content_public bug, daportal_module,
		daportal_user_enabled, daportal_bug,
		daportal_content_public project, daportal_project
		WHERE bug.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND bug.user_id=daportal_user_enabled.user_id
		AND bug.content_id=daportal_bug.content_id
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
	static protected $bug_query_list_user_count = "SELECT COUNT(*) AS count
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
	static protected $bug_query_load = "SELECT daportal_bug.content_id AS id,
		title, content, timestamp,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username, bug_id, project_id,
		state, type, priority, assigned, public
		FROM daportal_content_enabled, daportal_bug,
		daportal_user_enabled
		WHERE daportal_content_enabled.content_id
		=daportal_bug.content_id
		AND daportal_content_enabled.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_enabled.module_id=:module_id
		AND (daportal_content_enabled.public='1'
		OR daportal_content_enabled.user_id=:user_id)
		AND daportal_content_enabled.content_id=:content_id";
}

?>
