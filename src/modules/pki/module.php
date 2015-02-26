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



//PKIModule
class PKIModule extends MultiContentModule
{
	//public
	//methods
	//essential
	//PKIModule::call
	public function call($engine, $request, $internal = 0)
	{
		if($internal)
			return parent::call($engine, $request, $internal);
		switch(($action = $request->getAction()))
		{
			//FIXME implement
		}
		return parent::call($engine, $request, $internal);
	}


	//protected
	//properties
	static protected $content_classes = array('ca' => 'CAPKIContent',
		'caclient' => 'CAClientPKIContent',
		'caserver' => 'CAServerPKIContent');


	//methods
	//PKIModule::PKIModule
	protected function __construct($id, $name, $title = FALSE)
	{
		$title = ($title === FALSE) ? _('PKI') : $title;
		$this->content_list_count = 20;
		parent::__construct($id, $name, $title);
	}


	//accessors
	//PKIModule::setContext
	protected function setContext($engine = FALSE, $request = FALSE,
			$content = FALSE)
	{
		parent::setContext($engine, $request, $content);
		switch($this->content_class)
		{
			case 'CAClientPKIContent':
				$this->text_content_admin
					= _('CA clients administration');
				$this->text_content_list_title
					= _('CA client list');
				$this->text_content_list_title_by
					= _('CA clients from');
				$this->text_content_list_title_by_group
					= _('CA clients from group');
				$this->text_content_submit_content
					= _('Client certificate request');
				break;
			case 'CAServerPKIContent':
				$this->text_content_admin
					= _('CA servers administration');
				$this->text_content_list_title
					= _('CA server list');
				$this->text_content_list_title_by
					= _('CA servers from');
				$this->text_content_list_title_by_group
					= _('CA servers from group');
				$this->text_content_submit_content
					= _('Server certificate request');
				break;
			default:
			case 'CAPKIContent':
				$this->text_content_admin
					= _('CAs administration');
				$this->text_content_list_title
					= _('Certificate Authority list');
				$this->text_content_list_title_by
					= _('Certificate Authorities from');
				$this->text_content_list_title_by_group
					= _('Certificate Authorities from group');
				$this->text_content_submit_content
					= _('New Certificate Authority');
				break;
		}
	}


	//calls
	//FIXME implement


	//PKIModule::callDefault
	protected function callDefault($engine, $request = FALSE)
	{
		if($request !== FALSE && $request->getID() !== FALSE)
			return $this->callDisplay($engine, $request);
		return $this->callList($engine, $request);
	}
}

?>
