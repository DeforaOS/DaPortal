<?php //$Id$
//Copyright (c) 2012 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/page.php');


//GitScmProject
class GitScmProject
{
	//public
	//GitScmProject::attach
	public function attach(&$engine)
	{
	}


	//actions
	//GitScmProject::browse
	public function browse($engine, $project, $request)
	{
		$error = _('No Git repository defined');

		//FIXME really implement
		//check the cvsroot
		return new PageElement('dialog', array('type' => 'error',
				'text' => $error));
	}


	//GitScmProject::download
	public function download($engine, $project, $request)
	{
		$title = _('Repository');

		//repository
		$vbox = new PageElement('vbox');
		$vbox->append('title', array('text' => $title));
		$text = _('The source code can be obtained as follows: ');
		$vbox->append('label', array('text' => $text));
		$text = '$ git clone '.$project['cvsroot'];
		$vbox->append('label', array('text' => $text,
				'class' => 'preformatted'));
		return $vbox;
	}


	//GitScmProject::timeline
	public function timeline($engine, $project, $request)
	{
		$error = _('No Git repository defined');

		//FIXME really implement
		//check the cvsroot
		return new PageElement('dialog', array('type' => 'error',
				'text' => $error));
	}
}

?>
