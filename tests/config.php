<?php
//Copyright (c) 2014 Pierre Pronchery <khorben@defora.org>
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


//functions
$config = new Config;
if($config->get(FALSE, FALSE) !== FALSE)
	exit(3);
if($config->set(FALSE, 'test1', 'test2') === FALSE)
	exit(4);
if($config->get(FALSE, 'test1') != 'test2')
	exit(5);
if($config->set('test3', 'test4', 'test5') === FALSE)
	exit(4);
if($config->get('test3', 'test4') != 'test5')
	exit(6);
$config->reset();
if($config->get('test3', 'test4') !== FALSE)
	exit(7);
$filename = '../doc/daportal.conf';
//for OBJDIR support
if(($objdir = getenv('OBJDIR')) !== FALSE)
	$filename = $objdir.$filename;
if($config->load($filename) !== TRUE)
	exit(8);
if($config->get('defaults', 'charset') != 'utf-8')
	exit(9);
exit(0);

?>
