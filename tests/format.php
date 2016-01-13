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


//variables
$html5 = array(
	array('type' => 'entry', 'attributes' => array(
		'name' => 'entry', 'text' => 'Entry', 'value' => 'Value'
	), 'expected' => "<!DOCTYPE html>
<html>
	<head>
		<meta charset=\"utf-8\"/>
		<base href=\"/\"/>
		<link rel=\"stylesheet\" href=\"themes/DaPortal.css\" title=\"DaPortal\"/>
		<link rel=\"alternate stylesheet\" href=\"themes/DeforaOS.css\" title=\"DeforaOS\"/>
		<link rel=\"alternate stylesheet\" href=\"themes/khorben.css\" title=\"khorben\"/>
		<link rel=\"alternate stylesheet\" href=\"themes/EdgeBSD.css\" title=\"EdgeBSD\"/>
		<style type=\"text/css\"><!-- @import url('icons/gnome.css'); //--></style>
	</head>
	<body>
		<div class=\"entry\"><span class=\"label\">Entry</span><input class=\"entry\" type=\"text\" name=\"entry\" value=\"Value\"/></div>
	</body>
</html>"),
	array('type' => 'page', 'attributes' => FALSE, 'expected' => "<!DOCTYPE html>
<html>
	<head>
		<meta charset=\"utf-8\"/>
		<base href=\"/\"/>
		<link rel=\"stylesheet\" href=\"themes/DaPortal.css\" title=\"DaPortal\"/>
		<link rel=\"alternate stylesheet\" href=\"themes/DeforaOS.css\" title=\"DeforaOS\"/>
		<link rel=\"alternate stylesheet\" href=\"themes/khorben.css\" title=\"khorben\"/>
		<link rel=\"alternate stylesheet\" href=\"themes/EdgeBSD.css\" title=\"EdgeBSD\"/>
		<style type=\"text/css\"><!-- @import url('icons/gnome.css'); //--></style>
	</head>
	<body>
	</body>
</html>")
);


//functions
function render(Engine $engine, Format $format, PageElement $page,
		$expected = FALSE)
{
	ob_start();
	$format->render($engine, $page);
	$obtained = ob_get_contents();
	ob_end_clean();
	if($expected === FALSE)
		print($page->getType().': Obtained "'.$obtained."\"\n");
	else if($obtained != $expected)
	{
		print($page->getType().': Expected "'.$expected."\"\n");
		print($page->getType().': Obtained "'.$obtained."\"\n");
		return 2;
	}
	return 0;
}

$ret = 0;
$config->set('format', 'backend', 'html5');
$format = Format::attachDefault($engine);
foreach($html5 as $t)
{
	$page = new PageElement($t['type'], $t['attributes']);
	$ret |= render($engine, $format, $page, $t['expected']);
}
exit($ret);

?>
