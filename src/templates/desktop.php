<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//DesktopTemplate
class DesktopTemplate extends Template
{
	//protected
	//methods
	//DesktopTemplate::match
	protected function match(Engine $engine)
	{
		return 0;
	}


	//DesktopTemplate::attach
	protected function attach(Engine $engine)
	{
		parent::attach($engine);
	}


	//DesktopTemplate::render
	public function render(Engine $engine = NULL, PageElement $page = NULL)
	{
		//FIXME implement
		return $page;
	}
}

?>
