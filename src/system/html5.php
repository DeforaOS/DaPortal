<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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



//HTML5
class HTML5 extends HTML
{
	//public
	//methods
	//essential
	//HTML5::HTML5
	protected function __construct($charset = FALSE, $form = FALSE)
	{
		//for escaping
		if(!defined('ENT_HTML5'))
			define('ENT_HTML5', 0);
		$this->flags = ENT_COMPAT | ENT_HTML5;
		//allow more tags
		$this->whitelist['article'] = array('class');
		$this->whitelist['aside'] = array('class');
		$this->whitelist['details'] = array('class');
		$this->whitelist['figcaption'] = array('class');
		$this->whitelist['figure'] = array('class');
		$this->whitelist['footer'] = array('class');
		$this->whitelist['header'] = array('class');
		$this->whitelist['main'] = array('class');
		$this->whitelist['mark'] = array('class');
		$this->whitelist['meter'] = array('class');
		$this->whitelist['nav'] = array('class');
		$this->whitelist['output'] = array('class');
		$this->whitelist['progress'] = array('class');
		$this->whitelist['section'] = array('class');
		$this->whitelist['summary'] = array('class');
		$this->whitelist['time'] = array('class');
		parent::__construct($charset, $form);
	}


	//protected
	//properties
	static protected $class = 'HTML5';
}

?>
