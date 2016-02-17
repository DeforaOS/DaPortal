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



require_once('./modules/project/scm.php');


//CVSSCMProject
class CVSSCMProject extends SCMProject
{
	//public
	//CVSSCMProject::attach
	public function attach(Engine $engine)
	{
		global $config;

		$this->cvsroot = $config->get('module::project',
				'scm::backend::cvs::cvsroot'); //XXX
		$this->repository = $config->get('module::project',
				'scm::backend::cvs::repository'); //XXX
		parent::attach($engine);
	}


	//actions
	//CVSSCMProject::browse
	public function browse(ProjectContent $project, Request $request)
	{
		$cvsroot = $project->get('cvsroot');
		$error = _('No CVS repository defined');

		if($this->cvsroot === FALSE || strlen($cvsroot) == 0)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		$vbox = new PageElement('vbox');
		//browse
		$path = $this->cvsroot.'/'.$cvsroot;
		if(($file = $request->get('file')) !== FALSE)
			$file = $this->helperSanitizePath($file);
		else
			$file = '';
		$error = _('No such file or directory');
		if(($st = @lstat($path.'/'.$file)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if(($st['mode'] & CVSSCMProject::$S_IFDIR)
				=== CVSSCMProject::$S_IFDIR)
			return $this->_browseDir($request, $vbox, $path, $file);
		if(($revision = $request->get('revision')) !== FALSE)
			return $this->_browseFileRevision($request, $vbox,
					$path, $file, $revision);
		return $this->_browseFile($request, $vbox, $path, $file);
	}

	private function _browseDir($request, $vbox, $path, $file)
	{
		$error = _('Could not open directory');

		$vbox->append('title', array('text' => _('Browse source')));
		if(($dir = opendir($path.'/'.$file)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//view
		$columns = array('icon' => '', 'title' => _('Filename'),
				'date' => _('Date'),
				'revision' => _('Revision'),
				'username' => _('Author'),
				'content' => _('Description'));
		$view = $vbox->append('treeview', array('columns' => $columns));
		$folders = array();
		$files = array();
		while(($de = readdir($dir)) !== FALSE)
		{
			if($de == '.' || $de == '..')
				continue;
			if(($st = lstat($path.'/'.$file.'/'.$de)) === FALSE)
				continue;
			if(($st['mode'] & CVSSCMProject::$S_IFDIR)
					== CVSSCMProject::$S_IFDIR)
				$folders[$de] = $st;
			else if(substr($de, -2) != ',v')
				continue;
			else
				$files[$de] = $st;
		}
		ksort($folders);
		ksort($files);
		if($file != '' && ($dirname = dirname($file)) !== FALSE
				&& ($st = lstat($path.'/'.$dirname)) !== FALSE
				&& $st['mode'] & CVSSCMProject::$S_IFDIR
					== CVSSCMProject::$S_IFDIR)
		{
			if($dirname == '.' || $dirname == '/')
				$dirname = FALSE;
			$row = $view->append('row');
			$icon = new PageElement('image', array(
				'stock' => 'updir', 'size' => 16));
			$row->set('icon', $icon);
			//title
			$r = new Request($request->getModule(),
				$request->getAction(),
				$request->getID(), $request->getTitle(),
				array('file' => $dirname));
			$link = new PageElement('link', array('request' => $r,
					'text' => _('Parent directory')));
			$row->set('title', $link);
			//date
			$date = strftime(_('%Y/%m/%d %H:%M:%S'), $st['mtime']);
			$row->set('date', $date);
		}
		foreach($folders as $de => $st)
		{
			$row = $view->append('row');
			$icon = Mime::getIconByType($this->engine,
					'inode/directory', 16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->set('icon', $icon);
			//title
			$f = ltrim($file.'/'.$de, '/');
			$r = new Request($request->getModule(),
				$request->getAction(),
				$request->getID(), $request->getTitle(),
				array('file' => $f));
			$link = new PageElement('link', array('request' => $r,
					'text' => $de));
			$row->set('title', $link);
			//date
			$date = strftime(_('%Y/%m/%d %H:%M:%S'), $st['mtime']);
			$row->set('date', $date);
		}
		foreach($files as $de => $st)
		{
			$row = $view->append('row');
			$icon = Mime::getIcon($this->engine, $de, 16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->set('icon', $icon);
			//title
			$f = ltrim($file.'/'.$de, '/');
			$r = new Request($request->getModule(),
				$request->getAction(),
				$request->getID(), $request->getTitle(),
				array('file' => $f));
			$link = new PageElement('link', array('request' => $r,
					'text' => substr($de, 0, -2)));
			$row->set('title', $link);
			//date
			$date = strftime(_('%Y/%m/%d %H:%M:%S'), $st['mtime']);
			$row->set('date', $date);
			//obtain the revisions
			$cmd = 'rlog '.escapeshellarg($path.'/'.$file.'/'.$de);
			unset($rcs);
			exec($cmd, $rcs, $res);
			if(($cnt = count($rcs)) == 0)
				continue;
			for($revs = 0; $revs < $cnt; $revs++)
				if($rcs[$revs]
					== '----------------------------')
					break;
			//revision
			$revision = substr($rcs[$revs + 1], 9);
			$r = new Request($request->getModule(),
				$request->getAction(), $request->getID(),
				$request->getTitle(), array(
					'file' => $file.'/'.$de,
					'revision' => $revision));
			$link = new PageElement('link', array('request' => $r,
					'text' => $revision));
			$row->set('revision', $link);
			//author
			//XXX code duplication
			$username = substr($rcs[$revs + 2], 36);
			$username = substr($username, 0, strspn($username,
					'0123456789abcdefghijklmnopqrstuvwxyz'
					.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
			if(($user = User::lookup($this->engine, $username))
					!== FALSE)
			{
				$r = new Request('project', 'list',
					$user->getUserID(), $username);
				$username = new PageElement('link', array(
						'request' => $r,
						'stock' => 'user',
						'text' => $username));
			}
			$row->set('username', $username);
			//message
			//FIXME implement
		}
		closedir($dir);
		return $vbox;
	}

	private function _browseFile(Request $request, PageElement $vbox, $path,
			$file)
	{
		$error = _('Could not list revisions');

		//obtain the revisions
		$cmd = 'rlog '.escapeshellarg($path.'/'.$file);
		exec($cmd, $rcs, $res);
		if($res != 0 || count($rcs) == 0)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//view
		$vbox->append('title', array('text' => _('Revisions')));
		$columns = array('title' => _('Revision'), 'date' => _('Date'),
				'username' => _('Author'),
				'message' => _('Message'));
		$view = $vbox->append('treeview', array('columns' => $columns,
				'alternate' => TRUE));
		for($i = 0, $cnt = count($rcs); $i < $cnt;)
			if($rcs[$i++] == '----------------------------')
				break;
		for(; $i < $cnt - 2; $i += 3)
		{
			$row = $view->append('row');
			$revision = substr($rcs[$i], 9);
			$r = new Request('project', 'browse', $request->getID(),
				$request->getTitle(), array('file' => $file,
					'revision' => $revision));
			$link = new PageElement('link', array('request' => $r,
					'text' => $revision));
			$row->set('title', $link);
			$row->set('date', substr($rcs[$i + 1], 6, 19));
			//username
			$username = substr($rcs[$i + 1], 36);
			$username = substr($username, 0, strspn($username,
					'abcdefghijklmnopqrstuvwxyz'
					.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
					.'0123456789'));
			if(($user = User::lookup($this->engine, $username))
					!== FALSE)
			{
				$r = new Request('project', 'list',
					$user->getUserID(), $username);
				$username = new PageElement('link', array(
						'request' => $r,
						'stock' => 'user',
						'text' => $username));
			}
			$row->set('username', $username);
			for(; strncmp($rcs[$i + 2], 'branches: ', 10) == 0;
					$i++);
			//message
			$dashes = '----------------------------';
			$longdashes =
'=============================================================================';
			$message = $rcs[$i + 2];
			if($message == $dashes || $message == $longdashes)
				$message = '';
			else
			{
				$msg = '';
				for($i++; $i < $cnt && $rcs[$i + 2] != $dashes
					&& $rcs[$i + 2] != $longdashes; $i++)
					$msg = '...';
				$message .= $msg;
			}
			$row->set('message', $message);
		}
		return $vbox;
	}

	private function _browseFileRevision(Request $request,
			PageElement $vbox, $path, $file,
			$revision)
	{
		$error = 'Internal server error';

		$cmd = 'co -p -q -r'.escapeshellarg($revision)
			.' '.escapeshellarg($path.'/'.$file);
		if(($fp = popen($cmd, 'r')) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$filename = basename($file);
		$type = Mime::getType($this->engine, $filename);
		if($request->get('download') !== FALSE)
		{
			$ret = new PipeResponse($fp);
			$ret->setFilename($filename);
			$ret->setType($type);
			return $ret;
		}
		$label = $vbox->append('label');
		//link back
		$r = new Request('project', 'browse', $request->getID(),
			$request->getTitle(), array('file' => $file));
		$label->append('link', array('request' => $r, 'stock' => 'back',
					'text' => 'Back to the revision list'));
		//link to the download
		$label->append('label', array('text' => ' '));
		$rdownload = new Request('project', 'browse', $request->getID(),
			$request->getTitle(), array('file' => $file,
				'revision' => $revision, 'download' => 1));
		$label->append('link', array('request' => $rdownload,
					'stock' => 'download',
					'text' => 'Download file'));
		//link to this page
		$label->append('label', array('text' => ' '));
		$r = new Request('project', 'browse', $request->getID(),
			$request->getTitle(), array('file' => $file,
				'revision' => $revision));
		$label->append('link', array('request' => $r,
					'stock' => 'link',
					'text' => 'Permalink'));
		if(strncmp($type, 'image/', 6) == 0)
			//attempt to render the image inline
			$vbox->append('image', array('request' => $rdownload,
					'text' => $filename));
		else
			//render as text
			while(($line = fgets($fp)) !== FALSE)
			{
				$line = rtrim($line, "\r\n");
				$vbox->append('label', array(
						'class' => 'preformatted',
						'text' => $line));
			}
		pclose($fp);
		return $vbox;
	}


	//CVSSCMProject::download
	public function download(ProjectContent $project, Request $request)
	{
		$title = _('Repository');
		$repository = 'pserver:'.$this->repository;
		$cvsroot = $project->get('cvsroot');

		//repository
		if($this->repository === FALSE || strlen($cvsroot) == 0)
			return FALSE;
		$vbox = new PageElement('vbox');
		$vbox->append('title', array('text' => $title));
		$vbox->append('label', array('text' => _('The source code can be obtained as follows: ')));
		$text = '$ cvs -d:'.$repository.' co '.$cvsroot;
		$vbox->append('label', array('text' => $text,
				'class' => 'preformatted'));
		return $vbox;
	}


	//CVSSCMProject::timeline
	public function timeline(ProjectContent $project, Request $request)
	{
		$cvsroot = $project->get('cvsroot');
		$error = _('No CVS repository defined');

		//check the cvsroot
		$len = strlen($cvsroot);
		if($this->cvsroot === FALSE || $len == 0)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		//history
		$error = _('Could not open the project history');
		$filename = $this->cvsroot.'/CVSROOT/history';
		if(($fp = fopen($filename, 'r')) === FALSE)
			return new PageElement('dialog', array(
				'type' => 'error', 'text' => $error));
		//view
		$columns = array('icon' => '', 'title' => _('Filename'),
				'date' => _('Date'), 'action' => _('Action'),
				'revision' => _('Revision'),
				'username' => _('Author'));
		$vbox = new PageElement('vbox');
		$vbox->append('title', array('text' => _('Timeline')));
		$view = $vbox->append('treeview', array(
				'columns' => $columns));
		//rows
		while(($line = fgets($fp)) !== FALSE)
		{
			$fields = explode('|', $line);
			if(strlen($fields[4]) == 0)
				continue;
			if(strncmp($fields[3], $cvsroot, $len) != 0)
				continue;
			$event = FALSE;
			switch($fields[0][0])
			{
				case 'A':
					$event = 'Add';
					$icon = 'add';
					break;
				case 'F':
					$event = 'Release';
					$icon = FALSE;
					break;
				case 'M':
					$event = 'Modify';
					$icon = 'edit';
					break;
				case 'R':
					$event = 'Remove';
					$icon = 'remove';
					break;
			}
			if($event === FALSE)
				continue;
			$row = $view->prepend('row');
			//icon
			$icon = new PageElement('image', array(
					'stock' => $icon, 'size' => 16));
			$row->set('icon', $icon);
			//title
			$title = substr($fields[3], $len ? $len + 1 : $len).'/'
				.$fields[5];
			$title = ltrim($title, '/');
			$title = rtrim($title, "\n");
			$r = new Request($request->getModule(), 'browse',
				$request->getID(), $request->getTitle(),
				array('file' => $title.',v'));
			$link = new PageElement('link', array('request' => $r,
					'text' => $title));
			$row->set('title', $link);
			//date
			$date = substr($fields[0], 1, 9);
			$date = base_convert($date, 16, 10);
			$date = strftime(_('%d/%m/%Y %H:%M:%S'), $date);
			$row->set('date', $date);
			$row->set('action', $event);
			//revision
			$revision = $fields[4];
			$r = new Request($request->getModule(), 'browse',
				$request->getID(), $request->getTitle(), array(
					'file' => $title.',v',
					'revision' => $revision));
			$link = new PageElement('link', array('request' => $r,
					'text' => $revision));
			$row->set('revision', $link);
			//username
			$username = $fields[1];
			if(($user = User::lookup($this->engine, $username))
					!== FALSE)
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
		}
		//cleanup
		fclose($fp);
		return $vbox;
	}


	//protected
	//methods
	//helpers
	//CVSSCMProject::helperSanitizePath
	protected function helperSanitizePath($path)
	{
		$path = '/'.trim($path, '/').'/';
		$path = str_replace('/./', '/', $path);
		//FIXME really implement '..'
		if(strpos($path, '/../') !== FALSE)
			return '';
		return trim($path, '/');
	}


	//private
	//properties
	static private $S_IFDIR = 040000;
	private $cvsroot = FALSE;
	private $repository = FALSE;
}

?>
