<?php //$Id$
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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



//PageResponse
class PageResponse extends Response
{
	//public
	//methods
	//accessors
	//PageResponse::get
	public function get($name)
	{
		if(($ret = parent::get($name)) !== FALSE)
			return $ret;
		//XXX fallback on page properties
		//XXX an engine is required
		if(($page = $this->getContent(FALSE)) !== FALSE)
			return $page->getProperty($name);
		return FALSE;
	}


	//useful
	//PageResponse::render
	public function render($engine)
	{
		$page = $this->getContent();
		$type = $this->getType();

		if($type === FALSE)
			$type = $engine->getDefaultType();
		switch($type)
		{
			case 'text/html':
				$template = Template::attachDefault($engine);
				if($template === FALSE)
					break;
				if(($p = $template->render($engine, $page))
						!== FALSE)
					$page = $p;
				break;
		}
		$error = 'Could not determine the proper output format';
		if(($output = Format::attachDefault($engine, $type)) !== FALSE)
			$output->render($engine, $page);
		else
		{
			$engine->log('LOG_ERR', $error);
			return Response::$CODE_EIO;
		}
		return $this->getCode();
	}
}

?>
