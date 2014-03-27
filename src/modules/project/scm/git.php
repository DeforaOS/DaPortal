<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/mime.php');
require_once('./system/page.php');
require_once('./modules/project/scm.php');


//GitSCMProject
class GitSCMProject extends SCMProject
{
	//public
	//GitSCMProject::attach
	public function attach($engine)
	{
		global $config;

		$this->gitroot = $config->get('module::project',
				'scm::backend::git::gitroot');
	}


	//actions
	//GitSCMProject::browse
	public function browse($engine, $project, $request)
	{
		$error = _('No Git repository defined');

		//check the gitroot
		if($this->gitroot === FALSE
				|| strlen($project->get('cvsroot')) == 0)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		$vbox = new PageElement('vbox');
		//browse
		$url = parse_url($project->get('cvsroot'));
		$path = $this->gitroot.'/'.basename($url['path']);
		if(($file = $request->getParameter('file')) !== FALSE)
		{
			$file = $this->helperSanitizePath($file);
			$path .= "/$file";
		}
		else
			$file = '/';
		$error = _('No such file or directory');
		if(($st = @lstat($path)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if(($st['mode'] & GitSCMProject::$S_IFDIR)
				=== GitSCMProject::$S_IFDIR)
			return $this->_browseDir($engine, $request, $vbox,
					$path, $file);
		return $this->_browseFile($engine, $request, $vbox, $path,
				$file);
	}

	private function _browseDir($engine, $request, $vbox, $path, $file)
	{
		$error = _('Could not open directory');

		$vbox->append('title', array('text' => _('Browse source')));
		if(($dir = opendir($path)) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		//view
		$columns = array('icon' => '', 'title' => _('Filename'),
				'date' => _('Date'));
		$view = $vbox->append('treeview', array('columns' => $columns));
		$folders = array();
		$files = array();
		while(($de = readdir($dir)) !== FALSE)
		{
			if(strncmp('.', $de, 1) == 0)
				continue;
			if(($st = lstat($path.'/'.$de)) === FALSE)
				continue;
			if(($st['mode'] & GitSCMProject::$S_IFDIR)
					== GitSCMProject::$S_IFDIR)
				$folders[$de] = $st;
			else
				$files[$de] = $st;
		}
		ksort($folders);
		ksort($files);
		foreach($folders as $de => $st)
		{
			$row = $view->append('row');
			$icon = Mime::getIconByType($engine, 'inode/directory',
					16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->setProperty('icon', $icon);
			//title
			$f = ltrim($file.'/'.$de, '/');
			$r = new Request($request->getModule(),
				$request->getAction(),
				$request->getID(), $request->getTitle(),
				array('file' => $f));
			$link = new PageElement('link', array('request' => $r,
					'text' => $de));
			$row->setProperty('title', $link);
			//date
			$date = strftime(_('%Y/%m/%d %H:%M:%S'), $st['mtime']);
			$row->setProperty('date', $date);
		}
		foreach($files as $de => $st)
		{
			$row = $view->append('row');
			$icon = Mime::getIcon($engine, $de, 16);
			$icon = new PageElement('image', array(
					'source' => $icon));
			$row->setProperty('icon', $icon);
			//title
			$f = ltrim($file.'/'.$de, '/');
			$r = new Request($request->getModule(),
				$request->getAction(),
				$request->getID(), $request->getTitle(),
				array('file' => $f));
			$link = new PageElement('link', array('request' => $r,
					'text' => $de));
			$row->setProperty('title', $link);
			//date
			$date = strftime(_('%Y/%m/%d %H:%M:%S'), $st['mtime']);
			$row->setProperty('date', $date);
		}
		closedir($dir);
		return $vbox;
	}

	private function _browseFile($engine, $request, $vbox, $path, $file)
	{
		if(($fp = fopen($path, 'r')) === FALSE)
			return new PageElement('dialog', array(
					'type' => 'error', 'text' => $error));
		if($request->getParameter('download') !== FALSE)
		{
			$ret = new StreamResponse($fp);
			$filename = basename($file);
			$ret->setFilename($filename);
			$type = Mime::getType($engine, $filename);
			$ret->setType($type);
			return $ret;
		}
		$label = $vbox->append('label');
		//link back
		$r = new Request('project', 'browse', $request->getID(),
			$request->getTitle(), array('file' => dirname($file)));
		$label->append('link', array('request' => $r,
				'stock' => 'updir', 'text' => 'Browse'));
		//link to the download
		$label->append('label', array('text' => ' '));
		$r = new Request('project', 'browse', $request->getID(),
			$request->getTitle(), array('file' => $file,
			'download' => 1));
		$label->append('link', array('request' => $r,
					'stock' => 'download',
					'text' => 'Download file'));
		//link to this page
		$label->append('label', array('text' => ' '));
		$r = new Request('project', 'browse', $request->getID(),
			$request->getTitle(), array('file' => $file));
		$label->append('link', array('request' => $r,
					'stock' => 'link',
					'text' => 'Permalink'));
		while(($line = fgets($fp)) !== FALSE)
		{
			$line = rtrim($line, "\r\n");
			$vbox->append('label', array(
					'class' => 'preformatted',
					'text' => $line));
		}
		fclose($fp);
		return $vbox;
	}


	//GitSCMProject::download
	public function download($engine, $project, $request)
	{
		$title = _('Repository');

		//repository
		$vbox = new PageElement('vbox');
		$vbox->append('title', array('text' => $title));
		$text = _('The source code can be obtained as follows: ');
		$vbox->append('label', array('text' => $text));
		$text = '$ git clone '.$project->get('cvsroot');
		$vbox->append('label', array('text' => $text,
				'class' => 'preformatted'));
		return $vbox;
	}


	//GitSCMProject::timeline
	public function timeline($engine, $project, $request)
	{
		$error = _('No Git repository defined');

		//FIXME really implement
		//check the gitroot
		return new PageElement('dialog', array('type' => 'error',
				'text' => $error));
	}


	//protected
	//methods
	//helpers
	//GitSCMProject::helperSanitizePath
	protected function helperSanitizePath($path)
	{
		$path = '/'.ltrim($path, '/');
		$path = str_replace('/./', '/', $path);
		//FIXME really implement '..'
		if(strncmp($path, '/.', 2) == 0
				|| strpos($path, '/../') !== FALSE)
			return '/';
		return $path;
	}


	//private
	//properties
	static private $S_IFDIR = 040000;
	private $gitroot = FALSE;
}

?>
