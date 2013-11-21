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



if(chdir('../src') === FALSE)
	exit(2);
require_once('./system/config.php');
require_once('./engines/cli.php');
require_once('./system/module.php');

global $config;
$config = new Config;
$config->set('database', 'backend', 'sqlite3');
$config->set('database::sqlite3', 'filename', '../tests/sqlite.db3');
$engine = new CLIEngine;

?>
