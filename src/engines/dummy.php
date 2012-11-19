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



require_once('./system/engine.php');
require_once('./system/format.php');
require_once('./system/locale.php');
require_once('./system/template.php');


//DummyEngine
class DummyEngine extends Engine
{
	//public
	//methods
	//accessors
	//DummyEngine::getRequest
	public function getRequest()
	{
		return FALSE;
	}


	//essential
	//DummyEngine::match
	public function match()
	{
		return 0;
	}


	//DummyEngine::attach
	public function attach()
	{
		Locale::init($this);
	}


	//useful
	//DummyEngine::render
	public function render($page)
	{
		$template = Template::attachDefault($this);
		if($template !== FALSE)
			$page = $template->render($this, $page);
		if(($output = Format::attachDefault($this, $this->getType()))
					=== FALSE)
			$this->log('Could not determine the proper output'
				       .' format');
		else
			$output->render($this, $page);
	}
}

?>
