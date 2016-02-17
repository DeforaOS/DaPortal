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


//functions
function html(Engine $engine, $html, $expected = FALSE, $class = 'HTML')
{
	if(($html = $class::filter($engine, $html)) === FALSE)
		return FALSE;
	if($expected !== FALSE && $html != $expected)
	{
		print("Obtained:\n$html\n");
		print("Expected:\n$expected\n");
		return FALSE;
	}
	return TRUE;
}

function test(Engine $engine)
{
	$html = '<html><head><title>Title</title></head><body>'
		.'<h1>Title</h1><p>Some text.</p><time>Now</time>'
		.'</body></html>';

	if(html($engine, $html, '<h1>Title</h1><p>Some text.</p>Now') === FALSE)
		exit(2);
	if(html($engine, $html, '<h1>Title</h1><p>Some text.</p>'
		.'<time>Now</time>', 'HTML5') === FALSE)
		exit(3);
}

test($engine);
exit(0);

?>
