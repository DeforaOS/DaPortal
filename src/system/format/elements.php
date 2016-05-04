<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//FormatElements
abstract class FormatElements extends Format
{
	//public
	//methods
	//FormatElements::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
	{
		//FIXME ignore filename for the moment
		if($page === FALSE)
		{
			$p = new Page();
			$this->renderPage($p);
		}
		if($page->getType() == 'page')
			$this->renderPage($page);
		else
		{
			$title = $page->get('title');
			$p = new Page(array('title' => $title));
			$p->append($page);
			$this->renderPage($p);
		}
	}


	//protected
	//methods
	//useful
	//FormatElements::renderElement
	protected function renderElement(PageElement $e)
	{
		switch($e->getType())
		{
			case 'button':
				return $this->renderButton($e);
			case 'checkbox':
				return $this->renderCheckbox($e);
			case 'combobox':
				return $this->renderCombobox($e);
			case 'data':
				return $this->renderData($e);
			case 'dialog':
				return $this->renderDialog($e);
			case 'entry':
				return $this->renderEntry($e);
			case 'expander':
				return $this->renderExpander($e);
			case 'filechooser':
				return $this->renderFileChooser($e);
			case 'form':
				return $this->renderForm($e);
			case 'frame':
				return $this->renderFrame($e);
			case 'hbox':
				return $this->renderHbox($e);
			case 'htmledit':
				return $this->renderHtmledit($e);
			case 'htmlview':
				return $this->renderHtmlview($e);
			case 'iconview':
				return $this->renderIconview($e);
			case 'image':
				return $this->renderImage($e);
			case 'label':
				return $this->renderLabel($e);
			case 'link':
				return $this->renderLink($e);
			case 'menubar':
				return $this->renderMenubar($e);
			case 'radiobutton':
				return $this->renderRadioButton($e);
			case 'page':
				return $this->renderPage($e);
			case 'progress':
				return $this->renderProgress($e);
			case 'statusbar':
				return $this->renderStatusbar($e);
			case 'textview':
				return $this->renderTextview($e);
			case 'title':
				return $this->renderTitle($e);
			case 'toolbar':
				return $this->renderToolbar($e);
			case 'treeview':
				return $this->renderTreeview($e);
			case 'vbox':
				return $this->renderVbox($e);
			default:
				return $this->renderLabel($e);
		}
	}


	//abstract
	//useful
	abstract protected function renderButton(PageElement $e);
	abstract protected function renderCheckbox(PageElement $e);
	abstract protected function renderCombobox(PageElement $e);
	abstract protected function renderData(PageElement $e);
	abstract protected function renderDialog(PageElement $e);
	abstract protected function renderEntry(PageElement $e);
	abstract protected function renderExpander(PageElement $e);
	abstract protected function renderFileChooser(PageElement $e);
	abstract protected function renderForm(PageElement $e);
	abstract protected function renderFrame(PageElement $e);
	abstract protected function renderHbox(PageElement $e);
	abstract protected function renderHtmledit(PageElement $e);
	abstract protected function renderHtmlview(PageElement $e);
	abstract protected function renderIconview(PageElement $e);
	abstract protected function renderImage(PageElement $e);
	abstract protected function renderLabel(PageElement $e);
	abstract protected function renderLink(PageElement $e);
	abstract protected function renderMenubar(PageElement $e);
	abstract protected function renderPage(PageElement $e);
	abstract protected function renderProgress(PageElement $e);
	abstract protected function renderRadioButton(PageElement $e);
	abstract protected function renderStatusbar(PageElement $e);
	abstract protected function renderTextview(PageElement $e);
	abstract protected function renderTitle(PageElement $e);
	abstract protected function renderToolbar(PageElement $e);
	abstract protected function renderTreeview(PageElement $e);
	abstract protected function renderVbox(PageElement $e);
}

?>
