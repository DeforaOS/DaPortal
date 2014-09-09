<?php //$Id$
//Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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



//Group
class Group
{
	//public
	//methods
	//essential
	//Group::Group
	public function __construct($engine, $gid, $groupname = FALSE)
	{
		$db = $engine->getDatabase();
		$query = $this->query_get_by_id;

		$args = array('group_id' => $gid);
		if($groupname !== FALSE)
		{
			if($engine instanceof HTTPFriendlyEngine)
			{
				//XXX workaround for friendly titles
				$query .= ' AND groupname '
					.$db->like().' :groupname';
				$groupname = str_replace('-', '_', $groupname);
			}
			else
				$query = $this->query_get_by_id_groupname;
			$args['groupname'] = $groupname;
		}
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return;
		$res = $res->current();
		$this->group_id = $res['id'];
		$this->groupname = $res['groupname'];
		$this->enabled = $db->isTrue($res['enabled']);
	}


	//accessors
	//Group::getGroupID
	public function getGroupID()
	{
		return $this->group_id;
	}


	//Group::getGroupname
	public function getGroupname()
	{
		return $this->groupname;
	}


	//Group::isEnabled
	public function isEnabled()
	{
		return $this->enabled;
	}


	//Group::setEnabled
	public function setEnabled($engine, $enabled)
	{
		$db = $engine->getDatabase();
		$query = $this->query_set_enabled;
		$args = array('group_id' => $this->group_id,
			'enabled' => $enabled ? TRUE : FALSE);

		return ($db->query($engine, $query, $args) !== FALSE);
	}


	//static
	//useful
	//Group::disable
	static public function disable($engine, $gid)
	{
		$db = $engine->getDatabase();
		$query = Group::$query_disable;
		$args = array('group_id' => $gid);

		return ($db->query($engine, $query, $args) !== FALSE)
			? TRUE : FALSE;
	}


	//Group::enable
	static public function enable($engine, $gid)
	{
		$db = $engine->getDatabase();
		$query = Group::$query_enable;
		$args = array('group_id' => $gid);

		return ($db->query($engine, $query, $args) !== FALSE)
			? TRUE : FALSE;
	}


	//Group::insert
	static public function insert($engine, $groupname, $enabled = FALSE,
			&$error = FALSE)
	{
		$db = $engine->getDatabase();
		$query = Group::$query_insert;
		$error = '';

		//FIXME really validate groupname
		if(!is_string($groupname) || strlen($groupname) == 0)
			$error .= _("The group's name is not valid\n");
		//FIXME verify that the groupname is unique
		if(strlen($error) > 0)
			return FALSE;
		$args = array('groupname' => $groupname,
			'enabled' => $enabled ? 1 : 0);
		$res = $db->query($engine, $query, $args);
		if($res === FALSE || ($gid = $db->getLastID($engine,
						'daportal_group', 'group_id'))
				=== FALSE)
		{
			$error = _('Could not insert the group');
			return FALSE;
		}
		$group = new Group($engine, $gid);
		if($group->getGroupID() === FALSE)
		{
			$error = _('Could not insert the group');
			return FALSE;
		}
		$error = '';
		return $group;
	}


	//Group::lookup
	static public function lookup($engine, $groupname, $group_id = FALSE)
	{
		$db = $engine->getDatabase();
		$query = Group::$query_get_by_groupname;
		$args = array('groupname' => $groupname);
		static $cache = array();

		if(isset($cache[$groupname]))
		{
			if($group_id !== FALSE
					&& $cache[$groupname]->getGroupID()
					!= $group_id)
				return FALSE;
			return $cache[$groupname];
		}
		if(($res = $db->query($engine, $query, $args)) === FALSE
				|| count($res) != 1)
			return FALSE;
		$res = $res->current();
		$cache[$groupname] = new Group($engine, $res['id'], $groupname);
		if($group_id !== FALSE && $cache[$groupname]->getGroupID()
				!= $group_id)
			return FALSE;
		return $cache[$groupname];
	}


	//private
	//properties
	private $group_id = 0;
	private $groupname = 'nogroup';
	private $enabled = FALSE;

	static private $timestamp_format = '%Y-%m-%d %H:%M:%S';

	//queries
	//IN:	group_id
	private $query_get_by_id = "SELECT group_id AS id, groupname, enabled
		FROM daportal_group
		WHERE daportal_group.enabled='1' AND group_id=:group_id";
	//IN:	group_id
	//	groupname
	private $query_get_by_id_groupname = "SELECT group_id AS id, groupname,
		enabled
		FROM daportal_group
		WHERE group_id=:group_id
		AND daportal_group.enabled='1' AND groupname=:groupname";
	//IN:	group_id
	//	enabled
	private $query_set_enabled = "UPDATE daportal_group
		SET enabled=:enabled
		WHERE group_id=:group_id";
	//static
	//IN:	group_id
	static private $query_disable = "UPDATE daportal_group
		SET enabled='0'
		WHERE group_id=:group_id";
	//IN:	group_id
	static private $query_enable = "UPDATE daportal_group
		SET enabled='1'
		WHERE group_id=:group_id";
	//IN:	groupname
	static private $query_get_by_groupname = "SELECT group_id AS id
		FROM daportal_group
		WHERE enabled='1' AND groupname=:groupname";
	//IN:	groupname
	//	enabled
	static private $query_insert = 'INSERT INTO daportal_group
		(groupname, enabled) VALUES (:groupname, :enabled)';
}

?>
