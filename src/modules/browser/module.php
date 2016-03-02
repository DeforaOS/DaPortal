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



//BrowserModule
class BrowserModule extends Module
{
	//public
	//methods
	//essential
	//BrowserModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->$action($engine,
							$request);
				default:
					return FALSE;
			}
		switch($action)
		{
			case 'default':
			case 'download':
			case 'upload':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//protected
	//methods
	//accessors
	//BrowserModule::canUpload
	protected function canUpload(Engine $engine, Request $request = NULL,
			$content = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();
		$error = _('Permission denied');

		//check for the global setting
		if(!$this->configGet('upload'))
			return FALSE;
		//check for anonymous submissions
		$error = _('Anonymous submissions are not allowed');
		if($credentials->getUserID() == 0)
			if(!$this->configGet('anonymous'))
				return FALSE;
		if($content === FALSE)
			return TRUE;
		//check for idempotence
		$error = _('The request expired or is invalid');
		if($request === NULL || $request->isIdempotent())
			return FALSE;
		//check for write permissions
		$error = _('Could not lookup the path');
		if(($path = $this->getPath($engine, $request)) === FALSE)
			return FALSE;
		$error = _('Permission denied');
		return posix_access($path, W_OK) ? TRUE : FALSE;
	}


	//BrowserModule::getDate
	protected function getDate($time)
	{
		$format = _('%d/%m/%Y %H:%M:%S');

		return Date::formatTimestamp($time, $format);
	}


	//BrowserModule::getGroup
	protected function getGroup($gid)
	{
		static $cache = array();

		if(isset($cache[$gid]))
			return $cache[$gid];
		$cache[$gid] = (function_exists('posix_getgrgid')
			&& ($gr = posix_getgrgid($gid)) !== FALSE)
			? $gr['name'] : $gid;
		return $cache[$gid];
	}


	//BrowserModule::getPath
	protected function getPath(Engine $engine, Request $request)
	{
		$root = $this->getRoot($engine);
		$from = array('-');
		$to = array('?');

		if(($path = $request->getTitle()) === FALSE)
			return '/';
		$p = str_replace($from, $to, $path);
		if(($res = glob($root.'/'.$p, GLOB_NOESCAPE)) !== FALSE
				&& count($res) == 1)
			$path = substr($res[0], strlen($root));
		return $this->helperSanitizePath($path);
	}


	//BrowserModule::getPermissions
	protected function getPermissions($mode)
	{
		return Common::getPermissions($mode);
	}


	//BrowserModule::getRoot
	protected function getRoot(Engine $engine)
	{
		if(($root = $this->configGet('root')) === FALSE)
		{
			$message = 'The browser repository is not configured';
			$engine->log('LOG_WARNING', $message);
			$root = '/tmp';
		}
		return $root;
	}


	//BrowserModule::getToolbar
	protected function getToolbar(Engine $engine, $path, $directory = FALSE)
	{
		$toolbar = new PageElement('toolbar');

		if(($parent = dirname($path)) != $path)
		{
			$r = new Request($this->name, FALSE, FALSE,
					ltrim($parent, '/'));
			//XXX change the label to "Browse" for files
			$toolbar->append('button', array('request' => $r,
					'stock' => 'updir',
					'text' => _('Parent directory')));
		}
		$r = new Request($this->name, FALSE, FALSE, ltrim($path, '/'));
		$toolbar->append('button', array('request' => $r,
				'stock' => 'refresh', 'text' => _('Refresh')));
		if($directory === FALSE)
		{
			$r = new Request($this->name, 'download', FALSE,
					ltrim($path, '/'));
			$toolbar->append('button', array('request' => $r,
					'stock' => 'download',
					'text' => _('Download')));
		}
		else if($this->canUpload($engine))
		{
			$r = new Request($this->name, 'upload', FALSE,
					ltrim($path, '/'));
			$toolbar->append('button', array('request' => $r,
					'stock' => 'upload',
					'text' => _('Upload')));
		}
		return $toolbar;
	}


	//BrowserModule::getUser
	protected function getUser($uid)
	{
		static $cache = array();

		if(isset($cache[$uid]))
			return $cache[$uid];
		$cache[$uid] = (function_exists('posix_getpwuid')
			&& ($pw = posix_getpwuid($uid)) !== FALSE)
			? $pw['name'] : $uid;
		return $cache[$uid];
	}


	//useful
	//actions
	//BrowserModule::actions
	protected function actions(Engine $engine, Request $request)
	{
		$ret = array();

		if($request->get('user') !== FALSE
				|| $request->get('group') !== FALSE)
			return $ret;
		if($request->get('admin') != 0)
			return $ret;
		return $ret;
	}


	//calls
	//BrowserModule::callDefault
	protected function callDefault(Engine $engine, Request $request)
	{
		//obtain the path requested
		$path = $this->getPath($engine, $request);
		$title = _('Browser: ').$path;
		$page = new Page(array('title' => $title));
		//title
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//view
		$this->helperDisplay($engine, $page, $path);
		return new PageResponse($page);
	}


	//BrowserModule::callDownload
	protected function callDownload(Engine $engine, Request $request)
	{
		$root = $this->getRoot($engine);
		$error = _('Could not download the file requested');

		//obtain the path requested
		$path = $this->getPath($engine, $request);
		if(($fp = fopen($root.'/'.$path, 'rb')) !== FALSE)
		{
			$ret = new StreamResponse($fp);
			if(($type = Mime::getType($engine, $path)) !== FALSE)
				$ret->setType($type);
			$ret->setFilename(basename($path));
			return $ret;
		}
		$title = _('Browser: ').$path;
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		$page->append('dialog', array('type' => 'error',
				'text' => $error));
		return new PageResponse($page);
	}


	//BrowserModule::callUpload
	protected function callUpload(Engine $engine, Request $request)
	{
		$root = $this->getRoot($engine);

		//check permissions
		$error = _('Unknown error');
		if($this->canUpload($engine, $request, FALSE, $error) === FALSE)
			return new ErrorResponse($error, Response::$CODE_EPERM);
		//obtain the path requested
		$path = $this->getPath($engine, $request);
		//create the page
		$title = _('Browser: ')._('Upload to ').$path;
		$page = new Page(array('title' => $title));
		//title
		$page->append('title', array('stock' => $this->name,
				'text' => $title));
		//toolbar
		//FIXME let stat() vs lstat() be configurable
		$error = _('Could not open the file or directory requested');
		if(($st = @stat($root.'/'.$path)) === FALSE)
			return new ErrorResponse($error);
		if(($st['mode'] & Common::$S_IFDIR) == Common::$S_IFDIR)
			$toolbar = $this->getToolbar($engine, $path, TRUE);
		else
			$toolbar = $this->getToolbar($engine, $path, FALSE);
		$page->append($toolbar);
		//process the request
		if(($error = $this->_uploadProcess($engine, $request, $path))
				=== FALSE)
			return $this->_uploadSuccess($engine, $request, $path,
					$page);
		else if(is_string($error))
			$page->append('dialog', array('type' => 'error',
					'text' => $error));
		//form
		$r = new Request($this->name, 'upload', FALSE, ltrim($path,
				'/'));
		$form = $page->append('form', array('request' => $r));
		$form->append('filechooser', array('name' => 'files[]'));
		$r = new Request($this->name, FALSE, FALSE, ltrim($path, '/'));
		$form->append('button', array('request' => $r,
				'stock' => 'cancel',
				'target' => '_cancel', 'text' => _('Cancel')));
		$form->append('button', array('type' => 'submit',
				'name' => 'action', 'value' => '_upload',
				'text' => _('Upload')));
		return new PageResponse($page);
	}

	protected function _uploadProcess(Engine $engine, Request $request,
			$path)
	{
		$ret = TRUE;
		$forbidden = array('.', '..');
		//XXX UNIX supports backward slashes in filenames
		$delimiters = array('/', '\\');

		//verify the request
		if($request->isIdempotent())
			return TRUE;
		//upload the file(s)
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
				if(strpos($_FILES['files']['name'][$k], $d)
						!== FALSE)
					return _('An error occurred');
			$count++;
		}
		if($count == 0)
			return _('No file uploaded');
		if(is_file($path) && $count != 1)
			//overwrite files one file at a time
			return _('Destination is not a directory');
		//store each file uploaded
		foreach($_FILES['files']['error'] as $k => $v)
		{
			if($_FILES['files']['error'] == UPLOAD_ERR_NO_FILE)
				continue;
			$res = $this->_uploadProcessFile($engine, $request,
					$path, $_FILES['files']['tmp_name'][$k],
					$_FILES['files']['name'][$k], $content);
			if($res === FALSE)
				return _('Internal server error');
		}
		return FALSE;
	}

	protected function _uploadProcessFile(Engine $engine, Request $request,
			$parent, $pathname, $filename, &$content)
	{
		$root = $this->getRoot($engine);
		$dst = $root.'/'.$parent.'/'.$filename;

		return move_uploaded_file($pathname, $dst);
	}

	protected function _uploadSuccess(Engine $engine, Request $request,
			$path, $page)
	{
		$r = new Request($this->name, FALSE, FALSE, ltrim($path, '/'));

		return $this->helperRedirect($engine, $r, $page,
				$this->text_upload_progress);
	}


	//helpers
	//BrowserModule::helperDisplay
	protected function helperDisplay(Engine $engine, PageElement $page,
			$path)
	{
		$root = $this->getRoot($engine);
		$error = _('Could not open the file or directory requested');

		//FIXME let stat() vs lstat() be configurable
		if(($st = @stat($root.'/'.$path)) === FALSE)
			return $page->append('dialog', array('type' => 'error',
					'text' => $error));
		if(($st['mode'] & Common::$S_IFDIR) == Common::$S_IFDIR)
		{
			$toolbar = $this->getToolbar($engine, $path, TRUE);
			$page->append($toolbar);
			$this->helperDisplayDirectory($engine, $page, $root,
					$path);
		}
		else
		{
			$toolbar = $this->getToolbar($engine, $path, FALSE);
			$page->append($toolbar);
			$this->helperDisplayFile($engine, $page, $root, $path,
					$st);
		}
	}


	//BrowserModule::helperDisplayDirectory
	protected function helperDisplayDirectory(Engine $engine,
			PageElement $page, $root, $path)
	{
		$error = _('Could not open the directory requested');

		if(($dir = @opendir($root.'/'.$path)) === FALSE)
			return $page->append('dialog', array('type' => 'error',
				'text' => $error));
		$path = rtrim($path, '/');
		$columns = array('icon' => '', 'title' => _('Title'),
				'user' => _('User'), 'group' => _('Group'),
				'size' => _('Size'), 'date' => _('Date'),
				'mode' => _('Permissions'));
		$view = $page->append('treeview', array('columns' => $columns));
		//obtain (and sort) all entries
		$entries = array();
		while(($de = readdir($dir)) !== FALSE)
			$entries[] = $de;
		closedir($dir);
		asort($entries);
		foreach($entries as $de)
		{
			//skip "." and ".."
			if($de == '.' || $de == '..')
				continue;
			$fullpath = $root.'/'.$path.'/'.$de;
			$st = lstat($fullpath);
			$row = $view->append('row');
			if($st['mode'] & Common::$S_IFDIR)
				$icon = Mime::getIconByType($engine,
					'inode/directory', 16);
			else
				$icon = Mime::getIcon($engine, $de, 16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->setProperty('icon', $icon);
			$r = new Request($this->name, FALSE, FALSE,
					ltrim($path.'/'.$de, '/'));
			$link = new PageElement('link', array(
					'request' => $r, 'text' => $de));
			$row->setProperty('title', $link);
			$row->setProperty('user', $this->getUser($st['uid']));
			$row->setProperty('group', $this->getGroup($st['gid']));
			$row->setProperty('size', Common::getSize($st['size']));
			$row->setProperty('date', $this->getDate($st['mtime']));
			//permissions
			$permissions = $this->getPermissions($st['mode']);
			$permissions = new PageElement('label', array(
					'class' => 'preformatted',
					'text' => $permissions));
			$row->setProperty('mode', $permissions);
		}
	}


	//BrowserModule::helperDisplayFile
	protected function helperDisplayFile(Engine $engine, PageElement $page,
			$root, $path, $st)
	{
		$hbox = $page->append('hbox');
		$col1 = $hbox->append('vbox');
		$col2 = $hbox->append('vbox');
		//filename
		$col1->append('label', array('class' => 'bold',
				'text' => _('Filename:')));
		$r = new Request($this->name, 'download', FALSE,
				ltrim($path, '/'));
		$link = new PageElement('link', array('request' => $r,
				'text' => basename($path)));
		$col2->append($link);
		//type
		$this->_displayFileField($col1, $col2, _('Type:'),
				Mime::getType($engine, basename($path)));
		//user
		$this->_displayFileField($col1, $col2, _('User:'),
				$this->getUser($st['uid']));
		//group
		$this->_displayFileField($col1, $col2, _('Group:'),
				$this->getGroup($st['gid']));
		//permissions
		$this->_displayFileField($col1, $col2, _('Permissions:'),
				$this->getPermissions($st['mode']),
				'preformatted');
		//size
		$this->_displayFileField($col1, $col2, _('Size:'),
				Common::getSize($st['size']));
		//creation time
		$this->_displayFileField($col1, $col2, _('Created on:'),
				$this->getDate($st['ctime']));
		//modification time
		$this->_displayFileField($col1, $col2, _('Last modified:'),
				$this->getDate($st['mtime']));
		//access time
		$this->_displayFileField($col1, $col2, _('Last access:'),
				$this->getDate($st['atime']));
	}

	private function _displayFileField($col1, $col2, $field, $value,
			$class = FALSE)
	{
		$col1->append('label', array('class' => 'bold',
				'text' => $field.' '));
		$col2->append('label', array('class' => $class,
				'text' => $value));
	}


	//DownloadModule::helperRedirect
	protected function helperRedirect(Engine $engine, Request $request,
			PageElement $page, $text = FALSE)
	{
		//XXX duplicated from ContentModule
		if($text === FALSE)
			$text = $this->text_redirect_progress;
		$page->setProperty('location', $engine->getURL($request));
		$page->setProperty('refresh', 30);
		$box = $page->append('vbox');
		$box->append('label', array('text' => $text));
		$box = $box->append('hbox');
		$text = _('If you are not redirected within 30 seconds, please ');
		$box->append('label', array('text' => $text));
		$box->append('link', array('text' => _('click here'),
				'request' => $request));
		$box->append('label', array('text' => '.'));
		return new PageResponse($page);
	}


	//BrowserModule::helperSanitizePath
	protected function helperSanitizePath($path)
	{
		$path = '/'.ltrim($path, '/');
		$path = str_replace('/./', '/', $path);
		//FIXME really implement '..'
		if(strcmp($path, '/..') == 0 || strpos($path, '/../') !== FALSE)
			return '/';
		return $path;
	}


	//properties
	protected $text_redirect_progress = 'Redirection in progress, please wait...';
	protected $text_upload_progress = 'Upload in progress, please wait...';
}

?>
