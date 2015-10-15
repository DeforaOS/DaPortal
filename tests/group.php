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
if(($group = Group::lookup($engine, 'nogroup')) === FALSE
		|| $group->getGroupID() != 0)
	exit(2);
if($group->disable($engine) === FALSE
		|| $group->enable($engine) === FALSE)
	exit(3);
if(($groups = $group->listAll($engine)) === FALSE || count($groups) != 1)
	exit(4);
if(($groups = $group->listAll($engine, TRUE)) === FALSE || count($groups) != 1)
	exit(5);
if(($groups = $group->listAll($engine, FALSE)) === FALSE || count($groups) != 0)
	exit(6);
exit(0);

?>
