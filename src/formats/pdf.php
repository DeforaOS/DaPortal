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



//PDFFormat
class PDFFormat extends FormatElements
{
	//methods
	//essential
	//PDFFormat::match
	protected function match(Engine $engine, $type = FALSE)
	{
		switch($type)
		{
			case 'application/pdf':
				return 100;
			default:
				return 0;
		}
	}


	//PDFFormat::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
	}


	//public
	//methods
	//rendering
	//PDFFormat::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
	{
		$user = $engine->getCredentials();
		//XXX obtain the full name instead
		$author = $user->getUsername();
		$title = $page->getProperty('title');

		$this->pdf = PDF_new();
		PDF_set_info($this->pdf, "Creator", "DaPortal");
		PDF_set_info($this->pdf, "Author", $author);
		if($title !== FALSE)
			PDF_set_info($this->pdf, "Title", $title);
		print(PDF_get_buffer($this->pdf));
		PDF_delete($this->pdf);
	}


	//protected
	//methods
	//rendering
	//PDFFormat::renderBlock
	protected function renderBlock(PageElement $e)
	{
		if(($text = $e->getProperty('text')) === FALSE)
			$text = '';
		PDF_continue_text($this->pdf, $text);
	}


	//PDFFormat::renderButton
	protected function renderButton(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderCheckbox
	protected function renderCheckbox(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderCombobox
	protected function renderCombobox(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderData
	protected function renderData(PageElement $e)
	{
		//FIXME implement (attachment?)
	}


	//PDFFormat::renderDialog
	protected function renderDialog(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderEntry
	protected function renderEntry(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderExpander
	protected function renderExpander(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderFileChooser
	protected function renderFileChooser(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderForm
	protected function renderForm(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderFrame
	protected function renderFrame(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderHbox
	protected function renderHbox(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderHtmledit
	protected function renderHtmledit(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderHtmlview
	protected function renderHtmlview(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderIconview
	protected function renderIconview(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderImage
	protected function renderImage(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderInline
	protected function renderInline(PageElement $e)
	{
		$text = $e->getProperty('text');

		if($text !== FALSE)
			PDF_show($this->pdf, $text);
	}


	//PDFFormat::renderLabel
	protected function renderLabel(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderLink
	protected function renderLink(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderMenubar
	protected function renderMenubar(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderPage
	protected function renderPage(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderProgress
	protected function renderProgress(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderRadioButton
	protected function renderRadioButton(PageElement $e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderStatusbar
	protected function renderStatusbar(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderTextview
	protected function renderTextview(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderTitle
	protected function renderTitle(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderToolbar
	protected function renderToolbar(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderTreeview
	protected function renderTreeview(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderVbox
	protected function renderVbox(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//private
	//properties
	private $pdf;
}

?>
