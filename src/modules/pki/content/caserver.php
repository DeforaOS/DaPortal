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



//CAServerPKIContent
class CAServerPKIContent extends PKIContent
{
	//public
	//methods
	//essential
	//CAServerPKIContent::CAServerPKIContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		//translations
		$this->text_content_by = _('CA server from');
		$this->text_content_list_title = _('CA servers');
		$this->text_more_content = _('More CA servers...');
		$this->text_submit = _('New CA servers...');
	}


	//protected
	static protected $class = 'CAServerPKIContent';
	static protected $list_order = 'title ASC';
	//queries
	//FIXME implement
	//IN:	module_id
	static protected $caserver_query_list = '';
	//IN:	module_id
	static protected $caserver_query_list_count = '';
	//IN:	module_id
	//	user_id
	static protected $caserver_query_list_user = '';
	//IN:	module_id
	//	user_id
	static protected $caserver_query_list_user_count = '';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $caserver_query_load = '';
}

?>
