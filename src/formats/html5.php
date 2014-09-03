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



//HTML5Format
class HTML5Format extends HTMLFormat
{
	//protected
	//methods
	//essential
	//HTML5Format::match
	protected function match($engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/html':
				return 100;
			default:
				return 0;
		}
	}


	//HTML5Format::attach
	protected function attach($engine, $type = FALSE)
	{
		parent::attach($engine, $type);
		$this->doctype = "<!DOCTYPE html>\n";
		//for escaping
		if(!defined('ENT_HTML5'))
			define('ENT_HTML5', 0);
	}


	//escaping
	//HTML5Format::escapeAttribute
	protected function escapeAttribute($text)
	{
		return htmlspecialchars($text, ENT_COMPAT | ENT_HTML5);
	}


	//rendering
	//HTML5Format::renderProgress
	protected function renderProgress($e)
	{
		$args = array();
		$tag = 'progress';

		if(($v = $e->getProperty('min')) !== FALSE
				&& is_numeric($v))
		{
			$tag = 'meter';
			$args['min'] = $v;
			//the meter tag supports additional properties
			if(($v = $e->getProperty('low')) !== FALSE
					&& is_numeric($v))
				$args['low'] = $v;
			if(($v = $e->getProperty('high')) !== FALSE
					&& is_numeric($v))
				$args['high'] = $v;
		}
		if(($v = $e->getProperty('max')) !== FALSE
				&& is_numeric($v))
			$args['max'] = $v;
		if(($v = $e->getProperty('value')) !== FALSE
				&& is_numeric($v))
			$args['value'] = $v;
		if(($v = $e->getProperty('text')) !== FALSE)
			$args['title'] = $v;
		$this->tagOpen($tag, $e->getProperty('class'),
				$e->getProperty('id'), $args, $v);
		$this->tagClose($tag);
		$this->renderChildren($e);
	}


	//HTML5Format::renderStatusbar
	protected function renderStatusbar($e)
	{
		$this->renderTabs();
		$this->tagOpen('footer', $e->getProperty('class'),
				$e->getProperty('id'), FALSE,
				$e->getProperty('text'));
		$this->renderChildren($e);
		$this->tagClose('footer');
	}
}

?>
