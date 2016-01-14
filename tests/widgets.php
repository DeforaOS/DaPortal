<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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
//TODO:
//- correct the path to the data in <base>



require_once('./tests.php');

//force HTML5 output
$config->set('format', 'backend', 'html5');

$page = new Page(array('title' => 'Widgets'));

//titles
$page->append('title', array('text' => 'Title (level 1)'));
$vbox1 = $page->append('vbox');
$vbox1->append('title', array('text' => 'Title (level 2)'));
$vbox2 = $vbox1->append('vbox');
$vbox2->append('title', array('text' => 'Title (level 3)'));

//expander
$expander = $vbox2->append('expander', array('text' => 'Expander: '));
$expander->append('label', array('text' => 'Inside the expander'));

//form
$form = $vbox2->append('form');
$form->append('entry', array('text' => 'Entry: ',
		'placeholder' => 'Placeholder'));
$form->append('textview', array('text' => 'Text editor: ',
		'value' => 'Text viewer (and editor)'));
$form->append('htmledit', array('text' => 'HTML editor: ',
		'value' => '<h1>HTML viewer <small>and editor</small></h1>'));
$form->append('button', array('text' => 'Button'));
$form->append('button', array('type' => 'reset', 'text' => 'Reset'));
$form->append('button', array('type' => 'submit', 'text' => 'Submit'));

//treeview
$columns = array('title' => 'Title', 'col1' => 'Header 1',
	'col2' => 'Header 2', 'col3' => 'Header 3');
$view = $vbox1->append('treeview', array('columns' => $columns));
$view->append('row', array('title' => 'Title column', 'col1' => 'Column 1',
		'col2' => 'Column 2', 'col3' => 'Column 3'));

//statusbar
$footer = $page->append('statusbar');
$footer->append('label', array('text' => 'Status bar (footer)'));

$response = new PageResponse($page);
$engine->render($response);

exit(0);

?>
