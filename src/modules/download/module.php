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
//FIXME:
//- properly implement file deletion



require_once('./system/common.php');
require_once('./system/mime.php');
require_once('./modules/content/multi.php');
require_once('./modules/download/content/file.php');
require_once('./modules/download/content/folder.php');


//DownloadModule
class DownloadModule extends MultiContentModule
{
	//public
	//methods
	//useful
	//DownloadModule::call
	public function call($engine, $request, $internal = 0)
	{
		$action = $request->getAction();
		if($internal)
			switch($action)
			{
				case 'get':
					return $this->_get($engine,
							$request->getID(),
							$request->getTitle());
				case 'getRoot':
					return $this->getRoot($engine);
				case 'submit':
					return $this->_callInternalSubmit(
							$engine, $request);
				default:
					return FALSE;
			}
		switch($action)
		{
			case 'download':
			case 'submit':
				$action = 'call'.ucfirst($action);
				return $this->$action($engine, $request);
			case 'folder_new':
				return $this->callFolderNew($engine, $request);
			case 'file_insert':
				return $this->callSubmit($engine, $request);
		}
		return parent::call($engine, $request, $internal);
	}

	protected function _callInternalSubmit($engine, $request)
	{
		//XXX saves files in the root folder
		if(($filename = $request->getParameter('filename')) === FALSE)
			return FALSE;
		$content = FALSE;
		//FIXME should return $id as $content['download_id']
		$error = $this->_submitProcessFile($engine, NULL, $filename,
				$content, $id, TRUE);
		if($error === FALSE)
			return $content;
		return FALSE;
	}


	//protected
	//properties
	protected $S_IFDIR = 512;

	//translations
	protected $file_text_content_list_title = 'File list';
	protected $folder_text_content_list_title = 'Folder list';

	//queries
	protected $download_query_directory_insert =
		'INSERT INTO daportal_download
		(content_id, parent, mode) VALUES (:content_id, :parent, 512)';
	protected $download_query_get = "SELECT daportal_module.name AS module,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username,
		daportal_group.group_id AS group_id,
		daportal_group.groupname AS groupname,
		daportal_content.content_id AS id,
		daportal_content.title AS title,
		daportal_content.content AS content,
		daportal_content.timestamp AS timestamp,
		daportal_content.enabled AS enabled,
		daportal_content.public AS public,
		download.download_id AS download_id,
		parent_download.content_id AS parent_id,
		parent_content.title AS parent_title,
		download.mode AS mode
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_group, daportal_download download
		LEFT JOIN daportal_download parent_download
		ON download.parent=parent_download.download_id
		LEFT JOIN daportal_content parent_content
		ON parent_download.content_id=parent_content.content_id
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id
		AND daportal_content.content_id=download.content_id
		AND daportal_content.enabled='1'
		AND (daportal_content.public='1'
			OR daportal_content.user_id=:user_id)
		AND daportal_content.content_id=:content_id";
	protected $download_query_file_insert = 'INSERT INTO daportal_download
		(content_id, parent, mode) VALUES (:content_id, :parent,
			:mode)';
	protected $download_query_list = 'SELECT
		daportal_content_public.content_id AS id,
		daportal_content_public.enabled AS enabled,
		daportal_content_public.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname, title, mode
		FROM daportal_content_public, daportal_user_enabled,
		daportal_group, daportal_download
		WHERE daportal_content_public.module_id=:module_id
		AND daportal_content_public.user_id=daportal_user_enabled.user_id
		AND daportal_content_public.group_id=daportal_group.group_id
		AND daportal_content_public.content_id=daportal_download.content_id';
	protected $download_query_list_admin = "SELECT
		daportal_content.content_id AS id,
		daportal_content.enabled AS enabled,
		daportal_content.public AS public,
		daportal_content.timestamp AS timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname, title, mode
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_group, daportal_download
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id
		AND daportal_content.content_id=daportal_download.content_id
		AND daportal_content.enabled='1'";
	protected $download_query_list_admin_count = "SELECT COUNT(*)
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_group, daportal_download
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id
		AND daportal_content.content_id=daportal_download.content_id
		AND daportal_content.enabled='1'";
	protected $download_query_list_files = "SELECT
		daportal_content.content_id AS id,
		daportal_content.enabled AS enabled,
		timestamp, name AS module,
		daportal_user_enabled.user_id AS user_id, username, title
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_download
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.content_id=daportal_download.content_id
		AND daportal_content.enabled='1'
		AND daportal_content.public='1'
		AND mode & 512 = 0";
	protected $download_query_list_files_count = "SELECT COUNT(*)
		FROM daportal_content, daportal_module, daportal_user_enabled,
		daportal_download
		WHERE daportal_content.module_id=daportal_module.module_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.content_id=daportal_download.content_id
		AND daportal_content.enabled='1'
		AND daportal_content.public='1'
		AND mode & 512 = 0";


	//methods
	//essential
	//DownloadModule::DownloadModule
	protected function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Downloads') : $title;
		$this->content_classes = array(
			'folder' => 'FolderDownloadContent',
			'file' => 'FileDownloadContent');
		parent::__construct($id, $name, $title);
		$this->text_content_title = _('Downloads');
	}


	//DownloadModule::canUpload
	protected function canUpload($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		return $this->canUpdate($engine, $request, $content, $error);
	}


	//DownloadModule::getRoot
	protected function getRoot($engine)
	{
		global $config;
		$error = 'The download repository is not configured';

		if(($root = $config->get('module::'.$this->name, 'root'))
				=== FALSE)
		{
			$engine->log('LOG_WARNING', $error);
			$root = '/tmp';
		}
		return $root;
	}


	//DownloadModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
	{
		parent::setContext($engine, $request, $content);
		switch($this->content_class)
		{
			case 'FileDownloadContent':
				$this->text_content_list_title = $this->file_text_content_list_title;
				break;
			case 'FolderDownloadContent':
				$this->text_content_list_title = $this->folder_text_content_list_title;
				break;
		}
	}


	//DownloadModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$class = 'FolderDownloadContent';
		$p = ($request !== FALSE) ? $request->getParameter('page') : 0;
		$pcnt = FALSE;

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$root = new $class($engine, $this);
		return $root->display($engine, $request);
	}
}

?>
