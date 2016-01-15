<?php //$Id$
//Copyright (c) 2015-2016 Pierre Pronchery <khorben@defora.org>
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
	public function __construct(Engine $engine, Module $module,
			$properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		//translations
		static::$text_content = _('CA server');
		$this->text_content_by = _('CA server from');
		$this->text_content_list_title = _('CA servers');
		$this->text_more_content = _('More CA servers...');
		$this->text_submit = _('New CA servers...');
		$this->text_submit_content = _('New CA server');
	}


	//useful
	//CAServerPKIContent::save
	public function save(Engine $engine, Request $request = NULL,
			&$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert(Engine $engine, Request $request = NULL,
			&$error)
	{
		$parent = ($request->getID() !== FALSE)
			? CAPKIContent::load($engine, $this->getModule(),
				$request->getID(), $request->getTitle())
				: FALSE;
		$database = $engine->getDatabase();
		$query = static::$caserver_query_insert;

		//database transaction
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$error = _('Could not insert the CA server');
		$args = array('caserver_id' => $this->getID(),
			'parent' => ($parent !== FALSE) ? $parent->getID()
				: NULL,
			'country' => $request->get('country') ?: '',
			'state' => $request->get('state') ?: '',
			'locality' => $request->get('locality') ?: '',
			'organization' => $request->get('organization') ?: '',
			'section' => $request->get('section') ?: '',
			'email' => $request->get('email') ?: '',
			'signed' => FALSE);
		if($database->query($engine, $query, $args) === FALSE)
			return FALSE;

		//create certificate request
		if($this->createCertificate($engine, $request, $parent,
				$request->get('days'),
				$request->get('keysize'), $error) === FALSE)
			return $this->_insertCleanup($engine);

		//create signing request
		if($this->createSigningRequest($engine, $parent, $error)
				=== FALSE)
			return $this->_insertCleanup($engine);

		//sign directly if requested
		if($parent !== FALSE && $request->get('sign')
				&& $parent->sign($engine, $this, $error)
					=== FALSE)
			return FALSE;

		return TRUE;
	}

	protected function _insertCleanup(Engine $engine)
	{
		//FIXME really implement
		return FALSE;
	}


	//protected
	static protected $class = 'CAServerPKIContent';
	static protected $list_order = 'title ASC';
	//queries
	//IN:	caserver_id
	//	parent
	//	country
	//	state
	//	locality
	//	organization
	//	section
	//	email
	//	signed
	static protected $caserver_query_insert = 'INSERT INTO daportal_caserver
		(caserver_id, parent, country, state, locality, organization,
		section, email, signed) VALUES (:caserver_id, :parent,
		:country, :state, :locality, :organization, :section, :email,
		:signed)';
	//IN:	module_id
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_public, daportal_caserver
		WHERE daportal_content_public.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_public, daportal_caserver
		WHERE daportal_content_public.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id
		AND daportal_content_public.user_id=daportal_user_group.user_id
		AND daportal_user_group.group_id=daportal_group_enabled.group_id
		AND (daportal_user_group.group_id=:group_id
		OR daportal_content_public.group_id=:group_id)';
	//IN:	module_id
	//	user_id
	static protected $query_list_user = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_public, daportal_caserver
		WHERE daportal_content_public.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_enabled, daportal_caserver
		WHERE daportal_content_enabled.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, parent,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_enabled, daportal_caserver
		WHERE daportal_content_enabled.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND content_id=:content_id";
	//IN:	module_id
	//	title
	//	parent
	static protected $query_load_by_title_parent = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public, parent,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_public, daportal_caserver
		WHERE daportal_content_public.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id AND title=:title
		AND parent=:parent';
	//IN:	module_id
	//	title
	static protected $query_load_by_title_parent_null = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public, parent,
		country, state, locality, organization, section, email, signed
		FROM daportal_content_public, daportal_caserver
		WHERE daportal_content_public.content_id
		=daportal_caserver.caserver_id
		AND module_id=:module_id AND title=:title AND parent IS NULL';
}

?>
