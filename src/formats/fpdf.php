<?php //$Id$
//Copyright (c) 2013-2015 Pierre Pronchery <khorben@defora.org>
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



require('./formats/fpdf/fpdf.php');


//FPDFFormat
class FPDFFormat extends FormatElements
{
	//methods
	//essential
	//FPDFFormat::match
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


	//FPDFFormat::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
	}


	//public
	//methods
	//rendering
	//FPDFFormat::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
	{
		$user = $engine->getCredentials();
		//XXX obtain the full name instead
		$author = $user->getUsername();
		$title = $page->getProperty('title');

		$this->pdf = new FPDF();
		$this->pdf->AddPage();
		$this->pdf->SetFont('Arial', '', 12);
		$this->pdf->SetCreator('DaPortal');
		$this->pdf->SetAuthor($author);
		if($title !== FALSE)
			$this->pdf->SetTitle($title);
		//$this->renderElement($page);
		parent::render($engine, $page, $filename);
		if($filename !== FALSE)
			$this->pdf->Output($filename, 'F');
		else
			$this->pdf->Output();
		$this->pdf = FALSE;
	}


	//protected
	//methods
	//rendering
	//FPDFFormat::renderBlock
	protected function renderBlock(PageElement $e)
	{
		if(($text = $e->getProperty('text')) === FALSE)
			$text = '';
		$this->pdf->Ln();
		$this->pdf->Write(5, $text);
		$this->renderChildren($e);
	}


	//FPDFFormat::renderButton
	protected function renderButton(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderCheckbox
	protected function renderCheckbox(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderCombobox
	protected function renderCombobox(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderData
	protected function renderData(PageElement $e)
	{
		//FIXME implement (attachment?)
	}


	//FPDFFormat::renderDialog
	protected function renderDialog(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderEntry
	protected function renderEntry(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderExpander
	protected function renderExpander(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderFileChooser
	protected function renderFileChooser(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderForm
	protected function renderForm(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderFrame
	protected function renderFrame(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderHbox
	protected function renderHbox(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderHtmledit
	protected function renderHtmledit(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderHtmlview
	protected function renderHtmlview(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderIconview
	protected function renderIconview(PageElement $e)
	{
		$this->renderTreeview($e);
	}


	//FPDFFormat::renderChildren
	protected function renderChildren(PageElement $e)
	{
		$children = $e->getChildren();
		foreach($children as $c)
			$this->renderElement($c);
	}


	//FPDFFormat::renderImage
	protected function renderImage(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderInline
	protected function renderInline(PageElement $e)
	{
		$text = $e->getProperty('text');

		if($text !== FALSE)
			$this->pdf->Write(5, $text);
		$this->renderChildren($e);
	}


	//FPDFFormat::renderLabel
	protected function renderLabel(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderLink
	protected function renderLink(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderMenubar
	protected function renderMenubar(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderPage
	protected function renderPage(PageElement $e)
	{
		$this->renderChildren($e);
	}


	//FPDFFormat::renderProgress
	protected function renderProgress(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderRadioButton
	protected function renderRadioButton(PageElement $e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderStatusbar
	protected function renderStatusbar(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderTextview
	protected function renderTextview(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderTitle
	protected function renderTitle(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderToolbar
	protected function renderToolbar(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderTreeview
	protected function renderTreeview(PageElement $e)
	{
		$columns = $e->getProperty('columns');

		$this->pdf->Ln();
		if($columns === FALSE)
			$columns = array('icon' => '', 'label' => '');
		$width = 190 / count($columns);
		$height = 8;
		foreach($columns as $c => $d)
			$this->pdf->Cell($width, $height, $d, 1);
		$this->pdf->Ln();
		$children = $e->getChildren();
		foreach($children as $c)
		{
			if($c->getType() != 'row')
				continue;
			foreach($columns as $d => $e)
			{
				$f = $c->getProperty($d);
				if($f instanceof PageElement)
					$this->renderElement($f);
				else if(is_scalar($f))
					$this->pdf->Cell($width, $height, $f,
							1);
			}
			$this->pdf->Ln();
		}
	}


	//FPDFFormat::renderVbox
	protected function renderVbox(PageElement $e)
	{
		$this->renderBlock($e);
	}


	//private
	//properties
	private $pdf;
}

?>
