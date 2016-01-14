<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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



//NewsContent
class NewsContent extends Content
{
	//public
	//methods
	//essential
	//NewsContent::NewsContent
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->text_content_by = _('News by');
		$this->text_more_content = _('More news...');
		$this->text_open = _('Read');
		$this->text_submit = _('Submit');
		$this->text_submit_content = _('Submit news');
	}


	//useful
	//NewsContent::displayContent
	public function displayContent(Engine $engine, $request)
	{
		$text = HTML::format($engine, $this->getContent($engine));
		return new PageElement('htmlview', array('text' => $text));
	}


	//NewsContent::previewContent
	public function previewContent(Engine $engine, $request = FALSE)
	{
		$content = $this->getContent($engine);
		$length = $this->preview_length;

		$text = ($length <= 0 || strlen($content) < $length)
			? $content : substr($content, 0, $length).'...';
		$text = HTML::format($engine, $text);
		return new PageElement('htmlview', array('text' => $text));
	}


	//protected
	//properties
	static protected $class = 'NewsContent';
}

?>
