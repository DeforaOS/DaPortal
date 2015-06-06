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
		$this->fields['email'] = 'e-mail';
		$this->fields['parent'] = 'Parent CA';
		//let PKI content be public by default
		$this->setPublic(TRUE);
		$this->set('parent', FALSE);
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
					|| strlen($title) == 0
					|| strpos($title, '/') !== FALSE
					|| $title == '..')
			{
				$error = _('Invalid name');
				return FALSE;
			}
			if($this->getSubject($request) === FALSE)
			{
				$error = _('Invalid subject');
				return FALSE;
			}
			if(($parent = $request->get('parent')) !== FALSE
					&& CAPKIContent::load($engine, $module,
							$parent) === FALSE)
			{
				$error = _('Invalid parent');
				return FALSE;
			}
			if($class::loadFromName($engine, $this->getModule(),
					$title, $parent) !== FALSE)
			{
				$error = _('Duplicate name');
				return FALSE;
			}
		}
		return TRUE;
	}


	//PKIContent::getParent
	public function getParent($engine)
	{
		if(($parent = $this->get('parent')) === FALSE)
			return FALSE;
		return CAPKIContent::load($engine, $this->getModule(), $parent);
	}


	//PKIContent::getSubject
	public function getSubject($request = FALSE)
	{
		$ret = '';
		$fields = array('country' => 'C', 'state' => 'ST',
			'locality' => 'L', 'organization' => 'O',
			'section' => 'OU', 'cn' => 'CN',
			'email' => 'emailAddress');
		$s = ($request !== FALSE) ? $request : $this;

		foreach($fields as $field => $key)
		{
			$value = ($field == 'cn') ? $this->getTitle() //XXX
				: $s->get($field);
			if($value !== FALSE && strlen($value) > 0)
			{
				if(strchr($value, '/') !== FALSE)
					//XXX escape slashes instead?
					return FALSE;
				$ret.='/'.$key.'='.$value;
			}
		}
		return (strlen($ret) > 0) ? $ret : FALSE;
	}


	//useful
	//PKIContent::displayContent
	public function displayContent($engine, $request)
	{
		$parent = $this->getParent($engine);
		$columns = array('title' => '', 'value' => '');
		$fields = array('country' => _('Country: '),
			'state' => _('State: '), 'locality' => _('Locality: '),
			'organization' => _('Organization: '),
			'section' => _('Section: '),
			'email' => _('e-mail: '));

		$vbox = new PageElement('vbox');
		if($parent !== FALSE)
		{
			$expander = $vbox->append('expander', array(
				'title' => _('Parent CA')));
			$expander->append($parent->displayContent($engine,
				FALSE));
		}
		if($this->get('signed') === FALSE)
			$vbox->append('dialog', array('type' => 'warning',
					'text' => sprintf(
						_('This %s is not signed'),
						static::$text_content)));
		$view = $vbox->append('treeview', array('columns' => $columns));
		foreach($fields as $k => $v)
		{
			if(($value = $this->get($k)) === FALSE
					|| strlen($value) == 0)
				continue;
			$view->append('row', array('title' => $v,
					'value' => $value));
		}
		return $vbox;
	}


	//PKIContent::form
	public function form($engine, $request = FALSE)
	{
		return parent::form($engine, $request);
	}

	protected function _formSubmit($engine, $request)
	{
		$countries = array('AF' => _('Afghanistan'),
			'AX' => _('Åland'),
			'AB' => _('Albania'),
			'AL' => _('Algeria'),
			'AQ' => _('American Samoa'),
			'AN' => _('Andorra'),
			'AN' => _('Angola'),
			'AV' => _('Anguilla'),
			'AA' => _('Antarctica'),
			'AT' => _('Antigua and Barbuda'),
			'AG' => _('Argentina'),
			'AY' => _('Armenia'),
			'NU' => _('Aruba'),
			'AU' => _('Australia'),
			'OS' => _('Austria'),
			'AJ' => _('Azerbaijan'),
			'BA' => _('Bahamas'),
			'BN' => _('Bahrain'),
			'BW' => _('Bangladesh'),
			'BR' => _('Barbados'),
			'BY' => _('Belarus'),
			'BX' => _('Belgium'),
			'BH' => _('Belize'),
			'BJ' => _('Benin'),
			'BE' => _('Bermuda'),
			'BT' => _('Bhutan'),
			'BO' => _('Bolivia'),
			'BQ' => _('Bonaire, Sint Eustatius and Saba'),
			'BG' => _('Bosnia and Herzegovina'),
			'BC' => _('Botswana'),
			'BV' => _('Bouvet Island'),
			'BZ' => _('Brazil'),
			'IO' => _('British Indian Ocean Territory'),
			'BD' => _('Brunei Darussalam'),
			'BU' => _('Bulgaria'),
			'HV' => _('Burkina Faso'),
			'BI' => _('Burundi'),
			'KP' => _('Cambodia'),
			'CM' => _('Cameroon'),
			'CN' => _('Canada'),
			'CV' => _('Cape Verde'),
			'GC' => _('Cayman Islands'),
			'CE' => _('Central African Republic'),
			'CD' => _('Chad'),
			'CH' => _('Chile'),
			'CI' => _('China'),
			'KI' => _('Christmas Island'),
			'KK' => _('Cocos (Keeling) Islands'),
			'CO' => _('Colombia'),
			'IC' => _('Comoros'),
			'CG' => _('Congo (Brazzaville)'),
			'ZR' => _('Congo (Kinshasa)'),
			'KU' => _('Cook Islands'),
			'CS' => _('Costa Rica'),
			'IV' => _('Côte d\'Ivoire'),
			'RH' => _('Croatia'),
			'CU' => _('Cuba'),
			'UC' => _('Curaçao'),
			'CY' => _('Cyprus'),
			'CZ' => _('Czech Republic'),
			'DN' => _('Denmark'),
			'DJ' => _('Djibouti'),
			'DO' => _('Dominica'),
			'DR' => _('Dominican Republic'),
			'EQ' => _('Ecuador'),
			'EG' => _('Egypt'),
			'ES' => _('El Salvador'),
			'GQ' => _('Equatorial Guinea'),
			'ER' => _('Eritrea'),
			'EO' => _('Estonia'),
			'ET' => _('Ethiopia'),
			'FK' => _('Falkland Islands'),
			'FA' => _('Faroe Islands'),
			'FJ' => _('Fiji'),
			'FI' => _('Finland'),
			'FR' => _('France'),
			'FG' => _('French Guiana'),
			'PF' => _('French Polynesia'),
			'FS' => _('French Southern Lands'),
			'GO' => _('Gabon'),
			'GB' => _('Gambia'),
			'GG' => _('Georgia'),
			'DL' => _('Germany'),
			'GH' => _('Ghana'),
			'GI' => _('Gibraltar'),
			'GR' => _('Greece'),
			'GL' => _('Greenland'),
			'GD' => _('Grenada'),
			'MF' => _('Guadeloupe'),
			'GM' => _('Guam'),
			'GU' => _('Guatemala'),
			'GK' => _('Guernsey'),
			'GN' => _('Guinea'),
			'GW' => _('Guinea-Bissau'),
			'GY' => _('Guyana'),
			'HA' => _('Haiti'),
			'HM' => _('Heard and McDonald Islands'),
			'HO' => _('Honduras'),
			'HK' => _('Hong Kong'),
			'HU' => _('Hungary'),
			'IL' => _('Iceland'),
			'IN' => _('India'),
			'ID' => _('Indonesia'),
			'IR' => _('Iran'),
			'IQ' => _('Iraq'),
			'IE' => _('Ireland'),
			'IM' => _('Isle of Man'),
			'IS' => _('Israel'),
			'IY' => _('Italy'),
			'JM' => _('Jamaica'),
			'JP' => _('Japan'),
			'JE' => _('Jersey'),
			'JD' => _('Jordan'),
			'KZ' => _('Kazakhstan'),
			'KN' => _('Kenya'),
			'KB' => _('Kiribati'),
			'KR' => _('Korea, North'),
			'KO' => _('Korea, South'),
			'KW' => _('Kuwait'),
			'KG' => _('Kyrgyzstan'),
			'LA' => _('Laos'),
			'LV' => _('Latvia'),
			'LB' => _('Lebanon'),
			'LS' => _('Lesotho'),
			'LI' => _('Liberia'),
			'LY' => _('Libya'),
			'FL' => _('Liechtenstein'),
			'LT' => _('Lithuania'),
			'BX' => _('Luxembourg'),
			'MU' => _('Macau'),
			'MJ' => _('Macedonia'),
			'MG' => _('Madagascar'),
			'MW' => _('Malawi'),
			'MS' => _('Malaysia'),
			'MV' => _('Maldives'),
			'MI' => _('Mali'),
			'ML' => _('Malta'),
			'MH' => _('Marshall Islands'),
			'MR' => _('Martinique'),
			'MT' => _('Mauritania'),
			'MA' => _('Mauritius'),
			'MF' => _('Mayotte'),
			'MX' => _('Mexico'),
			'FM' => _('Micronesia'),
			'RM' => _('Moldova'),
			'MC' => _('Monaco'),
			'MO' => _('Mongolia'),
			'MJ' => _('Montenegro'),
			'MH' => _('Montserrat'),
			'MC' => _('Morocco'),
			'MZ' => _('Mozambique'),
			'BM' => _('Myanmar'),
			'NM' => _('Namibia'),
			'NW' => _('Nauru'),
			'NP' => _('Nepal'),
			'NL' => _('Netherlands'),
			'NC' => _('New Caledonia'),
			'NZ' => _('New Zealand'),
			'NK' => _('Nicaragua'),
			'NR' => _('Niger'),
			'NI' => _('Nigeria'),
			'NZ' => _('Niue'),
			'NF' => _('Norfolk Island'),
			'MY' => _('Northern Mariana Islands'),
			'NO' => _('Norway'),
			'OM' => _('Oman'),
			'PK' => _('Pakistan'),
			'PS' => _('Palau'),
			'PS' => _('Palestine'),
			'PM' => _('Panama'),
			'NG' => _('Papua New Guinea'),
			'PY' => _('Paraguay'),
			'PR' => _('Peru'),
			'PH' => _('Philippines'),
			'PT' => _('Pitcairn'),
			'PL' => _('Poland'),
			'PO' => _('Portugal'),
			'PU' => _('Puerto Rico'),
			'QT' => _('Qatar'),
			'RE' => _('Reunion'),
			'RO' => _('Romania'),
			'RS' => _('Russian Federation'),
			'RW' => _('Rwanda'),
			'TB' => _('Saint Barthélemy'),
			'HE' => _('Saint Helena'),
			'AT' => _('Saint Kitts and Nevis'),
			'LC' => _('Saint Lucia'),
			'RN' => _('Saint Martin (French part)'),
			'FP' => _('Saint Pierre and Miquelon'),
			'VC' => _('Saint Vincent and the Grenadines'),
			'ZM' => _('Samoa'),
			'SM' => _('San Marino'),
			'TP' => _('Sao Tome and Principe'),
			'SD' => _('Saudi Arabia'),
			'SG' => _('Senegal'),
			'YG' => _('Serbia'),
			'SC' => _('Seychelles'),
			'SL' => _('Sierra Leone'),
			'SR' => _('Singapore'),
			'NN' => _('Sint Maarten'),
			'SQ' => _('Slovakia'),
			'LJ' => _('Slovenia'),
			'SO' => _('Solomon Islands'),
			'SI' => _('Somalia'),
			'ZA' => _('South Africa'),
			'GS' => _('South Georgia and South Sandwich Islands'),
			'OD' => _('South Sudan'),
			'SP' => _('Spain'),
			'SB' => _('Sri Lanka'),
			'SU' => _('Sudan'),
			'SM' => _('Suriname'),
			'SJ' => _('Svalbard and Jan Mayen Islands'),
			'SV' => _('Swaziland'),
			'SN' => _('Sweden'),
			'SW' => _('Switzerland'),
			'SY' => _('Syria'),
			'RC' => _('Taiwan'),
			'TA' => _('Tajikistan'),
			'TN' => _('Tanzania'),
			'TH' => _('Thailand'),
			'TM' => _('Timor-Leste'),
			'TG' => _('Togo'),
			'TK' => _('Tokelau'),
			'TO' => _('Tonga'),
			'TD' => _('Trinidad and Tobago'),
			'TS' => _('Tunisia'),
			'TU' => _('Turkey'),
			'TR' => _('Turkmenistan'),
			'TI' => _('Turks and Caicos Islands'),
			'TV' => _('Tuvalu'),
			'UG' => _('Uganda'),
			'UR' => _('Ukraine'),
			'ER' => _('United Arab Emirates'),
			'UK' => _('United Kingdom'),
			'UM' => _('United States Minor Outlying Islands'),
			'US' => _('United States of America'),
			'UY' => _('Uruguay'),
			'UZ' => _('Uzbekistan'),
			'NV' => _('Vanuatu'),
			'VT' => _('Vatican City'),
			'VN' => _('Venezuela'),
			'VS' => _('Vietnam'),
			'VI' => _('Virgin Islands, British'),
			'VI' => _('Virgin Islands, U.S.'),
			'FW' => _('Wallis and Futuna Islands'),
			'WI' => _('Western Sahara'),
			'YE' => _('Yemen'),
			'ZB' => _('Zambia'),
			'ZW' => _('Zimbabwe'));
		$keysizes = array('' => 'Default', 1024 => 1024, 2048 => 2048,
			4096 => 4096);
		$days = array('' => 'Default', 365 => '1 year',
			730 => '2 years', 1095 => '3 years',
			1460 => '4 years', 2825 => '5 years',
			2190 => '6 years', 2555 => '7 years',
			2920 => '8 years', 3285 => '9 years',
			3650 => '10 years');
		$vbox = new PageElement('vbox');

		$vbox->append('entry', array('name' => 'title',
				'text' => _('Name: '),
				'placeholder' => _('Name'),
				'value' => $request->get('title')));
		$country = $vbox->append('combobox', array('name' => 'country',
				'editable' => TRUE,
				'text' => _('Country: '), 'size' => 2,
				'placeholder' => _('Country'),
				'value' => $request->get('country')));
		//countries
		asort($countries);
		foreach($countries as $value => $text)
			$country->append('label', array('text' => $text,
					'value' => $value));
		$vbox->append('entry', array('name' => 'state',
				'text' => _('State: '),
				'placeholder' => _('State'),
				'value' => $request->get('state')));
		$vbox->append('entry', array('name' => 'locality',
				'text' => _('Locality: '),
				'placeholder' => _('Locality'),
				'value' => $request->get('locality')));
		$vbox->append('entry', array('name' => 'organization',
				'text' => _('Organization: '),
				'placeholder' => _('Organization'),
				'value' => $request->get('organization')));
		$vbox->append('entry', array('name' => 'section',
				'text' => _('Section: '),
				'placeholder' => _('Section'),
				'value' => $request->get('section')));
		$vbox->append('entry', array('name' => 'email',
				'text' => _('e-mail: '),
				'placeholder' => _('e-mail'),
				'value' => $request->get('email')));
		//key size
		$keysize = $vbox->append('combobox', array('name' => 'keysize',
				'text' => _('Key size: '),
				'value' => $request->get('keysize')));
		foreach($keysizes as $value => $text)
			$keysize->append('label', array('text' => $text,
					'value' => $value));
		//expiration
		$expiration = $vbox->append('combobox', array('name' => 'days',
				'text' => _('Expiration: '),
				'value' => $request->get('days')));
		foreach($days as $value => $text)
			$expiration->append('label', array('text' => $text,
					'value' => $value));
		//signing
		if($request->getID() !== FALSE)
			$vbox->append('checkbox', array('name' => 'sign',
					'value' => $request->get('sign')
						? TRUE : FALSE,
					'text' => _('Sign')));
		return $vbox;
	}

	protected function _formUpdate($engine, $request)
	{
		//FIXME really implement
		return parent::_formUpdate($engine, $request);
	}


	//PKIContent::loadFromName
	static function loadFromName($engine, $module, $name, $parent = FALSE)
	{
		if(($res = static::_loadFromName($engine, $module, $name,
				$parent)) === FALSE)
			return FALSE;
		return static::loadFromProperties($engine, $module, $res);
	}

	static protected function _loadFromName($engine, $module, $name,
			$parent)
	{
		$database = $engine->getDatabase();
		$query = ($parent !== FALSE)
			? static::$query_load_by_title_parent
			: static::$query_load_by_title_parent_null;
		$args = array('module_id' => $module->getID(),
			'title' => $name);

		if($parent !== FALSE)
			$args['parent'] = $parent->getID();
		if(($res = $database->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		return $res->current();
	}


	//protected
	//methods
	//accessors
	//PKIContent::getRoot
	protected function getRoot($engine)
	{
		global $config;

		if($this->root !== FALSE)
			return $this->root;
		$module = $this->getModule();
		$section = 'module::'.$module->getName(); //XXX
		if(($this->root = $config->get($section, 'root')) === FALSE)
			return $engine->log('LOG_ERR', 'The PKI root folder is'
					.' not configured');
		return $this->root;
	}


	//CAPKIContent::getRootCA
	protected function getRootCA($engine, $parent = FALSE)
	{
		if($parent === FALSE)
			$parent = $this->getParent($engine);
		if($parent === FALSE)
			//FIXME add support for self-signed certificates
			return FALSE;
		return $parent->getRootCA($engine);
	}


	//useful
	//PKIContent::createCertificate
	protected function createCertificate($engine, $request = FALSE,
			$parent = FALSE, $days = FALSE, $keysize = FALSE,
			&$error = FALSE)
	{
		$root = $this->getRootCA($engine, $parent);
		$subject = $this->getSubject($request);

		//enforce reasonable defaults
		if($days === FALSE)
			$days = 365;
		if($keysize === FALSE)
			$keysize = 4096;
		//check parameters
		if($root === FALSE || $subject === FALSE || !is_numeric($days)
				|| !is_numeric($keysize))
		{
			$error = _('Invalid arguments to create certificate');
			return FALSE;
		}
		switch(static::$class)
		{
			case 'CAPKIContent':
				$x509 = ($parent !== FALSE) ? '' : ' -x509';
				$extensions = '';
				$keyout = $root.'/private/cakey.pem';
				$out = ($parent !== FALSE) ? $root.'/cacert.csr'
					: $root.'/cacert.pem';
				break;
			case 'CAClientPKIContent':
				$x509 = ' -x509';
				$extensions = ' -extensions usr_cert';
				$keyout = $root.'/private/'.$this->getTitle().'.key';
				$out = $root.'/newcerts/'.$this->getTitle().'.pem';
				break;
			case 'CAServerPKIContent':
				$x509 = ' -x509';
				$extensions = ' -extensions srv_cert';
				$keyout = $root.'/private/'.$this->getTitle().'.key';
				$out = $root.'/newcerts/'.$this->getTitle().'.pem';
				break;
			default:
				$error = _('Invalid class to create certificate');
				return FALSE;
		}
		$opensslcnf = $root.'/openssl.cnf';

		if(file_exists($keyout) || file_exists($out))
		{
			$error = _('Could not generate the certificate');
			return $engine->log('LOG_ERR',
					'Could not generate the certificate');
		}
		$days = ' -days '.escapeshellarg($days);
		$keysize = ' -newkey rsa:'.escapeshellarg($keysize);
		$cmd = 'openssl req -batch -nodes -new'.$x509.$days.$keysize
			.' -config '.escapeshellarg($opensslcnf).$extensions
			.' -keyout '.escapeshellarg($keyout)
			.' -out '.escapeshellarg($out)
			.' -subj '.escapeshellarg($subject)
			.' 2>&1'; //XXX avoid garbage on the standard error
		$res = -1;
		$engine->log('LOG_DEBUG', 'Executing: '.$cmd);
		exec($cmd, $output, $res);
		if($res != 0)
		{
			$error = _('Could not generate the certificate');
			return $engine->log('LOG_ERR',
					'Could not generate the certificate');
		}
		return TRUE;
	}


	//PKIContent::createSigningRequest
	protected function createSigningRequest($engine, $parent = FALSE,
			&$error = FALSE)
	{
		$root = $this->getRootCA($engine, $parent);

		//check parameters
		if($root === FALSE)
		{
			$error = _('Invalid arguments to signing request');
			return FALSE;
		}
		switch(static::$class)
		{
			case 'CAClientPKIContent':
			case 'CAServerPKIContent':
				$in = $root.'/newcerts/'.$this->getTitle().'.pem';
				$out = $root.'/newreqs/'.$this->getTitle().'.csr';
				$signkey = $root.'/private/'.$this->getTitle().'.key';
				break;
			default:
				$error = _('Invalid class to signing request');
				return FALSE;
		}
		$opensslcnf = $root.'/openssl.cnf';

		if(!file_exists($in) || file_exists($out)
				|| !file_exists($signkey))
		{
			$error = _('Could not generate the signing request');
			return $engine->log('LOG_ERR',
					'Could not generate the signing request');
		}
		$cmd = 'openssl x509 -x509toreq'
			.' -in '.escapeshellarg($in)
			.' -out '.escapeshellarg($out)
			.' -signkey '.escapeshellarg($signkey)
			.' 2>&1'; //XXX avoid garbage on the standard error
		$res = -1;
		$engine->log('LOG_DEBUG', 'Executing: '.$cmd);
		exec($cmd, $output, $res);
		if($res != 0)
		{
			$error = _('Could not generate the signing request');
			return $engine->log('LOG_ERR',
					'Could not generate the signing request');
		}
		return TRUE;
	}


	//PKIContent::sign
	protected function sign($engine, $content = FALSE, &$error = FALSE)
	{
		$parent = $this->getParent($engine);

		if($content !== FALSE || $parent === FALSE)
		{
			$error = _('Unsupported operation');
			return FALSE;
		}
		return $parent->sign($engine, $this, $error);
	}


	//private
	//properties
	private $root = FALSE;
}

?>
