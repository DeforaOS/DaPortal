<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



//ArticleContent
class ArticleContent extends Content
{
	//public
	//methods
	//essential
	//ArticleContent::ArticleContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		$this->text_content_by = _('Article by');
		$this->text_more_content = _('More articles...');
		$this->text_open = _('Read');
		$this->text_submit = _('New article...');
	}


	//useful
	//ArticleContent::displayContent
	public function displayContent($engine, $request)
	{
		$text = HTML::format($engine, $this->getContent($engine));
		return new PageElement('htmlview', array('text' => $text));
	}


	//ArticleContent::previewContent
	public function previewContent($engine, $request = FALSE)
	{
		$content = $this->getContent($engine);
		$length = $this->preview_length;

		$text = ($length <= 0 || strlen($content) < $length)
			? $content
			: substr($content, 0, $length).'...';
		$text = HTML::format($engine, $text);
		return new PageElement('htmlview', array('text' => $text));
	}


	//static
	//methods
	//ArticleContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $order = FALSE, $user = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'timestamp DESC';
				break;
		}
		return $class::_listAll($engine, $module, $limit, $offset,
				$order, $user, $class);
	}


	//ArticleContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		return Content::_load($engine, $module, $id, $title,
				get_class());
	}
}

?>
