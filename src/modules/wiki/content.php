<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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



//WikiContent
class WikiContent extends Content
{
	//public
	//methods
	//essential
	//WikiContent::WikiContent
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		$this->fields['message'] = 'log message';
		//let wiki pages always be public
		$this->setPublic(TRUE);
		parent::__construct($engine, $module, $properties);
		$this->text_content_by = _('Wiki page by');
		$this->text_more_content = _('More wiki pages...');
		$this->text_submit_content = _('Create a page');
	}


	//accessors
	//WikiContent::canSubmit
	public function canSubmit(Engine $engine, Request $request = NULL,
			&$error = FALSE)
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
	public function canUpdate(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		$credentials = $engine->getCredentials();

		$error = _('You need to be logged in to update wiki pages');
		if($credentials->getUserID() == 0)
			return FALSE;
		//verify the request
		$error = _('The request expired or is invalid');
		if($request !== NULL && $request->isIdempotent())
			return FALSE;
		//verify the title
		$title = ($request !== NULL) ? $request->get('title') : FALSE;
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
	public function getContent(Engine $engine)
	{
		return $this->getMarkup($engine);
	}


	//WikiContent::setContent
	public function setContent(Engine $engine, $content)
	{
		parent::setContent($engine,
				HTML::filter($engine, $content, array()));
		$this->markup = HTML::filter($engine, $content);
	}


	//useful
	//WikiContent::display
	public function display(Engine $engine, Request $request = NULL)
	{
		$type = ($request !== NULL) ? $request->get('display') : FALSE;
		$types = array('revisions');

		//allow more content types to be explicitly displayed
		if(in_array($type, $types))
			return $this->displayContent($engine, $request);
		return parent::display($engine, $request);
	}


	//WikiContent::displayContent
	public function displayContent(Engine $engine, Request $request)
	{
		$type = $request->get('display');
		$revision = $request->get('revision');
		$diff = $request->get('diff');

		$vbox = new PageElement('vbox');
		if($type === FALSE || $type == 'content')
			$vbox->append('htmlview', array(
					'text' => $this->getMarkup($engine,
						$request)));
		if($this->getID() !== FALSE)
		{
			$stock = $this->getModule()->getName();
			$title = _('Revisions');
			$revisions = $this->_contentRevisions($engine,
					$request);
			if($type === FALSE)
			{
				$expander = $vbox->append('expander',
						array('title' => $title));
				$expander->append($revisions);
			}
			else if($type == 'revisions')
			{
				$container = $vbox->append('title', array(
						'class' => 'revisions',
						'stock' => $stock,
						'text' => $title));
				$vbox->append($revisions);
			}
		}
		return $vbox;
	}

	protected function _contentRevisions(Engine $engine, Request $request)
	{
		$module = $this->getModule()->getName();
		$title = $this->getTitle();
		$error = _('Could not list revisions');

		if(($root = static::getRoot($module)) === FALSE
				|| strpos($title, '/') !== FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		//obtain the revision list
		$cmd = 'rlog';
		$cmd .= ' '.escapeshellarg($root.'/'.$title);
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
			$revision = substr($rcs[$i], 9);
			$r = $this->getRequest(FALSE, array(
					'revision' => $revision));
			$name = new PageElement('label');
			$name->append('link', array('request' => $r,
					'text' => $revision));
			if($i < $cnt - 5)
			{
				//FIXME revision1 may be wrong
				$revision1 = substr($rcs[$i + 4], 9);
				$r = $this->getRequest(FALSE, array(
						'diff' => '',
						'r1' => $revision1,
						'r2' => $revision));
				$name->append('label', array('text' => ' ('));
				$name->append('link', array('request' => $r,
						'text' => 'diff'));
				$name->append('label', array('text' => ')'));
			}
			$row->set('title', $name);
			//date
			$date = substr($rcs[$i + 1], 6, 19);
			$row->set('date', $date);
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
			$row->set('username', $username);
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
			$row->set('message', $message);
		}
		return $view;
	}


	//WikiContent::form
	public function form(Engine $engine, Request $request = NULL)
	{
		return parent::form($engine, $request);
	}

	public function _formSubmit(Engine $engine, Request $request)
	{
		$vbox = new PageElement('vbox');
		$vbox->append('entry', array('name' => 'title',
				'text' => _('Title: '),
				'value' => $request->get('title')));
		$vbox->append('htmledit', array('name' => 'content',
				'value' => $request->get('content')));
		$vbox->append('entry', array('text' => _('Log message: '),
				'name' => 'message',
				'value' => $request->get('message')));
		return $vbox;
	}

	public function _formUpdate(Engine $engine, Request $request)
	{
		$vbox = new PageElement('vbox');

		if(($value = $request->get('content')) === FALSE)
			$value = $this->getMarkup($engine);
		$vbox->append('htmledit', array('name' => 'content',
				'value' => $value));
		$value = $request->get('message');
		$vbox->append('entry', array('text' => _('Log message: '),
				'name' => 'message',
				'value' => $value));
		return $vbox;
	}


	//WikiContent::previewContent
	public function previewContent(Engine $engine, Request $request = NULL)
	{
		//XXX use the cache from the database instead
		$content = HTML::filter($engine, $this->getContent($engine),
				array());
		$length = $this->preview_length;
		$text = ($length <= 0 || strlen($content) < $length)
			? $content : substr($content, 0, $length).'...';

		return new PageElement('label', array('text' => $text));
	}


	//WikiContent::save
	public function save(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert(Engine $engine, Request $request = NULL,
			&$error)
	{
		$module = $this->getModule()->getName();
		$cred = $engine->getCredentials();
		$username = $cred->getUsername();
		$content = $request->get('content');

		$error = _('Could not find the wiki repository');
		if(($root = static::getRoot($module)) === FALSE)
			return FALSE;
		//XXX check first if this title already exists for this module
		$file = $root.'/'.$this->getTitle();
		$error = _('A wiki page already exists with this name');
		if(file_exists($file.',v'))
			return FALSE;
		//translate the content
		//XXX remains even in case of failure
		$this->setContent($engine, $request->get('content'));
		//insert the content
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$error = _('Could not create the wiki page');
		if(($fp = fopen($file, 'x')) === FALSE)
			return FALSE;
		$message = $request->get('message');
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

	protected function _saveUpdate(Engine $engine, Request $request = NULL,
			&$error)
	{
		$cred = $engine->getCredentials();
		$username = $cred->getUsername();
		$module = $this->getModule()->getName();

		$error = _('Could not find the wiki repository');
		if(($root = static::getRoot($module)) === FALSE)
			return FALSE;
		$file = $root.'/'.$this->getTitle();
		$error = _('No wiki page was found with this name');
		if(realpath($file.',v') === FALSE)
			return FALSE;
		//translate the content
		//XXX remains even in case of failure
		$this->setContent($engine, $request->get('content'));
		//update the content
		if(parent::_saveUpdate($engine, NULL, $error) === FALSE)
			return FALSE;
		$error = _('Could not update the wiki page');
		if(($fp = fopen($file, 'x')) === FALSE)
			return FALSE;
		$message = $request->get('message');
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


	//protected
	//properties
	static protected $class = 'WikiContent';


	//methods
	//accessors
	//WikiContent::getMarkup
	protected function getMarkup(Engine $engine, Request $request = NULL)
	{
		$revision = ($request !== NULL) ? $request->get('revision')
			: FALSE;
		$module = $this->getModule()->getName();
		$title = $this->getTitle();

		if($revision !== FALSE)
			return $this->getMarkupRevision($engine, $revision);
		if($request !== NULL && $request->get('diff') !== FALSE)
		{
			$r1 = $request->get('r1');
			$r2 = $request->get('r2');
			if($r1 !== FALSE && $r2 !== FALSE)
				return $this->getMarkupDiff($engine, $r1, $r2);
		}
		if($this->markup !== FALSE)
			return $this->markup;
		if(($rcs = $this->getMarkupRevision($engine)) === FALSE)
			return FALSE;
		$this->setContent($engine, $rcs);
		return $rcs;
	}


	//WikiContent::getMarkupDiff
	protected function getMarkupDiff(Engine $engine, $r1, $r2)
	{
		$module = $this->getModule()->getName();
		$title = $this->getTitle();

		if($r1 === FALSE || $r2 === FALSE)
			return FALSE;
		if(($root = static::getRoot($module)) === FALSE
				|| strpos($title, '/') !== FALSE)
			return FALSE;
		$cmd = 'rcsdiff -q -r'.escapeshellarg($r1)
			.' -r'.escapeshellarg($r2);
		$cmd .= ' -u '.escapeshellarg($root.'/'.$title);
		exec($cmd, $rcs, $res);
		if($res != 0 && $res != 1)
			return FALSE;
		$rcs = implode("\n", $rcs);
		//XXX improve the output format
		return '<pre>'.htmlspecialchars($rcs).'</pre>';
	}


	//WikiContent::getMarkupRevision
	protected function getMarkupRevision(Engine $engine, $revision = FALSE)
	{
		$module = $this->getModule()->getName();
		$title = $this->getTitle();

		if(($root = static::getRoot($module)) === FALSE
				|| strpos($title, '/') !== FALSE)
			return FALSE;
		$cmd = 'co -p -q';
		if($revision !== FALSE)
			$cmd .= ' -r'.escapeshellarg($revision);
		$cmd .= ' '.escapeshellarg($root.'/'.$title);
		exec($cmd, $rcs, $res);
		if($res != 0)
			return FALSE;
		return implode("\n", $rcs);
	}


	//private
	//properties
	private $markup = FALSE;
}

?>
