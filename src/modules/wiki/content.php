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
//FIXME:
//- warn when inserting a page with a title that already exists



require_once('./system/content.php');
require_once('./system/html.php');


//WikiContent
class WikiContent extends Content
{
	//public
	//methods
	//essential
	//WikiContent::WikiContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		$this->text_content_by = _('Wiki page by');
		$this->text_more_content = _('More wiki pages...');
		$this->text_submit = _('Create a page');
	}


	//useful
	//WikiContent::displayContent
	public function displayContent($engine, $request)
	{
		$revision = $request->getParameter('revision');

		$vbox = new PageElement('vbox');
		$vbox->append('htmlview', array(
			'text' => $this->getMarkup($revision)));
		if($this->getID() !== FALSE)
		{
			$vbox->append('title', array('class' => 'revisions',
				'stock' => $this->getModule()->getName(),
				'text' => _('Revisions')));
			$vbox->append($this->_contentRevisions($engine,
					$request));
		}
		return $vbox;
	}

	protected function _contentRevisions($engine, $request)
	{
		$error = _('Could not list revisions');

		if(($root = WikiContent::getRoot()) === FALSE
				|| strpos($this->getTitle(), '/') !== FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		//obtain the revision list
		$cmd = 'rlog';
		$cmd .= ' '.escapeshellarg($root.'/'.$this->getTitle());
		exec($cmd, $rcs, $res);
		if($res != 0)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		for($i = 0, $cnt = count($rcs); $i < $cnt;)
			if($rcs[$i++] == '----------------------------')
				break;
		$columns = array('title' => _('Name'), 'date' => _('Date'),
				'username' => _('Author'),
				'message' => _('Message'));
		$view = new PageElement('treeview', array(
			'class' => 'revisions', 'columns' => $columns));
		$lsp = '======================================================';
		$ssp = '----------------------------';
		for(; $i < $cnt - 2; $i += 3)
		{
			$row = $view->append('row');
			//name
			$name = substr($rcs[$i], 9);
			$r = $this->getRequest(FALSE, array(
					'revision' => $name));
			$name = new PageElement('link', array('request' => $r,
					'text' => $name));
			$row->setProperty('title', $name);
			//date
			$date = substr($rcs[$i + 1], 6, 19);
			$row->setProperty('date', $date);
			//username
			$username = substr($rcs[$i + 1], 36);
			$username = substr($username, 0, strspn($username,
					'abcdefghijklmnopqrstuvwxyz'
					.'ABCDEFGHIJKLMNOPQRSTUV'
					.'WXYZ0123456789'));
			if(($user = User::lookup($engine, $username)) !== FALSE)
			{
				$r = new Request('user', FALSE,
					$user->getUserID(),
					$user->getUsername());
				$username = new PageElement('link', array(
						'request' => $r,
						'stock' => 'user',
						'text' => $username));
			}
			$row->setProperty('username', $username);
			//message
			$message = $rcs[$i + 2];
			if($message == $ssp || strncmp($message, $lsp,
					strlen($lsp)) == 0)
				$message = '';
			else
			{
				$apnd = '';
				for($i++; $i < $cnt && $rcs[$i + 2] != $ssp
					&& strncmp($rcs[$i + 2], $lsp,
						strlen($lsp)) != 0; $i++)
						$apnd = '...';
				$message .= $apnd;
			}
			$row->setProperty('message', $message);
		}
		return $view;
	}


	//WikiContent::previewContent
	public function previewContent($engine, $request = FALSE)
	{
		$length = $this->preview_length;

		//FIXME verify that it doesn't break (or use plain text)
		$text = ($length <= 0 || strlen($this->getContent()) < $length)
			? $this->getContent()
			: substr($this->getContent(), 0, $length).'...';
		return new PageElement('htmlview', array('text' => $text));
	}


	//static
	//methods
	//WikiContent::getRoot
	static public function getRoot()
	{
		global $config;

		return $config->get('module::wiki', 'root');
	}


	//WikiContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $user = FALSE, $order = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'title ASC';
				break;
		}
		return WikiContent::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}


	//WikiContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		return Content::_load($engine, $module, $id, $title,
				get_class());
	}


	//protected
	//methods
	//accessors
	//WikiContent::getMarkup
	protected function getMarkup($revision = FALSE)
	{
		if($this->getID() === FALSE)
			return '';
		if($revision === FALSE && $this->markup !== FALSE)
			return $this->markup;
		if(($root = WikiContent::getRoot()) === FALSE)
			return FALSE;
		$cmd = 'co -p -q';
		if($revision !== FALSE)
			$cmd .= ' -r'.escapeshellarg($revision);
		$cmd .= ' '.escapeshellarg($root.'/'.$this->getTitle());
		exec($cmd, $rcs, $res);
		if($res != 0)
			return FALSE;
		$rcs = implode("\n", $rcs);
		if($revision === FALSE)
			$this->markup = $rcs;
		return $rcs;
	}


	//private
	//properties
	private $markup = FALSE;
}

?>
