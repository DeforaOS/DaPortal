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


	//accessors
	//WikiContent::canSubmit
	public function canSubmit($engine, $request = FALSE, &$error = FALSE)
	{
		if(parent::canSubmit($engine, $request, $error) === FALSE)
			return FALSE;
		//verify the title
		$error = _('The title must be set and not empty');
		if(($title = $this->getTitle()) === FALSE
				|| strlen($title) == 0)
			return FALSE;
		$error = _('The title must not contain slash characters');
		if(strpos($title, '/') !== FALSE
				|| strpos($title, '\\') !== FALSE)
			return FALSE;
		return TRUE;
	}


	//WikiContent::getContent
	public function getContent($engine)
	{
		return $this->getMarkup($engine);
	}


	//WikiContent::setContent
	public function setContent($content)
	{
		//XXX really wants an Engine instance
		parent::setContent(HTML::filter(FALSE, $content, array()));
		$this->markup = HTML::filter(FALSE, $content);
	}


	//useful
	//WikiContent::displayContent
	public function displayContent($engine, $request)
	{
		$revision = $request->getParameter('revision');

		$vbox = new PageElement('vbox');
		$vbox->append('htmlview', array(
			'text' => $this->getMarkup($engine, $revision)));
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
		$module = $this->getModule()->getName();
		$error = _('Could not list revisions');

		if(($root = WikiContent::getRoot($module)) === FALSE
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
		$content = $this->getContent($engine);
		$length = $this->preview_length;

		//FIXME verify that it doesn't break (or use plain text)
		$text = ($length <= 0 || strlen($content) < $length)
			? $content : substr($content, 0, $length).'...';
		return new PageElement('htmlview', array('text' => $text));
	}


	//WikiContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$module = $this->getModule()->getName();
		$cred = $engine->getCredentials();
		$username = $cred->getUsername();
		$content = $request->getParameter('content');

		$error = _('Could not find the wiki repository');
		if(($root = WikiContent::getRoot($module)) === FALSE)
			return FALSE;
		//XXX check first if this title already exists for this module
		$file = $root.'/'.$this->getTitle();
		$error = _('A wiki page already exists with this name');
		if(file_exists($file.',v'))
			return FALSE;
		//translate the content
		//XXX remains even in case of failure
		$this->setContent($request->getParameter('content'));
		//insert the content
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$error = _('Could not create the wiki page');
		if(($fp = fopen($file, 'x')) === FALSE)
			return FALSE;
		$message = $request->getParameter('message');
		$emessage = ($message !== FALSE && strlen($message) > 0)
			? ' -m'.escapeshellarg($message) : '';
		$eusername = escapeshellarg($username);
		$efile = escapeshellarg($file);
		$cmd = 'ci -q '.$emessage.' -w'.$eusername.' '.$efile;
		$res = -1;
		if(fwrite($fp, $this->markup) !== FALSE)
		{
			$error = _('Could not write the wiki page');
			if(fclose($fp) !== FALSE)
				exec($cmd, $rcs, $res);
		}
		else
			fclose($fp);
		if(file_exists($file))
			unlink($file);
		if(($ret = ($res == 0) ? TRUE : FALSE) === FALSE)
			if(file_exists($file.',v'))
				unlink($file.',v');
		return $ret;
	}

	protected function _saveUpdate($engine, $request, &$error)
	{
		$module = $this->getModule()->getName();

		$error = _('Could not find the wiki repository');
		if(($root = WikiContent::getRoot($module)) === FALSE)
			return FALSE;
		//it is forbidden to change the title
		if(($title = $request->getParameter('title')) !== FALSE
				&& $title != $this->getTitle())
		{
			$error = _('The title is not allowed to change');
			return FALSE;
		}
		//update the content
		if(parent::_saveUpdate($engine, $request, $error) === FALSE)
			return FALSE;
		//FIXME implement
		$error = _('Not yet implemented');
		return FALSE;
	}


	//static
	//methods
	//WikiContent::getRoot
	static public function getRoot($name = FALSE)
	{
		global $config;

		if($name === FALSE)
			$name = 'wiki';
		return $config->get('module::'.$name, 'root');
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
	protected function getMarkup($engine, $revision = FALSE)
	{
		$module = $this->getModule()->getName();

		if($revision === FALSE && $this->markup !== FALSE)
			return $this->markup;
		if(($root = WikiContent::getRoot($module)) === FALSE)
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
			$this->setContent($rcs);
		return $rcs;
	}


	//private
	//properties
	private $markup = FALSE;
}

?>
