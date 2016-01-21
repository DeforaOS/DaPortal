<?php //$Id$
//Copyright (c) 2015-2016 Pierre Pronchery <khorben@defora.org>
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


//json
function json(Engine $engine, PageElement $page, $expected)
{
	$page = new PageResponse($page);
	//XXX really enforce JSONFormat
	$page->setType('application/json');
	ob_start();
	//XXX this could be more optimal
	$engine->render($page);
	$output = ob_get_contents();
	ob_end_clean();
	if($output == $expected)
		return 0;
	printf("Obtained: $output\n");
	return -1;
}

$ret = 0;
//default output
$ret |= (json($engine, new Page, "{}") == 0) ? 0 : 2;

$page = new PageElement('treeview');
$page->append('row', array('title' => 'Title'));
$ret |= (json($engine, $page, '{"rows1":[
	{
		"title":"Title"
	}
]}') == 0) ? 0 : 4;

exit($ret);

?>
