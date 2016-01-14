<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
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



//DownloadProjectContent
class DownloadProjectContent extends ContentMulti
{
	//public
	//methods
	//essential
	//DownloadProjectContent::DownloadProjectContent
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		$this->fields['download_id'] = 'Download';
		$this->fields['project_id'] = 'Project';
		parent::__construct($engine, $module, $properties);
		//translations
		$this->text_content_by = _('Download from');
		$this->text_content_list_title = _('Project download list');
		$this->text_more_content = _('More downloads...');
		$this->text_submit = _('Upload');
		$this->text_submit_content = _('New download');
	}


	//accessors
	//DownloadProjectContent::getColumns
	static public function getColumns()
	{
		return array('icon' => '', 'title' => _('Filename'),
			'project' => _('Project'), 'username' => _('Owner'),
			'date' => _('Date'));
	}


	//useful
	//DownloadProjectContent::displayContent
	public function displayContent(Engine $engine, $request)
	{
		$module = Module::load($engine, 'download');

		if(($download = FileDownloadContent::load($engine, $module,
				$this->get('download_id'))) === FALSE)
			//XXX ignore error
			return parent::displayContent($engine, $request);
		//FIXME convert $request?
		return $download->displayContent($engine, $request);
	}


	//DownloadProjectContent::displayRow
	public function displayRow(Engine $engine, $request = FALSE)
	{
		$row = parent::displayRow($engine, $request);
		$project = ProjectContent::load($engine, $this->getModule(),
			$this->get('project_id'));

		//icon
		$type = Mime::getType($engine, $this->getTitle());
		$icon = Mime::getIconByType($engine, $type, 16);
		$icon = new PageElement('image', array('source' => $icon,
				'title' => $type));
		$row->set('icon', $icon);
		//project
		if($project !== FALSE)
			$row->set('project', new PageElement('link', array(
				'stock' => $this->getModule()->getName(),
				'text' => $project->getTitle(),
				'request' => $project->getRequest())));
		return $row;
	}


	//DownloadProjectContent::listByProject
	static public function listByProject(Engine $engine, Module $module,
			$project, $order = FALSE, $limit = FALSE,
			$offset = FALSE)
	{
		$query = static::$query_list
			.' AND project.content_id=:project_id';
		$args = array('module_id' => $module->getID(),
			'project_id' => $project->getID());

		if(($res = static::query($engine, $query, $args, $order, $limit,
				$offset)) === FALSE)
			return FALSE;
		return new ContentResult($engine, $module, static::$class,
			$res);
	}


	//protected
	//properties
	static protected $class = 'DownloadProjectContent';
	static protected $load_title = 'download.title';
	//queries
	//IN:	module_id
	static protected $query_list = 'SELECT project_download_id,
		download.content_id AS id, download.timestamp AS timestamp,
		project.module_id AS module_id, project.module AS module,
		download.user_id AS user_id, download.username AS username,
		download.group_id AS group_id, download.groupname AS groupname,
		download.title AS title, download.content AS content,
		download.enabled AS enabled, download.public AS public,
		project_id, download_id
		FROM daportal_project_download, daportal_content_public project,
		daportal_content_public download
		WHERE daportal_project_download.project_id=project.content_id
		AND daportal_project_download.download_id=download.content_id
		AND project.module_id=:module_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT project_download_id,
		download.content_id AS id, download.timestamp AS timestamp,
		project.module_id AS module_id, project.module AS module,
		download.user_id AS user_id, download.username AS username,
		download.group_id AS group_id, download.groupname AS groupname,
		download.title AS title, download.content AS content,
		download.enabled AS enabled, download.public AS public,
		project_id, download_id
		FROM daportal_project_download,
		daportal_content_enabled download,
		daportal_content_enabled project
		WHERE daportal_project_download.download_id=download.content_id
		AND daportal_project_download.project_id=project.content_id
		AND project.module_id=:module_id
		AND (download.public='1' OR download.user_id=:user_id)
		AND (project.public='1' OR project.user_id=:user_id)
		AND download.content_id=:content_id";
}

?>
