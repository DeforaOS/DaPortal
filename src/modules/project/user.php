<?php //$Id$
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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



//ProjectUser
class ProjectUser extends User
{
	//public
	//methods
	//accessors
	//ProjectUser::getProjects
	public function getProjects(Engine $engine, Module $module,
			$order = FALSE, $limit = FALSE, $offset = FALSE)
	{
		return ProjectContent::listAll($engine, $module, $order, $limit,
				$offset, $this);
	}


	//ProjectUser::isProjectAdmin
	public function isProjectAdmin(Engine $engine, ProjectContent $project)
	{
		if(($id = $project->getID()) === FALSE)
			return FALSE;
		if(isset($this->project_admin[$id]))
			return $this->project_admin[$id];
		//FIXME implement
		return FALSE;
	}


	//ProjectUser::isProjectMember
	public function isProjectMember(Engine $engine, ProjectContent $project)
	{
		//FIXME implement
		return FALSE;
	}


	//ProjectUser::setProjectAdmin
	public function setProjectAdmin(ProjectContent $project, $admin)
	{
		if(($id = $project->getID()) === FALSE)
			return FALSE;
		$this->project_admin[$id] = ($admin === TRUE) ? TRUE : FALSE;
		return TRUE;
	}


	//protected
	//properties
	protected $project_admin = array();
}

?>
