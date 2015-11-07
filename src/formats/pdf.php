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
	protected function renderBlock($e)
	{
		if(($text = $e->getProperty('text')) === FALSE)
			$text = '';
		PDF_continue_text($this->pdf, $text);
	}


	//PDFFormat::renderButton
	protected function renderButton($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderCheckbox
	protected function renderCheckbox($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderCombobox
	protected function renderCombobox($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderData
	protected function renderData($e)
	{
		//FIXME implement (attachment?)
	}


	//PDFFormat::renderDialog
	protected function renderDialog($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderEntry
	protected function renderEntry($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderExpander
	protected function renderExpander($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderFileChooser
	protected function renderFileChooser($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderForm
	protected function renderForm($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderFrame
	protected function renderFrame($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderHbox
	protected function renderHbox($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderHtmledit
	protected function renderHtmledit($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderHtmlview
	protected function renderHtmlview($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderIconview
	protected function renderIconview($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderImage
	protected function renderImage($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderInline
	protected function renderInline($e)
	{
		$text = $e->getProperty('text');

		if($text !== FALSE)
			PDF_show($this->pdf, $text);
	}


	//PDFFormat::renderLabel
	protected function renderLabel($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderLink
	protected function renderLink($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderMenubar
	protected function renderMenubar($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderPage
	protected function renderPage($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderProgress
	protected function renderProgress($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderRadioButton
	protected function renderRadioButton($e)
	{
		$this->renderInline($e);
	}


	//PDFFormat::renderStatusbar
	protected function renderStatusbar($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderTextview
	protected function renderTextview($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderTitle
	protected function renderTitle($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderToolbar
	protected function renderToolbar($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderTreeview
	protected function renderTreeview($e)
	{
		$this->renderBlock($e);
	}


	//PDFFormat::renderVbox
	protected function renderVbox($e)
	{
		$this->renderBlock($e);
	}


	//private
	//properties
	private $pdf;
}

?>
