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



require_once('./system/user.php');
require_once('./modules/content/module.php');
require_once('./modules/wiki/content.php');


//WikiModule
class WikiModule extends ContentModule
{
	//public
	//methods
	//essential
	//WikiModule::WikiModule
	public function __construct($id, $name, $title = FALSE)
	{
		$this->root = WikiContent::getRoot();
		$title = ($title === FALSE) ? _('Wiki') : $title;
		parent::__construct($id, $name);
		$this->content_class = 'WikiContent';
		$this->text_content_admin = _('Wiki administration');
		$this->text_content_by = _('Page by');
		$this->text_content_item = _('Wiki page');
		$this->text_content_items = _('Wiki pages');
		$this->text_content_list_title = _('Wiki pages');
		$this->text_content_list_title_by = _('Wiki pages by');
		$this->text_content_more_content = _('More wiki pages...');
		$this->text_content_submit = _('New wiki page');
		$this->text_content_title = _('Wiki');
	}


	//useful
	//WikiModule::call
	public function call($engine, $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		switch($action)
		{
			case 'monitor':
				$action = 'call'.ucfirst($action);
				return $this->$action($engine, $request);
			default:
				return parent::call($engine, $request,
						$internal);
		}
	}


	//protected
	//methods
	//calls
	//WikiModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		$title = $this->text_content_title;

		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		$page = new Page(array('title' => $title));
		$page->append('title', array('text' => $title,
				'stock' => $this->name));
		$vbox = $page->append('vbox');
		//search
		$vbox->append('title', array('text' => _('Search the wiki'),
				'stock' => 'search'));
		$r = new Request('search', 'advanced', FALSE, FALSE,
			array('inmodule' => $this->name, 'intitle' => 1));
		$form = $vbox->append('form', array('request' => $r));
		$hbox = $form->append('hbox');
		$hbox->append('entry', array('name' => 'q',
				'text' => _('Look for a page: ')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'search', 'text' => _('Search')));
		$r = new Request('search', 'advanced', FALSE, FALSE,
			array('inmodule' => $this->name, 'incontent' => 1));
		$form = $vbox->append('form', array('request' => $r));
		$hbox = $form->append('hbox');
		$hbox->append('entry', array('name' => 'q',
				'text' => _('Look inside pages: ')));
		$hbox->append('button', array('type' => 'submit',
				'stock' => 'search', 'text' => _('Search')));
		$vbox->append('title', array('text' => _('Recent changes'),
				'stock' => 'help'));
		//recent changes
		$vbox->append($this->callHeadline($engine, FALSE));
		//page list
		$r = new Request($this->name, 'list');
		$vbox->append('link', array('request' => $r,
				'stock' => $this->name,
				'text' => _('List all pages')));
		return $page;
	}


	//WikiModule::callMonitor
	protected function callMonitor($engine, $request)
	{
		$title = _('Wiki monitoring');

		$error = _('Permission denied');
		if(!$this->canAdmin($engine, FALSE, FALSE, $error))
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$page = new Page(array('title' => $title));
		$page->append('title', array('class' => $this->name,
				'stock' => 'monitor', 'text' => $title));
		$vbox = $page->append('vbox');
		//disk usage
		$vbox->append('title', array('text' => _('Disk usage')));
		$text = 'Disk usage for '.$this->root.': ';
		$label = $vbox->append('label', array('text' => $text));
		$free = disk_free_space($this->root);
		$total = disk_total_space($this->root);
		$avail = $total - $free;
		$value = ($avail * 100) / $total;
		$value = sprintf('%.1lf%%', $value);
		$label->append('progress', array('min' => 0, 'max' => $total,
				'low' => round($total * 0.10),
				'high' => round($total * 0.75),
				'value' => $total - $free, 'text' => $value));
		$total = round($total / (1024 * 1024));
		$free = round($free / (1024 * 1024));
		$avail = round($avail / (1024 * 1024));
		$text = " $avail / $total MB ($value)";
		$label->append('label', array('text' => $text));
		return $page;
	}


	//WikiModule::callSubmit
	protected function callSubmit($engine, $request = FALSE)
	{
		return parent::callSubmit($engine, $request);
	}

	protected function _submitProcess($engine, $request, &$content)
	{
		require_once('./system/html.php');
		$db = $engine->getDatabase();
		$cred = $engine->getCredentials();
		$username = $cred->getUsername();

		//verify the request
		if($request === FALSE
				|| $request->getParameter('submit') === FALSE)
			return TRUE;
		//validate the content
		if(($title = $request->getParameter('title')) === FALSE
				|| strlen($title) == 0)
			return _('The title must be set and not empty');
		if(strpos($title, '/') !== FALSE
				|| strpos($title, '\\') !== FALSE)
			return _('The title may not contain slashes');
		if(($text = $request->getParameter('content')) === FALSE)
			return _('The content must be set');
		$text = HTML::filter($engine, $text);
		//additional checks
		if($this->root === FALSE)
			return _('Internal server error');
		//XXX check first if this title already exists for this module
		$file = $this->root.'/'.$title;
		if(file_exists($file.',v'))
			return _('Internal server error');
		if(!HTML::validate($engine, '<div>'.$text.'</div>'))
			return _('Document not valid');
		//submit the content
		if($db->transactionBegin($engine) === FALSE)
			return _('Internal server error');
		$request->setParameter('public', TRUE);
		$res = parent::_submitProcess($engine, $request, $content);
		if($res === FALSE && ($fp = fopen($file, 'x')) !== FALSE)
		{
			$message = $request->getParameter('message');
			$emessage = ($message !== FALSE && strlen($message))
				? ' -m'.escapeshellarg($message) : '';
			$eusername = escapeshellarg($username);
			$efile = escapeshellarg($file);
			$cmd = 'ci -q '.$emessage.' -w'.$eusername.' '.$efile;
			$res = -1;
			if(fwrite($fp, $text) !== FALSE)
			{
				if(fclose($fp) !== FALSE)
					exec($cmd, $rcs, $res);
			}
			else
				fclose($fp);
			if(file_exists($file))
				unlink($file);
			$res = ($res == 0) ? FALSE : _('Internal server error');
		}
		if($res !== FALSE)
			$db->transactionRollback($engine);
		else if($db->transactionCommit($engine) === FALSE)
			return _('Internal server error');
		return $res;
	}


	//WikiModule::callUpdate
	protected function callUpdate($engine, $request)
	{
		return parent::callUpdate($engine, $request);
	}

	protected function _updateProcess($engine, $request, &$content)
	{
		require_once('./system/html.php');
		$db = $engine->getDatabase();
		$cred = $engine->getCredentials();
		$username = $cred->getUsername();
		$title = $content->getTitle();
		$res = FALSE;

		//validate the content and keep the current title
		$parameters = $request->getParameters();
		$parameters['title'] = $title;
		if(isset($parameters['content']))
		{
			$parameters['content'] = HTML::filter($engine,
					$parameters['content']);
			//FIXME broken
			//$content['content'] = $parameters['content'];
		}
		$r = new Request($this->name, 'update', $request->getID(),
			$title, $parameters);
		$r->setIdempotent($request->isIdempotent());
		//additional checks
		if($this->root === FALSE || strpos($title, '/') !== FALSE)
			return _('Invalid title for this page');
		if(!HTML::validate($engine, '<div>'.$content->getContent()
				.'</div>'))
			return _('Document not valid');
		$file = $this->root.'/'.$title;
		if(realpath($this->root.'/'.$title.',v') === FALSE)
			return _('Missing RCS file');
		//update the content
		if($db->transactionBegin($engine) === FALSE)
			return _('Internal server error');
		$res = parent::_updateProcess($engine, $r, $content);
		if($res === FALSE && ($fp = fopen($file, 'x')) !== FALSE)
		{
			$message = $request->getParameter('message');
			$emessage = ($message !== FALSE && strlen($message))
				? ' -m'.escapeshellarg($message) : '';
			$eusername = escapeshellarg($username);
			$efile = escapeshellarg($file);
			$cmd = 'rcs -q -l '.$efile;
			exec($cmd, $rcs, $res);
			$cmd = 'ci -q '.$emessage.' -w'.$eusername.' '.$efile;
			if($res == 0 && fwrite($fp, $content->getContent())
					!== FALSE)
			{
				if(fclose($fp) !== FALSE)
					exec($cmd, $rcs, $res);
			}
			else
				fclose($fp);
			if(file_exists($file))
				unlink($file);
			$res = ($res == 0) ? FALSE : _('Internal server error');
		}
		if($res !== FALSE)
			$db->transactionRollback($engine);
		else if($db->transactionCommit($engine) === FALSE)
			$res = _('Internal server error');
		return $res;
	}


	//helpers
	//WikiModule::helperActionsAdmin
	protected function helperActionsAdmin($engine, $request)
	{
		$admin = $request->getParameter('admin');

		$ret = parent::helperActionsAdmin($engine, $request);
		if($admin === 0)
			return $ret;
		$r = new Request($this->name, 'monitor');
		$ret[] = $this->helperAction($engine, 'monitor', $r,
				_('Wiki monitoring'));
		return $ret;
	}


	//WikiModule::helperSubmitContent
	protected function helperSubmitContent($engine, $request, $page)
	{
		$value = $request->getParameter('content');

		$page->append('htmledit', array('name' => 'content',
				'value' => $value));
	}


	//WikiModule::helperUpdateContent
	protected function helperUpdateContent($engine, $request, $content,
			$page)
	{
		$page->append('label', array('text' => _('Content: ')));
		if(($value = $request->getParameter('content')) === FALSE)
			$value = $content->getContent();
		$page->append('htmledit', array('name' => 'content',
				'value' => $value));
		$message = $request->getParameter('message');
		$page->append('entry', array('name' => 'message',
				'text' => _('Log message: '),
				'value' => $message));
	}


	//private
	//properties
	private $root = FALSE;
}

?>
