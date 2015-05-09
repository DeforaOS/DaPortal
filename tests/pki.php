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



require_once('./tests.php');


//pki
function pki($engine, $module)
{
	global $config;

	if($config->set('module::pki', 'root', getcwd().'/../tests/pki')
			=== FALSE)
		return 2;

	//authenticate (as administrator)
	$user = new User($engine, 1, 'admin');
	if(($res = $user->authenticate($engine, 'password')) === FALSE
			|| $engine->setCredentials($res) === FALSE)
		return 3;

	//create a CA
	$args = array('title' => 'Test CA', 'country' => 'CO',
		'state' => 'State', 'locality' => '', 'organization' => '',
		'section' => '', 'email' => 'root@localhost',
		'days' => 3650, 'keysize' => 1024);
	$request = new Request('pki', 'submit', FALSE, FALSE, $args);
	$request->setIdempotent(FALSE);
	$response = $engine->process($request);
	$engine->render($response);
	if($response->getCode() != 0)
		return 4;
	//XXX guessing the content ID
	if(($ca = CAPKIContent::load($engine, $module, 4, 'Test CA')) === FALSE)
		return 5;

	//create a child CA
	$args = array('title' => 'Test child CA', 'country' => 'CO',
		'state' => 'State', 'locality' => '', 'organization' => '',
		'section' => '', 'email' => 'root@localhost',
		'days' => 365, 'keysize' => 512);
	$request = $ca->getRequest('submit', $args);
	$request->setIdempotent(FALSE);
	$response = $engine->process($request);
	$engine->render($response);
	if($response->getCode() != 0)
		return 6;

	return 0;
}


function pki_cleanup()
{
	$files = array('cacert.csr', 'cacert.pem', 'index.txt',
		'index.txt.attr', 'index.txt.old',
		'newcerts/01.pem', //XXX rename like the title
		'openssl.cnf', 'private/cakey.pem', 'serial', 'serial.old');
	$directories = array('certs', 'crl', 'newcerts', 'newreqs', 'private');

	foreach($files as $f)
	{
		_cleanupUnlink(getcwd().'/../tests/pki/Test child CA/'.$f);
		_cleanupUnlink(getcwd().'/../tests/pki/Test CA/'.$f);
	}
	foreach($directories as $d)
	{
		_cleanupRmdir(getcwd().'/../tests/pki/Test child CA/'.$d);
		_cleanupRmdir(getcwd().'/../tests/pki/Test CA/'.$d);
	}
	_cleanupRmdir(getcwd().'/../tests/pki/Test child CA');
	_cleanupRmdir(getcwd().'/../tests/pki/Test CA');
	_cleanupRmdir(getcwd().'/../tests/pki');
}

function _cleanupRmdir($directory)
{
	if(!file_exists($directory))
		return TRUE;
	return rmdir($directory);
}

function _cleanupUnlink($file)
{
	if(!file_exists($file))
		return TRUE;
	return unlink($file);
}


pki_cleanup();
$module = Module::load($engine, 'pki');
$ret = pki($engine, $module);
exit($ret);

?>
