<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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



//BugReplyProjectContent
class BugReplyProjectContent extends ContentMulti
{
	//public
	//methods
	//BugReplyProjectContent::displayContent
	public function displayContent(Engine $engine, Request $request)
	{
		$text = HTML::format($engine, $this->getContent($engine));
		return new PageElement('htmlview', array('text' => $text));
	}


	//BugReplyProjectContent::listByBugID
	static public function listByBugID(Engine $engine, Module $module,
			$bug_id, $limit = FALSE, $offset = FALSE,
			$order = FALSE)
	{
		$ret = array();
		$class = static::$class;

		if(($res = static::_listByBugID($engine, $module, $bug_id,
				$order, $limit, $offset)) === FALSE)
			return FALSE;
		foreach($res as $r)
			$ret[] = new $class($engine, $module, $r);
		return $ret;
	}

	static protected function _listByBugID(Engine $engine, Module $module,
			$bug_id, $limit, $offset, $order)
	{
		$query = static::$query_list.' AND bug_id=:bug_id';
		$args = array('module_id' => $module->getID(),
			'bug_id' => $bug_id);

		return static::query($engine, $query, $args, $order, $limit,
				$offset);
	}


	//protected
	//properties
	static protected $class = 'BugReplyProjectContent';
	//queries
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT
		daportal_content_enabled.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		bug_id, state, type, priority, assigned
		FROM daportal_content_enabled, daportal_bug_reply
		WHERE daportal_content_enabled.content_id
		=daportal_bug_reply.content_id
		AND module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND daportal_content_enabled.content_id=:content_id";
	//IN:	module_id
	static protected $query_list = 'SELECT
		daportal_content_public.content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		bug_id, state, type, priority, assigned
		FROM daportal_content_public, daportal_bug_reply
		WHERE daportal_content_public.content_id
		=daportal_bug_reply.content_id
		AND module_id=:module_id';
}

?>
