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



//PKIContent
abstract class PKIContent extends ContentMulti
{
	//public
	//methods
	//essential
	//PKIContent::PKIContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		//fields
		$this->fields['country'] = 'Country';
		$this->fields['state'] = 'State';
		$this->fields['locality'] = 'Locality';
		$this->fields['organization'] = 'Organization';
		$this->fields['section'] = 'Section';
		$this->fields['cn'] = 'Common Name';
		$this->fields['email'] = 'e-mail';
		parent::__construct($engine, $module, $properties);
	}


	//accessors
	//PKIContent::canSubmit
	public function canSubmit($engine, $request = FALSE, &$error = FALSE)
	{
		$class = static::$class;

		if(parent::canSubmit($engine, $request, $error) === FALSE)
			return FALSE;
		if($request !== FALSE)
		{
			if(($title = $request->get('title')) === FALSE
					|| strchr($title, '/') !== FALSE
					|| $title == '..')
			{
				$error = _('Invalid name');
				return FALSE;
			}
			if(($parent = $request->get('parent')) !== FALSE
					&& CAPKIContent::load($engine, $module,
							$parent) === FALSE)
			{
				$error = _('Invalid parent');
				return FALSE;
			}
			if($class::loadFromName($engine, $title, $parent)
					!== FALSE)
			{
				$error = _('Duplicate name');
				return FALSE;
			}
		}
		return TRUE;
	}


	//PKIContent::getRoot
	protected function getRoot($engine)
	{
		global $config;
		static $root = FALSE;

		if($root !== FALSE)
			return $root;
		$module = $this->getModule();
		$section = 'module::'.$module->getName(); //XXX
		if(($root = $config->get($section, 'root')) === FALSE)
			return $engine->log('LOG_ERR', 'The PKI root folder is'
					.' not configured');
		return $root;
	}


	//useful
	//PKIContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$vbox = new PageElement('vbox');

		$vbox->append('entry', array('name' => 'title',
				'text' => _('Name: '),
				'value' => $request->get('title')));
		$vbox->append('entry', array('name' => 'country',
				'text' => _('Country: '), 'size' => 2,
				'value' => $request->get('country')));
		$vbox->append('entry', array('name' => 'state',
				'text' => _('State: '),
				'value' => $request->get('state')));
		$vbox->append('entry', array('name' => 'locality',
				'text' => _('Locality: '),
				'value' => $request->get('locality')));
		$vbox->append('entry', array('name' => 'organization',
				'text' => _('Organization: '),
				'value' => $request->get('organization')));
		$vbox->append('entry', array('name' => 'section',
				'text' => _('Section: '),
				'value' => $request->get('section')));
		$vbox->append('entry', array('name' => 'cn',
				'text' => _('Common Name: '),
				'value' => $request->get('cn')));
		$vbox->append('entry', array('name' => 'email',
				'text' => _('e-mail: '),
				'value' => $request->get('email')));
		return $vbox;
	}

	protected function _formUpdate($engine, $request)
	{
		//FIXME really implement
		return parent::_formUpdate($engine, $request);
	}


	//abstract
	abstract static function loadFromName($engine, $module, $name,
			$parent = FALSE);
}

?>
