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
require_once('./system/compat.php');


//main
global $_SERVER;
$hostname = gethostname();
$url = array("http://$hostname/dir1/dir2/dir3/index.php",
	"http://$hostname/dir1/dir2/dir3/index.php",
	"http://localhost/dir1/dir2/dir3/index.php/dir4/dir5/dir6/index.php/testmodule/testaction/32/Test%20title?arg1=test1&arg2=test2&arg3=test3%3Dtest4&arg5=test5",
	"http://localhost/dir1/dir2/dir3/index.php/dir4/dir5/dir6/index.php/testmodule/testaction/32/Test%20title?ids%5B123%5D=on&ids%5B125%5D=on",
	"http://localhost:8081/dir1/dir2/dir3/index.php/dir4/dir5/dir6/index.php/testmodule/testaction/32/Test%20title?ids%5B123%5D=on&ids%5B125%5D=on",
	"https://localhost/dir1/dir2/dir3/index.php/dir4/dir5/dir6/index.php/testmodule/testaction/32/Test%20title?ids%5B123%5D=on&ids%5B125%5D=on");


function _http($class)
{
	$res = array();

	$_SERVER['SCRIPT_NAME'] = '/dir1/dir2/dir3/index.php';
	$engine = new $class();
	$engine->attach();
	$res[] = $engine->getURL($engine->getRequest());

	$_SERVER['REQUEST_METHOD'] = 'GET';
	$engine = new $class();
	$engine->attach();
	$res[] = $engine->getURL($engine->getRequest());

	$_SERVER['PATH_INFO'] = '/dir4/dir5/dir6/index.php/testmodule/testaction/32/Test%20title';
	$_SERVER['QUERY_STRING'] = 'arg1=test1&arg2=test2&arg3=test3=test4&arg5=test5';
	$_SERVER['SERVER_NAME'] = 'localhost';
	$engine = new $class();
	$engine->attach();
	$res[] = $engine->getURL($engine->getRequest());

	//arrays
	$_SERVER['QUERY_STRING'] = 'ids[123]=on&ids[125]=on';
	$engine = new $class();
	$engine->attach();
	$res[] = $engine->getURL($engine->getRequest());

	//port
	$_SERVER['SERVER_PORT'] = 8081;
	$engine = new $class();
	$engine->attach();
	$res[] = $engine->getURL($engine->getRequest());

	//https
	$_SERVER['HTTPS'] = 'on';
	$_SERVER['SERVER_PORT'] = 443;
	$engine = new $class();
	$engine->attach();
	$res[] = $engine->getURL($engine->getRequest());

	return $res;
}

$ret = 0;
$res = _http('HTTPFriendlyEngine');
foreach($res as $o)
{
	$e = array_shift($url);
	if($o == $e)
	{
		print("$o\n");
		continue;
	}
	print("$o (expected: $e)\n");
	$ret = 2;
}

exit($ret);

?>
