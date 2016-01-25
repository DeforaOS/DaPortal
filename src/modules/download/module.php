<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//DownloadModule
class DownloadModule extends MultiContentModule
{
	//public
	//methods
	//accessors
	//DownloadModule::canPreview
	public function canPreview(Engine $engine, Request $request = NULL,
			Content $content = NULL, &$error = FALSE)
	{
		//XXX previewing is not always irrelevant
		return FALSE;
	}


	//DownloadModule::canPublish
	public function canPublish(Engine $engine, Request $request = NULL,
			Content $content = NULL, &$error = FALSE)
	{
		$error = 'Publishing is always disabled';
		return FALSE;
	}


	//DownloadModule::canUnpublish
	public function canUnpublish(Engine $engine, Request $request = NULL,
			Content $content = NULL, &$error = FALSE)
	{
		$error = 'Unpublishing is always disabled';
		return FALSE;
	}


	//useful
	//DownloadModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		$action = $request->getAction();

		if($internal)
			switch($action)
			{
				case 'get':
					return $this->getContent($engine,
							$request->getID(),
							$request->getTitle(),
							$request);
				case 'getRoot':
					return DownloadContent::getRoot($engine,
							$this->getName());
				case 'submit':
					return $this->_callInternalSubmit(
							$engine, $request);
			}
		else
			switch($action)
			{
				case 'download':
				case 'submit':
					$action = 'call'.$action;
					return $this->$action($engine,
							$request);
				case 'folder_new':
					return $this->callFolderNew($engine,
							$request);
				case 'file_insert':
					return $this->callSubmit($engine,
							$request);
			}
		return parent::call($engine, $request, $internal);
	}

	protected function _callInternalSubmit($engine, $request)
	{
		if(($filename = $request->get('filename')) === FALSE)
			return FALSE;
		$parent = $this->getContent($engine, $request->getID(),
				$request->getTitle(), $request);
		$error = $this->_submitProcessFileDo($engine, $request, $parent,
				$filename, $request->getTitle(), $content);
		if($error !== FALSE)
			//XXX report the error to the user instead
			return $engine->log('LOG_ERR', $error);
		return $content;
	}


	//protected
	//properties
	static protected $S_IFDIR = 512;
	static protected $content_classes = array(
		'folder' => 'FolderDownloadContent',
		'file' => 'FileDownloadContent');

	//queries
	//IN:	module_id
	static protected $query_list_admin = 'SELECT
		daportal_content.content_id AS id, timestamp,
		daportal_user_enabled.user_id AS user_id, username,
		daportal_group.group_id AS group_id, groupname,
		title, daportal_content.enabled AS enabled,
		daportal_content.public AS public, download_id, mode
		FROM daportal_content, daportal_download, daportal_user_enabled,
		daportal_group
		WHERE daportal_content.content_id=daportal_download.content_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id';
	//IN:	module_id
	static protected $query_list_admin_count = 'SELECT COUNT(*) AS count
		FROM daportal_content, daportal_download, daportal_user_enabled,
		daportal_group
		WHERE daportal_content.content_id=daportal_download.content_id
		AND daportal_content.module_id=:module_id
		AND daportal_content.user_id=daportal_user_enabled.user_id
		AND daportal_content.group_id=daportal_group.group_id';

	//translations
	protected $file_text_content_list_title = 'File list';
	protected $file_text_content_list_title_by = 'Files from';
	protected $file_text_content_list_title_by_group = 'Files from group';
	protected $folder_text_content_list_title = 'Folder list';
	protected $folder_text_content_list_title_by = 'Folders from';
	protected $folder_text_content_list_title_by_group = 'Folders from group';


	//methods
	//essential
	//DownloadModule::DownloadModule
	protected function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('Downloads') : $title;
		parent::__construct($id, $name, $title);
		$this->text_content_admin = _('Downloads administration');
		$this->text_content_title = _('Downloads');
	}


	//accessors
	//DownloadModule::canUpload
	protected function canUpload(Engine $engine, Request $request = NULL,
			Content $content = NULL, &$error = FALSE)
	{
		return $this->canUpdate($engine, $request, $content, $error);
	}


	//DownloadModule::setContext
	protected function setContext(Engine $engine = NULL,
			Request $request = NULL, Content $content = NULL)
	{
		parent::setContext($engine, $request, $content);
		switch($this->content_class)
		{
			case static::$content_classes['file']:
				$this->stock_content_submit = 'upload';
				$this->text_content_list_title
					= $this->file_text_content_list_title;
				$this->text_content_list_title_by
					= $this->file_text_content_list_title_by;
				$this->text_content_list_title_by_group
					= $this->file_text_content_list_title_by_group;
				$this->text_content_submit = _('Upload');
				$this->text_content_submit_content
					= _('Upload file');
				break;
			case static::$content_classes['folder']:
				$this->stock_content_submit = 'folder-new';
				$this->text_content_list_title
					= $this->folder_text_content_list_title;
				$this->text_content_list_title_by
					= $this->folder_text_content_list_title_by;
				$this->text_content_list_title_by_group
					= $this->folder_text_content_list_title_by_group;
				$this->text_content_submit = _('Create');
				$this->text_content_submit_content
					= _('New folder');
				break;
		}
	}


	//useful
	//calls
	//DownloadModule::callDefault
	protected function callDefault(Engine $engine, Request $request)
	{
		$class = static::$content_classes['folder'];
		$p = $request->get('page') ?: 0;

		if($request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$root = new $class($engine, $this);
		return new PageResponse($root->display($engine, $request));
	}


	//DownloadModule::callDownload
	protected function callDownload(Engine $engine, Request $request)
	{
		global $config;
		$error = _('Could not fetch content');

		if(($id = $request->getID()) === FALSE)
			return $this->callDefault($engine, new Request());
		if(($content = $this->getContent($engine, $id,
				$request->getTitle())) === FALSE)
			return new ErrorResponse($error,
				Response::$CODE_ENOENT);
		if($content instanceof FileDownloadContent)
			return $content->download($engine, $request);
		return new ErrorResponse($error);
	}


	//DownloadModule::callSubmit
	protected function callSubmit(Engine $engine, Request $request)
	{
		return parent::callSubmit($engine, $request);
	}

	protected function _submitContent(Engine $engine, Request $request)
	{
		$class = static::$content_classes['folder'];

		if($request->get('type') == 'file'
				&& ($parent = $request->get('parent'))
				!== FALSE)
			return $class::loadByDownloadID($engine, $this,
					$parent);
		return parent::_submitContent($engine, $request);
	}

	protected function _submitProcess(Engine $engine, Request $request,
			Content $content)
	{
		//verify the request
		if($request->isIdempotent())
			return TRUE;
		switch($request->get('type'))
		{
			case 'file':
				return $this->_submitProcessFile($engine,
						$request, $content);
			default:
				return parent::_submitProcess($engine, $request,
						$content);
		}
	}

	protected function _submitProcessFile(Engine $engine, Request $request,
			$parent)
	{
		$forbidden = array('.', '..');
		//XXX UNIX supports backward slashes in filenames
		$delimiters = array('/', '\\');

		if(!isset($_FILES['files'])
				|| count($_FILES['files']['error']) == 0)
			return TRUE;
		//check known errors
		$count = 0;
		foreach($_FILES['files']['error'] as $k => $v)
		{
			if($v == UPLOAD_ERR_NO_FILE)
				continue;
			else if($v != UPLOAD_ERR_OK)
				return _('An error occurred');
			foreach($forbidden as $f)
				if($_FILES['files']['name'][$k] == $f)
					return _('Forbidden filename');
			foreach($delimiters as $d)
				if(strstr($_FILES['files']['name'][$k], $d)
						!== FALSE)
					return _('An error occurred');
			$count++;
		}
		if($count == 0)
			return _('No file uploaded');
		//store each file uploaded
		$errors = array();
		foreach($_FILES['files']['error'] as $k => $v)
		{
			if($_FILES['files']['error'] == UPLOAD_ERR_NO_FILE)
				continue;
			$res = $this->_submitProcessFileDo($engine, $request,
					$parent,
					$_FILES['files']['tmp_name'][$k],
					$_FILES['files']['name'][$k]);
			if($res !== TRUE)
				$errors[] = $res;
		}
		return (count($errors)) ? implode("\n", $errors) : FALSE;
	}

	protected function _submitProcessFileDo(Engine $engine,
			Request $request, $parent, $pathname, $filename,
			&$content = FALSE)
	{
		$class = static::$content_classes['file'];

		if($filename === FALSE)
			$filename = basename($pathname);
		$content = new $class($engine, $this, array(
			'title' => $filename, 'content' => FALSE,
			'filename' => $pathname,
			'parent_id' => ($parent !== FALSE)
				? $parent->get('download_id') : FALSE,
			'mode' => 0644));
		$error = _('Internal server error');
		if($content->save($engine, $request, $error) === FALSE)
			return $error;
		return TRUE;
	}


	//forms
	//DownloadModule::formSubmit
	protected function formSubmit(Engine $engine, Request $request)
	{
		$r = $this->getRequest('submit', array(
				'type' => $request->get('type'),
				'parent' => $request->get('parent')));

		$form = new PageElement('form', array('request' => $r));
		//content
		$this->helperSubmitContent($engine, $request, $form);
		//buttons
		$this->helperSubmitButtons($engine, $request, $form);
		return $form;
	}


	//helpers
	//DownloadModule::helperActionsAdmin
	protected function helperActionsAdmin(Engine $engine, Request $request)
	{
		//XXX duplicated from ContentModule::helperActionsAdmin
		if($request->get('admin') === 0)
			return FALSE;
		$ret = array();
		$r = $this->getRequest('admin');
		$ret[] = $this->helperAction($engine, 'admin', $r,
				$this->text_content_admin);
		return $ret;
	}


	//DownloadModule::helperAdminRow
	protected function helperAdminRow(Engine $engine, PageElement $row,
			$res)
	{
		$class = ($res['mode'] & static::$S_IFDIR)
			? static::$content_classes['folder']
			: static::$content_classes['file'];

		$content = $class::loadFromProperties($engine, $this, $res);
		$r = $content->displayRow($engine);
		$columns = array('id', 'title', 'enabled', 'public', 'username',
			'date');
		foreach($columns as $c)
			$row->set($c, $r->get($c));
		$stock = ($res['mode'] & static::$S_IFDIR) ? 'folder' : 'file';
		$row->set('icon', new PageElement('image', array('size' => 16,
				'stock' => $stock)));
	}
}

?>
