<?php //$Id$
//Copyright (c) 2011-2015 Pierre Pronchery <khorben@defora.org>
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



//GtkEngine
class GtkEngine extends CLIEngine
{
	//protected
	//properties
	private $format = FALSE;


	//public
	//methods
	//useful
	//GtkEngine::render
	public function render(Response $response)
	{
		global $config;

		if($response instanceof PageResponse)
			$page = $response->getContent();
		else
			return FALSE;
		if(($template = Template::attachDefault($this)) === FALSE)
			return FALSE;
		if(($page = $template->render($this, $page)) === FALSE)
			return FALSE;
		return $this->format->render($this, $page);
	}


	//GtkEngine::match
	public function match()
	{
		if(($score = parent::match()) != 100)
			return $score;
		if(getenv('DISPLAY') === FALSE)
			return 0;
		if(class_exists('gtk'))
			return $score + 1;
		return 0;
	}


	//GtkEngine::attach
	public function attach()
	{
		parent::attach();
		$this->format = new GtkFormat('gtk');
		$this->format->attach($this);
	}


	//GtkEngine::log
	public function log($priority, $message)
	{
		return $this->format->log($priority, $message);
	}
}

?>
