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
require_once('./modules/download/content.php');
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
					return DownloadContent::getRoot(
							$this->name);
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
				$action = 'call'.$action;
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
		//FIXME obtain the parent
		$parent = NULL;
		//FIXME should return $id as $content['download_id']
		$error = $this->_submitProcessFile($engine, $request, $parent,
				$filename, $request->getTitle(), $content, $id,
				TRUE);
		if($error !== FALSE)
			//XXX report the error to the user instead
			return $engine->log('LOG_ERR', $error);
		return $content;
	}


	//protected
	//properties
	protected $S_IFDIR = 512;

	//translations
	protected $file_text_content_list_title = 'File list';
	protected $file_text_content_list_title_by = 'Files from';
	protected $folder_text_content_list_title = 'Folder list';
	protected $folder_text_content_list_title_by = 'Folders from';


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


	//accessors
	//DownloadModule::canUpload
	protected function canUpload($engine, $request = FALSE,
			$content = FALSE, &$error = FALSE)
	{
		return $this->canUpdate($engine, $request, $content, $error);
	}


	//DownloadModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
	{
		parent::setContext($engine, $request, $content);
		switch($this->content_class)
		{
			case 'FileDownloadContent':
				$this->stock_content_submit = 'upload';
				$this->text_content_list_title
					= $this->file_text_content_list_title;
				$this->text_content_list_title_by
					= $this->file_text_content_list_title_by;
				$this->text_content_submit = _('Upload');
				$this->text_content_submit_content
					= _('Upload file');
				break;
			case 'FolderDownloadContent':
				$this->stock_content_submit = 'folder-new';
				$this->text_content_list_title
					= $this->folder_text_content_list_title;
				$this->text_content_list_title_by
					= $this->folder_text_content_list_title_by;
				$this->text_content_submit = _('Create');
				$this->text_content_submit_content
					= _('New folder');
				break;
		}
	}


	//useful
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


	//DownloadModule::callDownload
	protected function callDownload($engine, $request)
	{
		global $config;
		$error = _('Could not fetch content');

		if(($id = $request->getID()) === FALSE)
			return $this->callDefault($engine);
		if(($content = $this->_get($engine, $id, $request->getTitle()))
				=== FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		if($content instanceof FileDownloadContent)
			return $content->download($engine, $request);
		return new PageElement('dialog', array('type' => 'error',
			'text' => $error));
	}


	//DownloadModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		return parent::callSubmit($engine, $request);
	}

	protected function _submitProcess($engine, $request, $content)
	{
		//verify the request
		if($request === FALSE
				|| $request->getParameter('_submit') === FALSE)
			return TRUE;
		if($request->isIdempotent() !== FALSE)
			return _('The request expired or is invalid');
		//FIXME obtain the parent
		$parent = NULL;
		//check known errors
		if(!isset($_FILES['files']))
			return TRUE;
		foreach($_FILES['files']['error'] as $k => $v)
			if($v != UPLOAD_ERR_OK)
				return _('An error occurred');
		//store each file uploaded
		foreach($_FILES['files']['error'] as $k => $v)
			$this->_submitProcessFile($engine, $request, $parent,
					$_FILES['files']['tmp_name'][$k],
					$_FILES['files']['name'][$k], $content,
					$id);
		return FALSE;
	}

	protected function _submitProcessFile($engine, $request, $parent,
			$pathname, $filename, &$content, &$id)
	{
		if($filename === FALSE)
			$filename = basename($pathname);
		//FIXME check for filename unicity in the current folder
		$content = new FileDownloadContent($engine, $this, array(
			'title' => $filename, 'content' => FALSE,
			'filename' => $pathname,
			'parent_id' => $parent, 'mode' => 0644));
		$error = _('Internal server error');
		if($content->save($engine, $request, $error) === FALSE)
			return $error;
		return FALSE;
	}
}

?>
