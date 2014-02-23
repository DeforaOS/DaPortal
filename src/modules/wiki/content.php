<?php //$Id$
//Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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
class WikiContent extends MultiContent
{
	//public
	//methods
	//essential
	//WikiContent::WikiContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		$this->fields['message'] = 'log message';
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		$this->text_content_by = _('Wiki page by');
		$this->text_more_content = _('More wiki pages...');
		$this->text_submit_content = _('Create a page');
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


	//WikiContent::canUpdate
	public function canUpdate($engine, $request = FALSE, &$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('You need to be logged in to update wiki pages');
		if($credentials->getUserID() == 0)
			return FALSE;
		//verify the request
		$error = _('The request expired or is invalid');
		if($request !== FALSE && $request->isIdempotent())
			return FALSE;
		//verify the title
		$title = ($request !== FALSE)
			? $request->getParameter('title') : FALSE;
		if($title === FALSE)
			$title = $this->getTitle();
		//it is forbidden to change the title
		$error = _('The title is not allowed to change');
		if($title != $this->getTitle())
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
	public function setContent($engine, $content)
	{
		parent::setContent($engine,
				HTML::filter($engine, $content, array()));
		$this->markup = HTML::filter($engine, $content);
	}


	//useful
	//WikiContent::displayContent
	public function displayContent($engine, $request)
	{
		$type = $request->getParameter('type');
		$revision = $request->getParameter('revision');

		$vbox = new PageElement('vbox');
		if($type === FALSE)
			$vbox->append('htmlview', array(
					'text' => $this->getMarkup($engine,
						$revision)));
		if($this->getID() !== FALSE
				&& ($type === FALSE || $type == 'revisions'))
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


	//WikiContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	public function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->getParameter('title')));
		$vbox->append('htmledit', array('name' => 'content',
				'value' => $request->getParameter('content')));
		$vbox->append('entry', array('text' => _('Log message: '),
				'name' => 'message',
				'value' => $request->getParameter('message')));
		return $vbox;
	}

	public function _formUpdate($engine, $request)
	{
		$vbox = new PageElement('vbox');
		$value = FALSE;

		if($request !== FALSE)
			$value = $request->getParameter('content');
		if($value === FALSE)
			$value = $this->getMarkup($engine);
		$vbox->append('htmledit', array('name' => 'content',
				'value' => $value));
		$value = ($request !== FALSE)
			? $request->getParameter('message') : FALSE;
		$vbox->append('entry', array('text' => _('Log message: '),
				'name' => 'message',
				'value' => $value));
		return $vbox;
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
		$this->setContent($engine, $request->getParameter('content'));
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
		$cred = $engine->getCredentials();
		$username = $cred->getUsername();
		$module = $this->getModule()->getName();

		$error = _('Could not find the wiki repository');
		if(($root = WikiContent::getRoot($module)) === FALSE)
			return FALSE;
		$file = $root.'/'.$this->getTitle();
		$error = _('No wiki page was found with this name');
		if(realpath($file.',v') === FALSE)
			return FALSE;
		//translate the content
		//XXX remains even in case of failure
		$this->setContent($engine, $request->getParameter('content'));
		//update the content
		if(parent::_saveUpdate($engine, FALSE, $error) === FALSE)
			return FALSE;
		$error = _('Could not update the wiki page');
		if(($fp = fopen($file, 'x')) === FALSE)
			return FALSE;
		$message = $request->getParameter('message');
		$emessage = ($message !== FALSE && strlen($message) > 0)
			? ' -m'.escapeshellarg($message) : '';
		$eusername = escapeshellarg($username);
		$efile = escapeshellarg($file);
		$cmd = 'rcs -q -l '.$efile;
		exec($cmd, $rcs, $res);
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
		return ($res == 0) ? TRUE : FALSE;
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
			$this->setContent($engine, $rcs);
		return $rcs;
	}


	//private
	//properties
	private $markup = FALSE;
}

?>
