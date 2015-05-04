<?php //$Id$
//Copyright (c) 2012-2015 Pierre Pronchery <khorben@defora.org>
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



//bindtextdomain
if(!function_exists('bindtextdomain'))
{
	function bindtextdomain($domain, $directory)
	{
		//XXX should not fail and return a string instead
		return NULL;
	}
}


//gettext
if(!function_exists('gettext'))
{
	function gettext($text)
	{
		return $text;
	}
}


//setlocale
if(!function_exists('setlocale'))
{
	function setlocale($category, $locale)
	{
		return FALSE;
	}
}


//textdomain
if(!function_exists('textdomain'))
{
	function textdomain($domain)
	{
		//XXX should not fail and return a string instead
		return NULL;
	}
}


//_
if(!function_exists('_'))
{
	function _($text)
	{
		return gettext($text);
	}
}

?>
