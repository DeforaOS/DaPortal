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



//ScreenshotProjectContent
class ScreenshotProjectContent extends DownloadProjectContent
{
	//public
	//methods
	//essential
	//ScreenshotProjectContent::ScreenshotProjectContent
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		//translations
		$this->text_content_by = _('Screenshot from');
		$this->text_content_list_title = _('Project screenshot list');
		$this->text_more_content = _('More screenshots...');
		$this->text_submit = _('Upload');
		$this->text_submit_content = _('New screenshot');
	}


	//ScreenshotProjectContent::displayRow
	public function displayRow(Engine $engine, $request = FALSE)
	{
		$module = Module::load($engine, 'download');

		$row = parent::displayRow($engine, $request);
		if(($download = FileDownloadContent::load($engine, $module,
				$this->get('download_id'))) === FALSE)
			//XXX ignore error
			return $row;
		$r = $download->getRequest('download');
		$thumbnail = new PageElement('image', array('request' => $r));
		$row->set('thumbnail', $thumbnail);
		$row->set('label', $row->get('title'));
		return $row;
	}


	//protected
	//properties
	static protected $class = 'ScreenshotProjectContent';
	static protected $load_title = 'download.title';
	//queries
	//IN:	module_id
	static protected $query_list = 'SELECT project_screenshot_id,
		download.content_id AS id, download.timestamp AS timestamp,
		project.module_id AS module_id, project.module AS module,
		download.user_id AS user_id, download.username AS username,
		download.group_id AS group_id, download.groupname AS groupname,
		download.title AS title, download.content AS content,
		download.enabled AS enabled, download.public AS public,
		project_id, download_id
		FROM daportal_project_screenshot, daportal_content_public project,
		daportal_content_public download
		WHERE daportal_project_screenshot.project_id=project.content_id
		AND daportal_project_screenshot.download_id=download.content_id
		AND project.module_id=:module_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT project_screenshot_id,
		download.content_id AS id, download.timestamp AS timestamp,
		project.module_id AS module_id, project.module AS module,
		download.user_id AS user_id, download.username AS username,
		download.group_id AS group_id, download.groupname AS groupname,
		download.title AS title, download.content AS content,
		download.enabled AS enabled, download.public AS public,
		project_id, download_id
		FROM daportal_project_screenshot,
		daportal_content_enabled download,
		daportal_content_enabled project
		WHERE daportal_project_screenshot.download_id=download.content_id
		AND daportal_project_screenshot.project_id=project.content_id
		AND project.module_id=:module_id
		AND (download.public='1' OR download.user_id=:user_id)
		AND (project.public='1' OR project.user_id=:user_id)
		AND download.content_id=:content_id";
}

?>
