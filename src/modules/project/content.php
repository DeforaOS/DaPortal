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


//ProjectContent
class ProjectContent extends Content
{
	//public
	//methods
	//essential
	//ProjectContent::ProjectContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields['synopsis'] = 'Synopsis';
		$this->fields['scm'] = 'SCM';
		$this->fields['cvsroot'] = 'CVS root';
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		$this->text_content_by = _('Project from');
		$this->text_content_list_title = _('Project list');
		$this->text_more_content = _('More projects...');
		$this->text_submit = _('New project...');
	}


	//accessors
	//ProjectContent::canUpload
	public function canUpload($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		//FIXME really implement
		return $credentials->isAdmin();
	}


	//useful
	//ProjectContent::displayContent
	public function displayContent($engine, $request)
	{
		$vbox = new PageElement('vbox');

		if(($text = $this->get('synopsis')) !== FALSE
				&& strlen($text))
			$vbox->append('label', array('class' => 'bold',
					'text' => $text));
		$vbox->append('label', array('text' => $this->getContent()));
		return $vbox;
	}


	//ProjectContent::save
	public function save($engine, $request, &$error)
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

	protected function _saveInsert($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = $this->project_query_insert;

		if(($ret = parent::_saveInsert($engine, $request, $error))
				=== FALSE)
			return FALSE;
		$error = _('Could not insert the project');
		$args = array('project_id' => $ret,
			'synopsis' => $this->get('synopsis'),
			'cvsroot' => $this->get('cvsroot'));
		if($database->query($engine, $query, $args)
				=== FALSE)
			return FALSE;
		return $ret;
	}

	protected function _saveUpdate($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = $this->project_query_update;

		if(($ret = parent::_saveUpdate($engine, $request, $error))
				=== FALSE)
			return FALSE;
		$error = _('Could not update the project');
		$args = array('project_id' => $this->id,
			'synopsis' => $this->get('synopsis'),
			'cvsroot' => $this->get('cvsroot'));
		if($database->query($engine, $query, $args)
				=== FALSE)
			return FALSE;
		return $ret;
	}


	//static
	//methods
	//ProjectContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $user = FALSE, $order = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'title ASC';
				break;
		}
		$class::$query_list = $class::$project_query_list;
		return $class::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}


	//ProjectContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		$class = get_class();

		$class::$query_load = $class::$project_query_load;
		return $class::_load($engine, $module, $id, $title, $class);
	}


	//protected
	//properties
	//queries
	//IN:	project_id
	//	synopsis
	//	cvsroot
	protected $project_query_insert = 'INSERT INTO
		daportal_project(project_id, synopsis, cvsroot)
		VALUES (:project_id, :synopsis, :cvsroot)';
	//IN:	module_id
	static protected $project_query_list = 'SELECT content_id AS id,
		daportal_content_public.enabled AS enabled, timestamp,
		name AS module, daportal_user_enabled.user_id AS user_id,
		username, title, synopsis, scm, cvsroot
		FROM daportal_content_public, daportal_module,
		daportal_user_enabled, daportal_project
		WHERE daportal_content_public.module_id
		=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content_public.user_id
		=daportal_user_enabled.user_id
		AND daportal_content_public.content_id
		=daportal_project.project_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $project_query_load = "SELECT project_id AS id,
		timestamp, title, daportal_module.module_id AS module_id,
		daportal_module.name AS module,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username, content, synopsis,
		scm, cvsroot, daportal_content.enabled AS enabled, public
		FROM daportal_content, daportal_module, daportal_project,
		daportal_user_enabled
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_module.module_id=:module_id
		AND daportal_content.content_id=daportal_project.project_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.enabled='1'
		AND (daportal_content.public='1'
		OR daportal_content.user_id=:user_id)
		AND project_id=:content_id";
	//IN:	project_id
	//	synopsis
	//	cvsroot
	protected $project_query_update = 'UPDATE daportal_project
		SET synopsis=:synopsis, cvsroot=:cvsroot
		WHERE project_id=:project_id';
}

?>
