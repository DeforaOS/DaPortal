<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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



//NewsModule
class NewsModule extends ContentModule
{
	//public
	//methods
	//essential
	//NewsModule::NewsModule
	public function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('News') : FALSE;
		parent::__construct($id, $name, $title);
		$this->content_class = 'NewsContent';
		//translations
		$this->text_content_admin = _('News administration');
		$this->text_content_list_title = _('News list');
		$this->text_content_list_title_by = _('News by');
		$this->text_content_list_title_by_group = _('News by group');
		$this->text_content_submit_content = _('Submit news');
		$this->text_content_title = _('News');
	}


	//useful
	//NewsModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		if($internal)
			return parent::call($engine, $request, $internal);
		switch($request->getAction())
		{
			case 'rss':
				//for backward compatibility
				return $this->callRSS($engine, $request);
		}
		return parent::call($engine, $request, $internal);
	}


	//calls
	//NewsModule::callRSS
	protected function callRSS($engine, $request)
	{
		$request->setType('application/rss+xml');
		return $this->callHeadline($engine, $request);
	}
}

?>
