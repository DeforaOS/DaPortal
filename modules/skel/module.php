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
if(eregi("module.php", $_SERVER["REQUEST_URI"]))
{
	header("Location: ../../index.php");
	exit(1);
}


function skel_admin()
{
	global $administrator;

	if($administrator != 1)
		return 0;
	return 0;
}


function skel_default()
{
	return 0;
}


function skel_dump()
{
	global $administrator;

	if($administrator != 1)
		return 0;
	return 0;
}


function skel_install()
{
	global $administrator;

	if($administrator != 1)
		return 0;
	return 0;
}


function skel_uninstall()
{
	global $administrator;

	if($administrator != 1)
		return 0;
	return 0;
}


switch($action)
{
	case "admin":
		return skel_admin();
	case "dump":
		return skel_dump();
	case "install":
		return skel_install();
	case "uninstall":
		return skel_uninstall();
	default:
		return skel_default();
}


?>
