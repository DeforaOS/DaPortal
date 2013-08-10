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



require_once('./system/content.php');
require_once('./system/html.php');


//WikiContent
class WikiContent extends Content
{
	//public
	//methods
	//essential
	//WikiContent::WikiContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->class = get_class();
		$this->text_content_by = _('Wiki page by');
		$this->text_more_content = _('More wiki pages...');
		$this->text_submit = _('Create a page');
	}


	//useful
	//WikiContent::displayContent
	public function displayContent($engine, $request)
	{
		return new PageElement('htmlview', array(
			'text' => $this->getContent()));
	}


	//WikiContent::previewContent
	public function previewContent($engine, $request)
	{
		$length = $this->preview_length;

		//FIXME verify that it doesn't break (or use plain text)
		$text = ($length <= 0 || strlen($this->getContent()) < $length)
			? $this->getContent()
			: substr($this->getContent(), 0, $length).'...';
		return new PageElement('htmlview', array('text' => $text));
	}


	//static
	//methods
	//WikiContent::listAll
	static public function listAll($engine, $module, $limit = FALSE,
			$offset = FALSE, $order = FALSE)
	{
		$class = get_class();

		switch($order)
		{
			case FALSE:
			default:
				$order = 'title ASC';
				break;
		}
		return WikiContent::_listAll($engine, $module, $limit, $offset,
				$order, $class);
	}


	//WikiContent::load
	static public function load($engine, $module, $id, $title = FALSE)
	{
		return Content::_load($engine, $module, $id, $title,
				get_class());
	}
}

?>
