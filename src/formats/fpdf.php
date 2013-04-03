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



require_once('./system/format.php');
require('./formats/fpdf/fpdf.php');


//FPDFFormat
class FPDFFormat extends FormatElements
{
	//methods
	//essential
	//FPDFFormat::match
	protected function match($engine, $type = FALSE)
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
	protected function attach($engine, $type = FALSE)
	{
	}


	//public
	//methods
	//rendering
	//FPDFFormat::render
	public function render($engine, $page, $filename = FALSE)
	{
		$user = $engine->getCredentials();
		//XXX obtain the full name instead
		$author = $user->getUsername();
		$title = $page->getProperty('title');

		$this->pdf = new FPDF();
		$this->pdf->addPage();
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
	protected function renderBlock($e)
	{
		if(($text = $e->getProperty('text')) === FALSE)
			$text = '';
		$this->pdf->Ln();
		$this->pdf->Write(5, $text);
		$this->renderChildren($e);
	}


	//FPDFFormat::renderButton
	protected function renderButton($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderCheckbox
	protected function renderCheckbox($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderCombobox
	protected function renderCombobox($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderDialog
	protected function renderDialog($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderEntry
	protected function renderEntry($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderFileChooser
	protected function renderFileChooser($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderForm
	protected function renderForm($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderFrame
	protected function renderFrame($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderHbox
	protected function renderHbox($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderHtmledit
	protected function renderHtmledit($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderHtmlview
	protected function renderHtmlview($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderIconview
	protected function renderIconview($e)
	{
		$this->renderTreeview($e);
	}


	//FPDFFormat::renderChildren
	protected function renderChildren($e)
	{
		$children = $e->getChildren();
		foreach($children as $c)
			$this->renderElement($c);
	}


	//FPDFFormat::renderImage
	protected function renderImage($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderInline
	protected function renderInline($e)
	{
		$text = $e->getProperty('text');

		if($text !== FALSE)
			$this->pdf->Write(5, $text);
		$this->renderChildren($e);
	}


	//FPDFFormat::renderLabel
	protected function renderLabel($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderLink
	protected function renderLink($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderMenubar
	protected function renderMenubar($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderPage
	protected function renderPage($e)
	{
		$this->renderChildren($e);
	}


	//FPDFFormat::renderProgress
	protected function renderProgress($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderRadioButton
	protected function renderRadioButton($e)
	{
		$this->renderInline($e);
	}


	//FPDFFormat::renderStatusbar
	protected function renderStatusbar($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderTextview
	protected function renderTextview($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderTitle
	protected function renderTitle($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderToolbar
	protected function renderToolbar($e)
	{
		$this->renderBlock($e);
	}


	//FPDFFormat::renderTreeview
	protected function renderTreeview($e)
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
				if(is_string($f) || is_integer($f))
					$this->pdf->Cell($width, $height, $f,
							1);
				else
					$this->renderElement($f);
			}
			$this->pdf->Ln();
		}
	}


	//FPDFFormat::renderVbox
	protected function renderVbox($e)
	{
		$this->renderBlock($e);
	}


	//private
	//properties
	private $pdf;
}

?>
