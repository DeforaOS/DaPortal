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



//CAPKIContent
class CAPKIContent extends PKIContent
{
	//public
	//methods
	//essential
	//CAPKIContent::CAPKIContent
	public function __construct($engine, $module, $properties = FALSE)
	{
		parent::__construct($engine, $module, $properties);
		//translations
		static::$text_content = _('Certificate Authority');
		$this->text_content_by = _('Certificate Authority from');
		$this->text_content_list_title = _('Certificate Authorities');
		$this->text_more_content = _('More Certificate Authorities...');
		$this->text_submit = _('New Certificate Authority...');
		$this->text_submit_content = _('New Certificate Authority');
	}


	//accessors
	//CAPKIContent::canSubmit
	public function canSubmit($engine, $request = FALSE, &$error = FALSE)
	{
		if(parent::canSubmit($engine, $request, $error) === FALSE)
			return FALSE;
		if($request !== FALSE)
		{
			$title = $request->get('title');

			//check for duplicates
			if(($root = $this->getRoot($engine)) === FALSE)
			{
				$error = _('Internal error');
				return FALSE;
			}
			if(file_exists($root))
			{
				$error = _('Duplicate CA');
				return FALSE;
			}
		}
		return TRUE;
	}


	//CAPKIContent::getRoot
	protected function getRoot($engine)
	{
		global $config;

		if($this->root !== FALSE)
			return $this->root;
		$module = $this->getModule();
		$section = 'module::'.$module->getName(); //XXX
		if(($root = $config->get($section, 'root')) === FALSE)
			return $engine->log('LOG_ERR', 'The PKI root folder is'
					.' not configured');
		$this->root = $root.'/'.$this->getTitle();
		return $this->root;
	}


	//useful
	//CAPKIContent::createRoot
	protected function createRoot($engine)
	{
		if(($root = $this->getRoot($engine)) === FALSE)
			return FALSE;
		//create directories as required
		$dirs = array('certs', 'crl', 'newcerts', 'newreqs', 'private');
		foreach($dirs as $d)
		{
			$directory = $root.'/'.$d;
			if($this->_rootDirectory($engine, $directory) === FALSE)
				return FALSE;
		}
		return TRUE;
	}

	protected function _rootDirectory($engine, $directory)
	{
		if(is_dir($directory) && is_readable($directory))
			return TRUE;
		if(mkdir($directory, 0700, TRUE) !== FALSE)
			return TRUE;
		return $engine->log('LOG_ERR', $directory.': Could not'
				.' create directory');
	}


