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



//MultiContent
class MultiContent extends Content
{
	//public
	//methods
	//useful
	//MultiContent::displayToolbar
	public function displayToolbar($engine, $request)
	{
		$credentials = $engine->getCredentials();
		$module = $this->getModule();

		if($this->type === FALSE)
			return parent::displayToolbar($engine, $request);
		//FIXME code duplication
		$toolbar = new PageElement('toolbar');
		if($credentials->isAdmin($engine))
		{
			$r = $module->getRequest('admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		if($module->canSubmit($engine, FALSE, $this))
		{
			$r = $module->getRequest('submit', array(
					'type' => $this->type));
			$toolbar->append('button', array('request' => $r,
					'stock' => 'new',
					'text' => $this->text_submit_content));
		}
		if($this->getID() !== FALSE)
		{
			if(!$this->isPublic() && $this->canPublish($engine,
					FALSE, $this))
			{
				$r = $this->getRequest('publish');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'publish',
						'text' => $this->text_publish));
			}
			if($this->canUpdate($engine, FALSE, $this))
			{
				$r = $this->getRequest('update');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'update',
						'text' => $this->text_update));
			}
		}
		//FIXME implement
		return $toolbar;
	}


	//MultiContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		$database = $engine->getDatabase();

		if($database->transactionBegin($engine) === FALSE)
			return FALSE;
		if(($ret = parent::save($engine, $request, $error)) === FALSE)
			$database->transactionRollback($engine);
		else if($database->transactionCommit($engine) === FALSE)
			return FALSE;
		return $ret;
	}


	//protected
	//methods
	//accessors
	//MultiContent::getType
	protected function getType()
	{
		return $this->type;
	}


	//MultiContent::setType
	protected function setType($type)
	{
		$this->type = $type;
	}


	//private
	//properties
	private $type = FALSE;
}

?>