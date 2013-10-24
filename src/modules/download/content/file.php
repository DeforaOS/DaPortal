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



require_once('./system/common.php');
require_once('./modules/download/content.php');


//FileDownloadContent
class FileDownloadContent extends DownloadContent
{
	//public
	//methods
	//essential
	//FileDownloadContent::FileDownloadContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		$this->text_content_by = _('Uploaded by');
		$this->text_more_content = _('Parent folder');
		$this->text_submit = _('Upload file...');
	}


	//useful
	//FileDownloadContent::displayContent
	public function displayContent($engine, $request)
	{
		$module = $this->getModule()->getName();
		$root = DownloadContent::getRoot($module);
		$text = $this->getContent($engine);
		$format = _('%A, %B %e %Y, %H:%M:%S');

		//output the file details
		$filename = $root.'/'.$this->get('download_id');
		$error = _('Could not obtain details for this file');
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
	public function displayToolbar($engine, $request)
	{
		$toolbar = parent::displayToolbar($engine, $request);
		$toolbar->prepend('button', array('stock' => 'download',
			'request' => $this->getRequest('download'),
			'text' => _('Download')));
		$request = new Request($this->getModule()->getName(), FALSE,
			$this->get('parent_id'));
		$toolbar->prepend('button', array('stock' => 'updir',
			'request' => $request,
			'text' => _('Browse...')));
		return $toolbar;
	}


	//FileDownloadContent::download
	public function download($engine, $request)
	{
		$module = $this->getModule()->getName();
		$root = DownloadContent::getRoot($module);

		//output the file
		$filename = $root.'/'.$this->get('download_id');
		$mime = Mime::getType($engine, $this->getTitle());
		if(($fp = fopen($filename, 'rb')) === FALSE)
		{
			$error = _('Could not read file');
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		}
		$engine->setType($mime);
		return $fp;
	}


	//FileDownloadContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$module = $this->getModule()->getName();
		$root = $this->getRoot($module);
		$db = $engine->getDatabase();
		$query = $this->file_query_insert;
		$parent = $this->get('parent_id');

		if(($filename = $request->getParameter('filename')) === FALSE
				&& ($filename = $this->get('filename'))
				=== FALSE)
		{
			$error = _('The filename must be specified');
			$engine->log('LOG_ERR', $error);
			return FALSE;
		}
		//FIXME check for filename unicity in the current folder
		$name = basename($filename);
		//set missing parameters
		$this->set('download_id', FALSE);
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$args = array('content_id' => $this->getID(),
			'parent' => $parent, 'mode' => 420);
		if($db->query($engine, $query, $args) === FALSE)
		{
			$error = _('Could not register the file');
			return FALSE;
		}
		//store the file
		if(($did = $db->getLastID($engine, 'daportal_download',
				'download_id')) === FALSE)
		{
			$error = _('Internal server error');
			return FALSE;
		}
		$this->set('download_id', $did);
		//copy (or move) the file
		$dst = $root.'/'.$did;
		$error = _('Could not copy the file');
		if(is_uploaded_file($filename))
			return move_uploaded_file($filename, $dst);
		else
			return copy($filename, $dst);
	}


	//static
	//FileDownloadContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		$class = get_class();
		$class::$query_load = $class::$file_query_load;
		return parent::_load($engine, $module, $id, $title,
				get_class());
	}


	//protected
	//properties
	//queries
	//IN:	content_id
	//	parent
	//	mode
	protected $file_query_insert = 'INSERT INTO daportal_download
		(content_id, parent, mode)
		VALUES (:content_id, :parent, :mode)';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $file_query_load = "SELECT daportal_module.name AS module,
		daportal_user_enabled.user_id AS user_id,
		daportal_user_enabled.username AS username,
		daportal_group.group_id AS group_id,
		daportal_group.groupname AS \"group\",
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
		AND (download.mode & 512) = 0
		AND daportal_content.content_id=:content_id";
}

?>
