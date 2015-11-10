<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
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
if(($dir = opendir('../src/templates')) === FALSE)
	exit(2);
$ret = 0;
$page = new Page;
$dialog = new PageElement('dialog', array('type' => 'info',
		'text' => 'This is just a test'));
while(($de = readdir($dir)) !== FALSE)
{
	if(substr($de, -4) != '.php')
		continue;
	$name = substr($de, 0, -4);
	$config->set('template', 'backend', $name);
	if(($template = Template::attachDefault($engine)) === FALSE)
	{
		$engine->log('LOG_ERR',
				$template.': Could not attach template');
		$ret = 3;
		continue;
	}
	$template->render($engine, $page);
	$template->render($engine, $dialog);
}
closedir($dir);
exit($ret);

?>
