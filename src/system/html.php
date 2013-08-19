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



//HTML
class HTML
{
	//public
	//methods
	//essential
	//HTML::HTML
	protected function __construct($charset = FALSE)
	{
		global $config;

		//for escaping
		if(!defined('ENT_HTML401'))
			define('ENT_HTML401', 0);
		//for encoding
		if($charset === FALSE)
			$charset = $config->get('defaults', 'charset');
		$this->charset = $charset;
		switch(strtolower($charset))
		{
			case 'ascii':
				$this->parser = xml_parser_create('US-ASCII');
				break;
			case 'iso-8859-1':
			case 'iso-8859-15':
				$this->parser = xml_parser_create('ISO-8859-1');
				break;
			case 'utf-8':
				$this->parser = xml_parser_create('UTF-8');
				break;
			default:
				$this->parser = xml_parser_create('');
				break;
		}
	}


	//HTML::~HTML
	public function __destruct()
	{
		xml_parser_free($this->parser);
	}


	//static
	//useful
	//HTML::filter
	static public function filter($engine, $content, $whitelist = FALSE)
	{
		$html = new HTML;
		$start = array($html, '_filterElementStart');
		$end = array($html, '_filterElementEnd');
		$filter = array($html, '_filterCharacterData');
		$from = array('<br>', '<hr>');
		$to = array('<br/>', '<hr/>');

		if($whitelist !== FALSE)
			$html->whitelist = $whitelist;
		if(xml_set_element_handler($html->parser, $start, $end)
				!== TRUE)
			return ''; //XXX report error
		xml_set_character_data_handler($html->parser, $filter);
		//give it more chances to validate
		$content = str_ireplace($from, $to, $content);
		switch(strtolower($html->charset))
		{
			case 'iso-8859-1':
			case 'iso-8859-15':
				//do not rely on input charset detection
				$content = utf8_encode($content);
				break;
		}
		//give it a root tag if it seems to need one
		if(strncmp('<!DOCTYPE', $content, 9) != 0
				&& strncmp('<?xml', $content, 4) != 0)
			$content = '<root>'.$content.'</root>';
		if(($ret = xml_parse($html->parser, $content, TRUE)) != 1)
		{
			$error = xml_error_string(xml_get_error_code(
					$html->parser)).' at line '
				.xml_get_current_line_number($html->parser)
				.', column '
				.xml_get_current_column_number($html->parser);
			$engine->log('LOG_DEBUG', $error);
		}
		//close the remaining tags
		while(($tag = array_pop($html->stack)) != NULL)
			$html->content .= "</$tag>";
		return $html->content;
	}

	protected function _filterCharacterData($parser, $data)
	{
		//skip the contents of blacklisted tags
		if($this->blacklist_level > 0)
			return;
		$this->content .= htmlspecialchars($data, ENT_NOQUOTES);
	}

	protected function _filterElementStart($parser, $name,
			$attributes)
	{
		$tag = strtolower($name);
		//skip the contents of blacklisted tags
		if($this->blacklist_level > 0)
			return $this->blacklist_level++;
		if(in_array($tag, HTML::$blacklist))
		{
			$this->blacklist_level = 1;
			return;
		}
		//output whitelisted tags and attributes
		if(!isset($this->whitelist[$tag]))
			return;
		$this->content .= "<$tag";
		$a = $this->whitelist[$tag];
		foreach($attributes as $k => $v)
		{
			$attr = strtolower($k);
			if(!in_array($attr, $a))
				continue;
			$this->content .= ' '.$attr.'="'
				.htmlspecialchars($v, ENT_COMPAT | ENT_HTML401,
						$this->charset).'"';
		}
		//close the <br> and <img> tags directly
		if($tag == 'br' || $tag == 'img')
			$this->content .= '/';
		$this->content .= '>';
		$this->stack[] = $tag;
	}

	protected function _filterElementEnd($parser, $name)
	{
		$tag = strtolower($name);
		//skip the contents of blacklisted tags
		if($this->blacklist_level > 1)
			return $this->blacklist_level--;
		if($this->blacklist_level == 1 && in_array($tag,
				HTML::$blacklist))
		{
			$this->blacklist_level = 0;
			return;
		}
		if(!isset($this->whitelist[$tag]))
			return;
		//the <br> and <img> tags were already closed
		if($tag == 'br' || $tag == 'img')
			return;
		$this->content .= "</$tag>";
		//remember which tags were closed
		for($i = count($this->stack) - 1; $i >= 0; $i--)
			if($this->stack[$i] == $tag)
			{
				unset($this->stack[$i]);
				break;
			}
	}