	//CAPKIContent::displayToolbar
	public function displayToolbar($engine, $request = FALSE)
	{
		//XXX copied from Content
		$credentials = $engine->getCredentials();
		$action = ($request !== FALSE) ? $request->getAction() : FALSE;

		$toolbar = new PageElement('toolbar');
		if($credentials->isAdmin($engine))
		{
			$r = $this->getModule()->getRequest('admin');
			$toolbar->append('button', array('request' => $r,
					'stock' => 'admin',
					'text' => _('Administration')));
		}
		if($action != 'submit' && $this->getModule()->canSubmit($engine,
				FALSE, $this))
		{
			$types = array('ca' => $this->text_submit_content,
				'caserver' => _('New CA server'),
				'caclient' => _('New CA client'));
			foreach($types as $type => $text)
			{
				$r = $this->getRequest('submit',
						array('type' => $type));
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => $this->stock_submit,
						'text' => $text));
			}
		}
		if($this->getID() !== FALSE)
		{
			if($action != 'publish' && !$this->isPublic()
					&& $this->canPublish($engine, FALSE,
						$this))
			{
				$r = $this->getRequest('publish');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'publish',
						'text' => $this->text_publish));
			}
			if($action != 'update' && $this->canUpdate($engine,
					FALSE, $this))
			{
				$r = $this->getRequest('update');
				$toolbar->append('button', array(
						'request' => $r,
						'stock' => 'update',
						'text' => $this->text_update));
			}
		}
		return $toolbar;
	}


	//CAPKIContent::save
	public function save($engine, $request = FALSE, &$error = FALSE)
	{
		return parent::save($engine, $request, $error);
	}

	protected function _saveInsert($engine, $request, &$error)
	{
		$parent = ($request->getID() !== FALSE)
			? static::load($engine, $this->getModule(),
				$request->getID(), $request->getTitle())
				: FALSE;
		$database = $engine->getDatabase();
		$query = static::$ca_query_insert;

		//configuration
		if(($root = $this->getRoot($engine)) === FALSE)
		{
			$error = _('Internal error');
			return FALSE;
		}

		//database transaction
		if(parent::_saveInsert($engine, $request, $error) === FALSE)
			return FALSE;
		$error = _('Could not insert the CA');
		$args = array('ca_id' => $this->getID(),
			'parent' => ($parent !== FALSE) ? $parent->getID()
				: NULL,
			'country' => $request->get('country') ?: '',
			'state' => $request->get('state') ?: '',
			'locality' => $request->get('locality') ?: '',
			'organization' => $request->get('organization') ?: '',
			'section' => $request->get('section') ?: '',
			'cn' => $request->get('cn') ?: '',
			'email' => $request->get('email') ?: '',
			'signed' => FALSE);
		if($database->query($engine, $query, $args) === FALSE)
			return $this->_insertCleanup($engine);

		//directories
		if($this->createRoot($engine) === FALSE)
			return $this->_insertCleanup($engine, TRUE);

		//files
		if($this->_insertIndex($engine) === FALSE
				|| $this->_insertConfig($engine) === FALSE
				|| $this->_insertSerial($engine) === FALSE)
			return $this->_insertCleanup($engine, TRUE, TRUE);

		//certificate
		if($this->_insertCertificate($engine, $request, $parent)
				=== FALSE)
			return $this->_insertCleanup($engine, TRUE, TRUE, TRUE);
		if($parent !== FALSE && $parent->sign($engine, $this) === FALSE)
			return $this->_insertCleanup($engine, TRUE, TRUE, TRUE);

		return TRUE;
	}

	protected function _insertCertificate($engine, $request, $parent)
	{
		$root = $this->getRoot($engine);
		$x509 = ($parent !== FALSE) ? '' : ' -x509';
		$opensslcnf = $root.'/openssl.cnf';
		$days = $request->get('days');
		$key = $request->get('keysize');
		$keyout = $root.'/private/cacert.key';
		$out = ($parent !== FALSE) ? $root.'/cacert.csr'
			: $root.'/cacert.crt';
		$subject = $this->getSubject($request);

		$days = is_numeric($days)
			? ' -days '.escapeshellarg($days) : '';
		$key = is_numeric($key)
			? ' -newkey rsa:'.escapeshellarg($key) : '';
		$cmd = 'openssl req -batch -nodes -new'.$x509.$days.$key
			.' -config '.escapeshellarg($opensslcnf)
			.' -keyout '.escapeshellarg($keyout)
			.' -out '.escapeshellarg($out)
			.' -subj '.escapeshellarg($subject)
			.' 2>&1'; //XXX avoid garbage on the standard error
		$res = -1;
		$engine->log('LOG_DEBUG', 'Executing: '.$cmd);
		exec($cmd, $output, $res);
		return ($res == 0) ? TRUE : FALSE;
	}

	protected function _insertCleanup($engine, $directories = FALSE,
			$files = FALSE, $certificates = FALSE)
	{
		//FIXME really implement
		return FALSE;
	}

	protected function _insertConfig($engine)
	{
		$from = array("\\", "\"", "$");
		$to = array("\\\\", "\\\"", "\\$");
		$root = $this->getRoot($engine);
		$filename = $root.'/openssl.cnf';
		$module = $this->getModule()->getName();
		$template = 'modules/'.$module.'/openssl.cnf.in';

		if(($fp = fopen($filename, 'w')) === FALSE)
			return FALSE;
		ob_start();
		$home = str_replace($from, $to, $root);
		$title = str_replace($from, $to, $this->getTitle());
		$res = include($template);
		if($res !== FALSE)
			$res = fwrite($fp, ob_get_contents());
		ob_end_clean();
		if(fclose($fp) === FALSE || $res === FALSE)
		{
			unlink($filename);
			return FALSE;
		}
		return TRUE;
	}

	protected function _insertIndex($engine)
	{
		$root = $this->getRoot($engine);

		return touch($root.'/index.txt');
	}

	protected function _insertSerial($engine)
	{
		$root = $this->getRoot($engine);
		$filename = $root.'/serial';

		if(($fp = fopen($filename, 'w')) === FALSE)
			return FALSE;
		$res = fwrite($fp, "01\n");
		if(fclose($fp) === FALSE || $res === FALSE)
		{
			unlink($filename);
			return FALSE;
		}
		return TRUE;
	}

	protected function _saveUpdate($engine, $request, &$error)
	{
		$database = $engine->getDatabase();
		$query = static::$ca_query_update;
		$args = array('ca_id' => $this->getID(),
			'signed' => $this->get('signed') ? TRUE : FALSE);

		if(parent::_saveUpdate($engine, $request, $error) === FALSE)
			return FALSE;
		return ($database->query($engine, $query, $args) !== FALSE)
			? TRUE : FALSE;
	}


	//CAPKIContent::sign
	protected function sign($engine, $content, &$error = FALSE)
	{
		if($content instanceof CAPKIContent)
			return $this->_signCA($engine, $content);
		$error = _('Unsupported operation');
		return FALSE;
	}

	private function _signCA($engine, $ca, &$error)
	{
		$root = $this->getRoot($engine);
		$opensslcnf = $root.'/openssl.cnf';
		$caroot = $ca->getRoot($engine);

		if($root === FALSE || $caroot === FALSE)
		{
			$error = _('Internal error');
			return FALSE;
		}
		$cmd = 'openssl ca -batch'
			.' -config '.escapeshellarg($opensslcnf)
			.' -extensions v3_ca'
			.' -policy policy_anything'
			.' -out '.escapeshellarg($caroot.'/cacert.crt')
			.' -infiles '.escapeshellarg($caroot.'/cacert.csr');
		$res = -1;
		$engine->log('LOG_DEBUG', 'Executing: '.$cmd);
		exec($cmd, $output, $res);
		if($res != 0)
		{
			$error = _('Could not sign CA');
			return FALSE;
		}
		$ca->set('signed', TRUE);
		$ca->save($engine);
		return TRUE;
	}


	//protected
	//properties
	static protected $class = 'CAPKIContent';
	static protected $list_order = 'title ASC';
	//queries
	//IN:	ca_id
	//	parent
	//	country
	//	state
	//	locality
	//	organization
	//	section
	//	cn
	//	email
	//	signed
	static protected $ca_query_insert = 'INSERT INTO daportal_ca (
		ca_id, parent, country, state, locality, organization, section,
		cn, email, signed) VALUES (:ca_id, :parent, :country, :state,
		:locality, :organization, :section, :cn, :email, :signed)';
	//IN:	ca_id
	//	signed
	static protected $ca_query_update = 'UPDATE daportal_ca
		SET signed=:signed WHERE ca_id=:ca_id';
	//IN:	module_id
	static protected $query_list = 'SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_ca
		WHERE daportal_content_public.content_id=daportal_ca.ca_id
		AND module_id=:module_id';
	//IN:	module_id
	//	group_id
	static protected $query_list_group = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_public, daportal_ca
		WHERE daportal_content_public.content_id=daportal_ca.ca_id
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
		FROM daportal_content_public, daportal_ca
		WHERE daportal_content_public.content_id=daportal_ca.ca_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	static protected $query_list_user_private = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, cn, email
		FROM daportal_content_enabled, daportal_ca
		WHERE daportal_content_enabled.content_id=daportal_ca.ca_id
		AND module_id=:module_id
		AND user_id=:user_id';
	//IN:	module_id
	//	user_id
	//	content_id
	static protected $query_load = "SELECT content_id AS id, timestamp,
		module_id, module, user_id, username, group_id, groupname,
		title, content, enabled, public,
		country, state, locality, organization, section, cn, email,
		signed
		FROM daportal_content_enabled, daportal_ca
		WHERE daportal_content_enabled.content_id=daportal_ca.ca_id
		AND module_id=:module_id
		AND (public='1' OR user_id=:user_id)
		AND content_id=:content_id";
	//IN:	module_id
	//	title
	//	parent
	static protected $query_load_by_title_parent = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, cn, email,
		signed
		FROM daportal_content_public, daportal_ca
		WHERE daportal_content_public.content_id=daportal_ca.ca_id
		AND module_id=:module_id AND title=:title AND parent=:parent';
	//IN:	module_id
	//	title
	static protected $query_load_by_title_parent_null = 'SELECT content_id AS id,
		timestamp, module_id, module, user_id, username,
		group_id, groupname, title, content, enabled, public,
		country, state, locality, organization, section, cn, email,
		signed
		FROM daportal_content_public, daportal_ca
		WHERE daportal_content_public.content_id=daportal_ca.ca_id
		AND module_id=:module_id AND title=:title AND parent IS NULL';


	//private
	//properties
	private $root = FALSE;
}

?>
