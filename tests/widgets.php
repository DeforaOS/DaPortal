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



require_once('./tests.php');

$config->set('format', 'backend', 'html5');

$page = new Page(array('title' => 'Widgets'));
$page->append('title', array('text' => 'Title (level 1)'));
$vbox = $page->append('vbox');
$vbox->append('title', array('text' => 'Title (level 2)'));
$vbox = $vbox->append('vbox');
$vbox->append('title', array('text' => 'Title (level 3)'));
$form = $vbox->append('form');
$form->append('button', array('text' => 'Button'));
$form->append('button', array('type' => 'reset', 'text' => 'Reset'));
$form->append('button', array('type' => 'submit', 'text' => 'Submit'));
$footer = $page->append('statusbar');
$footer->append('label', array('text' => 'Status bar (footer)'));

$response = new PageResponse($page);
$engine->render($response);

exit(0);

?>
