<?php //$Id$
//Copyright (c) 2013-2015 Pierre Pronchery <khorben@defora.org>
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



//FileDownloadContent
class FileDownloadContent extends DownloadContent
{
	//public
	//methods
	//essential
	//FileDownloadContent::FileDownloadContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields['filename'] = 'Filename';
		parent::__construct($engine, $module, $properties);
		//translations
		$this->stock_back = 'updir';
		$this->stock_submit = 'upload';
		$this->text_content_by = _('Uploaded by');
		$this->text_more_content = _('Parent folder');
		$this->text_submit = _('Upload file...');
		$this->text_submit_content = _('Upload');
	}


	//useful
	//FileDownloadContent::displayButtons
	public function displayButtons($engine, $request)
	{
		$module = $this->getModule();
		$parent = $this->get('parent_id');

		$hbox = new PageElement('hbox');
		//parent folder
		$r = new Request($module->getName(), FALSE, $parent);
		$hbox->append('link', array('stock' => $this->stock_back,
				'request' => $r,
				'text' => $this->text_more_content));
		$r = $this->getRequest();
		$hbox->append('link', array('request' => $r,
				'stock' => $this->stock_link,
				'text' => $this->text_link));
		return $hbox;
	}


	//FileDownloadContent::displayContent
	public function displayContent($engine, $request)
	{
		$text = $this->getContent($engine);
		$format = _('%A, %B %e %Y, %H:%M:%S');

		//output the file details
		$error = _('Could not obtain details for this file');
		if(($filename = $this->getFilename($engine)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		if(($st = stat($filename)) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		$hbox = new PageElement('hbox');
		$col1 = $hbox->append('vbox');
		$col2 = $hbox->append('vbox');
		$request = $this->getRequest('download');
		$this->_contentField($col1, $col2, _('Name: '),
				new PageElement('link', array(
					'request' => $request,
					'text' => $this->getTitle())));
		$this->_contentField($col1, $col2, _('Type: '),
				Mime::getType($engine, $this->getTitle()));
		$request = new Request('user', FALSE, $this->getUserID(),
			$this->getUsername());
		$this->_contentField($col1, $col2, _('Owner: '),
				new PageElement('link', array(
					'request' => $request,
					'stock' => 'user',
					'text' => $this->getUsername())));
		$this->_contentField($col1, $col2, _('Group: '),
				$this->getGroup());
		$this->_contentField($col1, $col2, _('Permissions: '),
				$this->getPermissions());
		$this->_contentField($col1, $col2, _('Size: '),
				Common::getSize($st['size']));
		$this->_contentField($col1, $col2, _('Created on: '),
				strftime($format, $st['ctime']));
		$this->_contentField($col1, $col2, _('Last modified: '),
				strftime($format, $st['mtime']));
		$this->_contentField($col1, $col2, _('Last access: '),
				strftime($format, $st['ctime']));
		$this->_contentField($col1, $col2, _('Comment: '), $text);
		return $hbox;
	}

	private function _contentField($col1, $col2, $field, $value)
	{
		$col1->append('label', array('class' => 'bold',
				'text' => $field.' '));
		if($value instanceof PageElement)
			$col2->append($value);
		else
			$col2->append('label', array('text' => $value));
	}


	//FileDownloadContent::displayToolbar
	public function displayToolbar($engine, $request = FALSE)
	{
		$credentials = $engine->getCredentials();
		$module = $this->getModule();
		$parent = $this->get('parent_id');

		$toolbar = new PageElement('toolbar');
		//parent folder
		//XXX would be nicer with the title too
		$r = new Request($module->getName(), FALSE, $parent);
		$toolbar->append('button', array('stock' => 'updir',
				'request' => $r, 'text' => _('Browse')));
		//download
		$toolbar->append('button', array('stock' => 'download',
				'request' => $this->getRequest('download'),
				'text' => _('Download')));
		if($this->getID() !== FALSE
				&& $this->canUpdate($engine, FALSE, $this))
		{
			//rename
			$r = $this->getRequest('update');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'update',
					'text' => $this->text_update));
		}
		//administration
		if($credentials->isAdmin($engine))
		{
			$r = $module->getRequest('admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		return $toolbar;
	}


	//FileDownloadContent::download
	public function download($engine, $request)
	{
		//output the file
		if(($filename = $this->getFilename($engine)) === FALSE
				|| ($fp = fopen($filename, 'rb')) === FALSE)
		{
			$error = _('Could not read file');
			return new ErrorResponse($error);
		}
		$ret = new StreamResponse($fp);
		$ret->setFilename($this->getTitle());
		$type = Mime::getType($engine, $this->getTitle());
		$ret->setType($type);
		return $ret;
	}


	//FileDownloadContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('filechooser', array('text' => _('File: '),
				'name' => 'files[]'));
		return $vbox;
	}


	//FileDownloadContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$db = $engine->getDatabase();
		$query = static::$file_query_insert;
		$parent = $this->get('parent_id');
		$mode = 420;
		$umask = $this->configGet('umask');

		if(($filename = $this->getFilenameSubmitted($request)) === FALSE
				|| !is_string($filename)
				|| strlen($filename) == 0)
		{
			$error = _('The filename must be specified');
			$engine->log('LOG_ERR', $error);
			return FALSE;
		}
		//FIXME check for filename unicity in the current folder
		$name = $this->getTitle();
		//set missing parameters
		$this->set('download_id', FALSE);
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$args = array('content_id' => $this->getID(),
			'parent' => ($parent !== FALSE) ? $parent : NULL,
			'mode' => $mode);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$error = _('Could not register the file');
			return FALSE;
		}
		//store the file
		$error = _('Internal server error');
		if(($did = $db->getLastID($engine, static::$download_table,
				static::$download_table_id)) === FALSE)
			return FALSE;
		$this->set('download_id', $did);
		//set the umask (if configured)
		$umask = (sscanf($umask, '%o', $umask) == 1)
			? umask($umask) : FALSE;
		//copy (or move) the file
		$error = _('Could not copy the file');
		if(($dst = $this->getFilename($engine)) === FALSE)
			$ret = FALSE;
		else if(is_uploaded_file($filename))
			//FIXME the umask is not applied
			$ret = move_uploaded_file($filename, $dst);
		else
			$ret = copy($filename, $dst);
		if($umask !== FALSE)
			umask($umask);
		return $ret;
	}


	//protected
	//properties
	static protected $class = 'FileDownloadContent';

	static protected $download_table = 'daportal_download';
	static protected $download_table_id = 'download_id';
	//queries
	//IN:	content_id
	//	parent
	//	mode
	static protected $file_query_insert = 'INSERT INTO daportal_download
		(content_id, parent, mode)
		VALUES (:content_id, :parent, :mode)';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT
		daportal_content_enabled.content_id AS id,
		daportal_content_enabled.timestamp AS timestamp,
		daportal_content_enabled.module_id AS module_id, module,
		daportal_content_enabled.user_id AS user_id, username,
		daportal_content_enabled.group_id AS group_id, groupname,
		daportal_content_enabled.title AS title,
		daportal_content_enabled.content AS content,
		daportal_content_enabled.enabled AS enabled,
		daportal_content_enabled.public AS public,
		download.download_id AS download_id,
		parent_download.content_id AS parent_id,
		parent_content.title AS parent_title,
		download.mode AS mode
		FROM daportal_content_enabled, daportal_download download
		LEFT JOIN daportal_download parent_download
		ON download.parent=parent_download.download_id
		LEFT JOIN daportal_content parent_content
		ON parent_download.content_id=parent_content.content_id
		WHERE daportal_content_enabled.content_id=download.content_id
		AND daportal_content_enabled.module_id=:module_id
		AND (daportal_content_enabled.public='1'
		OR daportal_content_enabled.user_id=:user_id)
		AND (download.mode & 512) = 0
		AND daportal_content_enabled.content_id=:content_id
		AND (parent_content.enabled IS NULL OR parent_content.enabled='1')
		AND (parent_content.public IS NULL OR parent_content.public='1'
		OR parent_content.user_id=:user_id)";


	//methods
	//accessors
	//FileDownloadContent::getFilename
	protected function getFilename($engine)
	{
		$module = $this->getModule()->getName();

		if(($root = static::getRoot($engine, $module)) === FALSE)
			return FALSE;
		if(($id = $this->get('download_id')) === FALSE
				|| !is_numeric($id))
			return FALSE;
		return $root.'/'.$id;
	}


	//FileDownloadContent::getFilenameSubmitted
	protected function getFilenameSubmitted(Request $request = NULL)
	{
		if(!is_null($request)
				&& ($filename = $request->get('filename'))
				!== FALSE)
			return $filename;
		return $this->get('filename');
	}
}

?>
