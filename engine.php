<?php
//Copyright 2004 Pierre Pronchery
//This file is part of DaPortal
//
//DaPortal is free software; you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation; either version 2 of the License, or
//(at your option) any later version.
//
//DaPortal is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with DaPortal; if not, write to the Free Software
//Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA



//check url
if(eregi("engine.php", $_SERVER["REQUEST_URI"]))
{
	header("Location: index.php");
	exit(1);
}


//include system modules
require("system/config.php");
require("system/sql.php");
if(sql_connect($dbhost, $dbport, $dbname, $dbuser, $dbpassword) != 0)
{
	require_once("system/raw.php");
	raw_require("html/sql.html");
	return 1;
}
require("system/login.php");


function engine_module($module, $action)
{
	require_once("system/module.php");
	if(!ereg("^[a-z]*$", $module))
	{
		print("\t\t<h1>Invalid module</h1>
\t</body>
</html>\n");
		return 1;
	}
	//FIXME layout
	require_once("system/page.php");
	page_include("top");
	$res = module_include($module, $action);
	page_include("bottom");
	print("\t</body>
</html>\n");
	return $res;
}


function engine_invalid()
{
	require_once("system/raw.php");
	raw_require("html/xhtml.html");
	raw_require("html/invalid.html");
	return 1;
}


function engine_page($page)
{
	require_once("system/page.php");
	if($page == "" || !ereg("^[a-z]+$", $page))
		$page = "index";
	$res = page_include($page);
	print("\t</body>
</html>\n");
}


function engine_post()
{
	require_once("system/module.php");
	if(($module = $_POST["module"]) == "")
		return 1;
	if(($action = $_POST["action"]) == "")
		return 1;
	if(!ereg("^[a-z]+$", $module)
			|| ($res = sql_query("select moduleid from daportal_modules where modulename='$module'")) == FALSE)
	{
		require_once("system/raw.php");
		raw_require("html/xhtml.html");
		raw_require("html/invalid.html");
		return 1;
	}
	global $moduleid;
	$moduleid = $res[0]["moduleid"];
	return module_process($module, $action);
}


//process requests
if($_SERVER['REQUEST_METHOD'] == "POST")
	return engine_post();
if($_SERVER['REQUEST_METHOD'] != "GET")
	return engine_invalid();
require_once("system/raw.php");
raw_require("html/xhtml.html");
print("\t<head>
\t\t<title>DaPortal</title>
\t\t<link type=\"text/css\" rel=\"stylesheet\" href=\"themes/dark.css\"/>
\t</head>
\t<body>\n");
if($_GET["module"] != "")
	return engine_module($_GET['module'], $_GET['action']);
return engine_page($_GET["page"]);


?>