	//HTML::format
	static public function format($engine, $content)
	{
		$from = '/((ftp:\/\/|http:\/\/|https:\/\/|mailto:)([-+a-zA-Z0-9.:\/_%?!=,;~#@()]|&amp;)+)/';
		//FIXME obfuscate e-mail addresses
		$to = '<a href="\1">\1</a>';

		$ret = '<div>';
		$lines = explode("\n", $content);
		$list = 0;
		foreach($lines as $l)
		{
			$l = htmlspecialchars($l, ENT_COMPAT);
			$l = preg_replace($from, $to, $l);
			if(strlen($l) > 0 && $l[0] == ' ')
			{
				if(strlen($l) > 2 && $l[1] == '*'
						&& $l[2] == ' ')
				{
					//list
					$l = '<li>'.substr($l, 3).'</li>';
					if($list == 0)
					{
						$list = 1;
						$l = '<ul>'.$l;
					}
					$ret .= $l;
				}
				else
					//preformatted content
					$ret .= '<span class="preformatted">'
						.substr($l, 1).'</span><br/>';
			}
			else if($list)
			{
				//close the list if necessary
				$ret .= '</ul>'.$l.'<br/>';
				$list = 0;
			}
			else
				$ret .= $l.'<br/>';
		}
		$ret .= '</div>';
		return $ret;
	}


	//HTML::validate
	static public function validate($engine, $content)
	{
		$html = new HTML;
		$start = array($html, '_validateElementStart');
		$end = array($html, '_validateElementEnd');

		if(xml_set_element_handler($html->parser, $start, $end)
				!== TRUE)
			return FALSE;
		switch(strtolower($html->charset))
		{
			case 'iso-8859-1':
			case 'iso-8859-15':
				//do not rely on input charset detection
				$content = utf8_encode($content);
				break;
		}
		if(($ret = xml_parse($html->parser, $content, TRUE)) != 1)
		{
			$error = xml_error_string(xml_get_error_code(
					$html->parser)).' at line '
				.xml_get_current_line_number($html->parser)
				.', column '
				.xml_get_current_column_number($html->parser);
			$engine->log('LOG_DEBUG', $error);
		}
		return ($ret == 1) ? $html->valid : FALSE;
	}

	protected function _validateElementStart($parser, $name,
			$attributes)
	{
		//XXX report errors
		$tag = strtolower($name);
		if(!isset($this->whitelist[$tag]))
		{
			$this->valid = FALSE;
			return;
		}
		$a = $this->whitelist[$tag];
		foreach($attributes as $k => $v)
			if(!in_array(strtolower($k), $a))
			{
				$this->valid = FALSE;
				return;
			}
	}

	protected function _validateElementEnd($parser, $name)
	{
	}


	//protected
	//properties
	protected $charset = FALSE;
	protected $parser;
	protected $content = '';
	protected $stack = array();
	protected $valid = TRUE;
	static protected $blacklist = array('script', 'style', 'title');
	protected $blacklist_level = 0;
	protected $whitelist = array(
		'a' => array('href', 'name', 'rel', 'title'),
		'acronym' => array('class'),
		'b' => array('class'),
		'big' => array('class'),
		'br' => array(),
		'center' => array(),
		'del' => array('class'),
		'div' => array('class'),
		'h1' => array('class'),
		'h2' => array('class'),
		'h3' => array('class'),
		'h4' => array('class'),
		'h5' => array('class'),
		'h6' => array('class'),
		'hr' => array('class'),
		'i' => array('class'),
		'img' => array('alt', 'class', 'src', 'title'),
		'ins' => array('class'),
		'li' => array('class'),
		'ol' => array('class'),
		'p' => array('class'),
		'pre' => array('class'),
		'small' => array('class'),
		'span' => array('class'),
		'sub' => array('class'),
		'sup' => array('class'),
		'table' => array('class'),
		'tbody' => array('class'),
		'td' => array('class', 'colspan', 'rowspan'),
		'th' => array('class', 'colspan', 'rowspan'),
		'tr' => array('class'),
		'tt' => array('class'),
		'u' => array('class'),
		'ul' => array('class'));
}

?>
