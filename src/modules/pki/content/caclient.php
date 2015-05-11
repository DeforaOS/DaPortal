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



//CAClientPKIContent
class CAClientPKIContent extends PKIContent
{
	//public
	//methods
	//essential
	//CAClientPKIContent::CAClientPKIContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		$this->fields['parent'] = 'Parent CA';
		//translations
		$this->text_content_by = _('CA client from');
		$this->text_content_list_title = _('CA clients');
		$this->text_more_content = _('More CA clients...');
		$this->text_submit = _('New CA client...');
	}


	//useful
	//CAClientPKIContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = static::$caclient_query_insert;

		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$error = _('Could not insert the CA client');
		$args = array('caclient_id' => $this->getID(),
			'parent' => $this->get('parent') ?: NULL,
			'country' => $this->get('country'),
			'state' => $this->get('state'),
			'locality' => $this->get('locality'),
			'organization' => $this->get('organization'),
			'section' => $this->get('section'),
			'cn' => $this->get('cn'),
			'email' => $this->get('email'));
		if($database->query($engine, $query, $args)
				=== FALSE)
			return FALSE;
		return TRUE;
	}


	//protected
	static protected $class = 'CAClientPKIContent';
	static protected $list_order = 'title ASC';
	//queries
	//IN:	caclient_id
	//	parent
	//	country
	//	state
	//	locality
	//	organization
	//	section
	//	cn
	//	email
	static protected $caclient_query_insert = 'INSERT INTO daportal_caclient
		(caclient_id, parent, country, state, locality, organization,
		section, cn, email) VALUES (:caclient_id, :parent, :country,
		:state, :locality, :organization, :section, :cn, :email)';
	//IN:	module_id
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_caclient
		WHERE daportal_content_public.content_id
		=daportal_caclient.caclient_id
		AND module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_caclient
		WHERE daportal_content_public.content_id
		=daportal_caclient.caclient_id
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
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_caclient
		WHERE daportal_content_public.content_id
		=daportal_caclient.caclient_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_enabled, daportal_caclient
		WHERE daportal_content_enabled.content_id
		=daportal_caclient.caclient_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public, parent,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_enabled, daportal_caclient
		WHERE daportal_content_enabled.content_id
		=daportal_caclient.caclient_id
		AND module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND content_id=:content_id";
	//IN:	module_id
	//	title
	//	parent
	static protected $query_load_by_title_parent = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public, parent,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_caclient
		WHERE daportal_content_public.content_id
		=daportal_caclient.caclient_id
		AND module_id=:module_id AND title=:title AND parent=:parent';
	//IN:	module_id
	//	title
	static protected $query_load_by_title_parent_null = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public, parent,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_caclient
		WHERE daportal_content_public.content_id
		=daportal_caclient.caclient_id
		AND module_id=:module_id AND title=:title AND parent IS NULL';
}

?>
