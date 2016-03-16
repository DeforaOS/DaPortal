<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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


//TestModule
class TestModule extends ContentModule
{
}


//test
function _test(Engine $engine, Module $module)
{
	//anonymous submissions are not allowed
	$request = $module->getRequest('submit');
	$request->setIdempotent(FALSE);
	$response = $engine->process($request);
	if(!($response instanceof ErrorResponse))
		return 1;
	$engine->log(LOG_INFO, $response->getContent());
	//authenticate
	$user = new User($engine, 1, 'admin');
	if(($credentials = $user->authenticate($engine, 'password')) === FALSE)
		return FALSE;
	$engine->setCredentials($credentials);
	//requests must not be idempotent
	$request = $module->getRequest('submit', array('enabled' => TRUE,
		'public' => TRUE));
	$request->setIdempotent(FALSE);
	$response = $engine->process($request);
	if(!($response instanceof PageResponse))
		return 2;
	//XXX obtain the new content
	if(($list = Content::listAll($engine, $module)) === FALSE)
		return 3;
	if(count($list) != 1)
		return 4;
	$content = $list[0];
	//update the content
	$title = 'New title';
	$request = $content->getRequest('update', array('title' => $title));
	$request->setIdempotent(FALSE);
	$response = $engine->process($request);
	if(!($response instanceof PageResponse))
		return 5;
	//XXX obtain the content again
	if(($list = Content::listAll($engine, $module)) === FALSE)
		return 3;
	if(count($list) != 1)
		return 4;
	$content = $list[0];
	if($content->getTitle() != $title)
		return 5;
	return 0;
}


//insert the module
$database = $engine->getDatabase();
$query = 'INSERT INTO daportal_module (name, enabled) VALUES (:name, :enabled)';
if($database->query(NULL, $query, array('name' => 'test', 'enabled' => TRUE))
		=== FALSE)
	exit(2);

if(($module = Module::load($engine, 'test')) === FALSE)
	exit(3);
if(($ret = _test($engine, $module)) != 0)
	exit($ret + 3);
exit(0);

?>
