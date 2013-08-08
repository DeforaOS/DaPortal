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


	//static
	//methods
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
}

?>