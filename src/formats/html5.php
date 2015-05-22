<?php //$Id$
//Copyright (c) 2012-2015 Pierre Pronchery <khorben@defora.org>
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
			define('ENT_HTML5', ENT_HTML401);
	}


	//escaping
	//HTML5Format::escape
	protected function escape($text)
	{
		return htmlspecialchars($text,
				ENT_COMPAT | ENT_HTML5 | ENT_NOQUOTES,
				$this->encoding);
	}


	//HTML5Format::escapeAttribute
	protected function escapeAttribute($text)
	{
		return htmlspecialchars($text, ENT_COMPAT | ENT_HTML5,
				$this->encoding);
	}


	//rendering
	//HTML5Format::renderEntry
	protected function renderEntry($e)
	{
		//XXX code duplicated from HTMLFormat::renderEntry
		$class = ($e->get('class') !== FALSE)
			? 'entry '.$e->get('class') : 'entry';

		$this->renderTabs();
		$this->tagOpen('div', $e->getType());
		if(($text = $e->get('text')) !== FALSE)
		{
			$l = new PageElement('label', array(
					'class' => $e->get('class')));
			$l->setProperty('text', $text);
			$this->renderElement($l);
		}
		$name = $e->get('name');
		$value = $e->get('value');
		$type = ($e->get('hidden') === TRUE) ? 'password' : 'text';
		$attributes = array('type' => $type, 'name' => $name,
			'value' => $value);
		if(($size = $e->get('size')) !== FALSE && is_numeric($size))
			$attributes['size'] = $size;
		if(($placeholder = $e->get('placeholder')) !== FALSE)
			$attributes['placeholder'] = $placeholder;
		if(($width = $e->get('width')) !== FALSE && is_numeric($width))
			$attributes['style'] = 'width: '.$width.'ex';
		$this->tag('input', $class, $e->get('id'), $attributes);
		if($this->getJavascript() && is_string($name)
				&& substr($name, -2) == '[]')
			$this->tag('input', 'stock16 add hidden', FALSE, array(
					'type' => 'button',
					'value' => _('More')));
		$this->tagClose('div');
	}


	//HTML5Format::renderMetaCharset
	protected function renderMetaCharset($charset)
	{
		$this->renderTabs();
		$this->tag('meta', FALSE, FALSE, array('charset' => $charset));
	}


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
