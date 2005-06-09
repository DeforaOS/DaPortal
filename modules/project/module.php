<?php
//modules/project/module.php
//FIXME license
//FIXME hide attic option



//check url
if(!ereg('/index.php$', $_SERVER['PHP_SELF']))
	exit(header('Location: ../../index.php'));


function _project_toolbar($id)
{
	include('toolbar.tpl');
}


function project_admin($args)
{
	global $user_id;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error('Permission denied');
	print('<h1><img src="modules/project/icon.png" alt=""/> Projects administration</h1>'."\n");
	$projects = _sql_array('SELECT content_id AS id, name, title AS desc'
			.', username AS admin, enabled'
			.', daportal_content.user_id AS user_id'
			.' FROM daportal_content, daportal_user'
			.', daportal_project'
			.' WHERE daportal_content.user_id=daportal_user.user_id'
			.' AND daportal_content.content_id'
			.'=daportal_project.project_id'
			.' ORDER BY name ASC;');
	if(!is_array($projects))
		return _error('Could not list projects');
	$count = count($projects);
	for($i = 0; $i < $count; $i++)
	{
		$projects[$i]['name'] = _html_safe_link($projects[$i]['name']);
		$projects[$i]['admin'] = '<a href="index.php?module=project'
				.'&amp;action=list'
				.'&amp;user_id='.$projects[$i]['user_id'].'">'
				._html_safe_link($projects[$i]['admin'])
				.'</a>';
		$projects[$i]['desc'] = _html_safe($projects[$i]['desc']);
		$projects[$i]['module'] = 'project';
		$projects[$i]['action'] = 'update';
		$projects[$i]['icon'] = 'modules/project/icon.png';
		$projects[$i]['thumbnail'] = 'modules/project/icon.png';
		$projects[$i]['enabled'] = ($projects[$i]['enabled'] == 't')
			? 'enabled' : 'disabled';
		$projects[$i]['enabled'] = '<img src="modules/admin/'
			.$projects[$i]['enabled'].'" alt="'
			.$projects[$i]['enabled'].'"/>';
	}
	$toolbar = array();
	$toolbar[] = array('title' => 'New project',
			'icon' => 'modules/project/icon.png',
			'link' => 'index.php?module=project&action=new');
	_module('explorer', 'browse_trusted', array(
			'class' => array('enabled' => '',
					'admin' => 'Administrator',
					'desc' => 'Description'),
			'toolbar' => $toolbar,
			'view' => 'details',
			'entries' => $projects));
}


function project_browse($args)
{
	$project = _sql_array('SELECT name, cvsroot'
			.' FROM daportal_content, daportal_project'
			." WHERE project_id='".$args['id']."'"
			.' AND daportal_content.content_id'
			.'=daportal_project.project_id;');
	if(!is_array($project) || count($project) != 1)
		return _error('Invalid project');
	$project = $project[0];
	_project_toolbar($args['id']);
	if(strlen($project['cvsroot']) == 0)
	{
		print('<h1><img src="modules/project/icon.png" alt=""/> '
			._html_safe($project['name']).' CVS</h1>'."\n");
		return _info('This project does not have a CVS repository', 1);
	}
	if(!ereg('^[a-zA-Z0-9., /]+$', $project['cvsroot'])
			|| ereg('\.\.', $project['cvsroot']))
		return _error('Invalid CVSROOT', 1);
	if(!isset($args['file']))
		return _browse_dir($args['id'], $project['name'],
				$project['cvsroot'], '');
	$file = stripslashes($args['file']);
	if(!ereg('^[a-zA-Z0-9., /]+$', $file) || ereg('\.\.', $file))
		return _error('Invalid path', 1);
	$filename = '/Apps/CVS/DeforaOS/'.$project['cvsroot'].'/'.$file;
	if(is_dir($filename))
		return _browse_dir($args['id'], $project['name'],
				$project['cvsroot'], $file);
	else if(file_exists($filename))
		return _browse_file($args['id'], $project['name'],
				$project['cvsroot'], $file, $args['revision']);
	_error($filename, 1);
	_browse_dir($args['id'], $project['name'], $project['cvsroot'], '');
}

function _browse_dir($id, $project, $cvsroot, $filename)
{
	print('<h1><img src="modules/project/icon.png" alt=""/> '
			._html_safe($project).' CVS: '
			._html_safe($filename).'</h1>'."\n");
	//FIXME un-hardcode locations (invoke the cvs executable instead?)
	$path = '/Apps/CVS/DeforaOS/'.$cvsroot.'/'.$filename;
	if(($dir = @opendir($path)) == FALSE)
		return _error('Could not open CVS repository', 1);
	$dirs = array();
	$files = array();
	while($de = readdir($dir))
	{
		if($de == '.' || $de == '..')
			continue;
		if(is_dir($path.'/'.$de))
			$dirs[] = $de;
		else
			$files[] = $de;
	}
	closedir($dir);
	sort($dirs);
	sort($files);
	$entries = array();
	foreach($dirs as $d)
	{
		$name = _html_safe_link($d);
		$name = '<a href="index.php?module=project&amp;action=browse'
				.'&amp;id='.$id.'&amp;file='.$filename.'/'
				.$name.'">'.$name.'</a>';
		$entries[] = array('name' => $name,
				'icon' => 'modules/project/folder.png',
				'thumbnail' => 'modules/project/folder.png');
	}
	foreach($files as $f)
	{
		unset($rcs);
		//FIXME maybe use -h and -z among other options
		exec('rlog "'.str_replace('"', '\"', $path.'/'.$f).'"', $rcs);
		_info('rlog "'.str_replace('"', '\"', $path.'/'.$f).'"', 0);
		for($i = 0, $count = count($rcs); $i < $count; $i++)
			_info($i.': '.$rcs[$i], 0);
		$file = _html_safe_link($filename.'/'.$f);
		$name = _html_safe(substr($rcs[2], 14));
		$name = '<a href="index.php?module=project&amp;action=browse'
				.'&amp;id='.$id.'&amp;file='.$file.'">'
				.$name.'</a>';
		$revision = _html_safe(substr($rcs[12], 9));
		$revision = '<a href="index.php?module=project'
				.'&amp;action=browse&amp;id='.$id
				.'&amp;file='.$file.'&amp;revision='.$revision
				.'">'.$revision.'</a>';
		$date = _html_safe(substr($rcs[13], 6, 19));
		$author = substr($rcs[13], 36);
		$author = substr($author, 0, strspn($author,
				'abcdefghijklmnopqrstuvwxyz'
				.'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
				.'0123456789'));
		//FIXME cache this info
		$author_id = _sql_single('SELECT user_id FROM daportal_user'
				." WHERE username='".addslashes($author)."';");
		$author = _html_safe_link($author);
		if(is_numeric($author_id))
			$author = '<a href="index.php?module=user&amp;id='
					.$author_id.'">'.$author.'</a>';
		$message = _html_safe($rcs[14]);
		//FIXME choose icon depending on the file type
		$entries[] = array('name' => $name,
				'icon' => 'modules/project/default.png',
				'thumbnail' => 'modules/project/default.png',
				'revision' => $revision,
				'date' => $date,
				'author' => $author,
				'message' => $message);
	}
	_module('explorer', 'browse_trusted', array('entries' => $entries,
			'class' => array('revision' => 'Revision',
					'date' => 'Date',
					'author' => 'Author',
					'message' => 'Message'),
			'view' => 'details'));
}

function _browse_file($id, $project, $cvsroot, $filename, $revision)
	//FIXME if revision is specified and valid:
	//- display file content
	//else:
	//- list revisions in an explorer
	//- allow diff requests
	//also think about:
	//- downloads
	//- creating archives
	//other ideas:
	//- timeline
{
	$path = '/Apps/CVS/DeforaOS/'.$cvsroot.'/'.$filename;
	//FIXME maybe use -h and -z among other options
	exec('rlog "'.str_replace('"', '\"', $path).'"', $rcs);
	_info('rlog "'.str_replace('"', '\"', $path).'"', 0);
	print('<h1><img src="modules/project/icon.png" alt=""/> '
			._html_safe($project).' CVS: '
			._html_safe(substr($rcs[2], 14)).'</h1>'."\n");
	print('<pre>');
	for($i = 0, $count = count($rcs); $i < $count; $i++)
		print(_html_safe($i.': '.$rcs[$i])."\n");
	print('</pre>'."\n");
}


function project_bug_display($args)
{
	if(!is_numeric($args['id']))
		return _error('Invalid bug ID', 1);
	$bug = _sql_array('SELECT daportal_content.content_id as content_id'
			.', daportal_bug.bug_id AS id, timestamp, title'
			.', content, name, username'
			.' FROM daportal_content, daportal_bug, daportal_user'
			.', daportal_project'
			." WHERE enabled='t'"
			.' AND daportal_content.content_id=daportal_bug.bug_id'
			.' AND daportal_content.user_id=daportal_user.user_id'
			." AND bug_id='".$args['id']."';");
	if(!is_array($bug) || count($bug) != 1)
		return _error('Unable to display bug', 1);
	$title = '#'.$bug['id'].': '.$bug['title'];
	$bug = $bug[0];
}


function project_bug_list($args)
{
	print('<h1><img src="modules/project/bug.png" alt=""/> Bug reports</h1>'."\n");
	$where = '';
	if(strlen($args['project']))
		$where.=" AND daportal_project.name='".$args['project']."'";
	if(strlen($args['username']))
		$where.=" AND daportal_user.username='".$args['username']."'";
	if(strlen($args['state']))
		$where.=" AND daportal_bug.state='".$args['state']."'";
	if(strlen($args['type']))
		$where.=" AND daportal_bug.type='".$args['type']."'";
	if(strlen($args['priority']))
		$where.=" AND daportal_bug.priority='".$args['priority']."'";
	include('bug_list_filter.tpl');
	/* FIXME */
	$bugs = _sql_array('SELECT daportal_content.content_id as content_id'
			.', bug_id AS id, timestamp AS date, title AS name'
			.', content, daportal_project.name AS project, username'
			.' FROM daportal_content, daportal_bug, daportal_user'
			.', daportal_project'
			." WHERE enabled='t'"
			.' AND daportal_content.content_id=daportal_bug.bug_id'
			.' AND daportal_content.user_id=daportal_user.user_id'
			.$where
			.' ORDER BY date DESC;');
	if(!is_array($bugs))
		return _error('Unable to list bugs', 1);
	for($i = 0, $count = count($bugs); $i < $count; $i++)
		$bugs[$i]['id'] = '#'.$bugs[$i]['id'];
	$toolbar = array();
	$toolbar[] = array('icon' => 'modules/project/bug.png',
		'title' => 'Report a bug',
		'link' => 'index.php?module=project&action=bug_new');
	_module('explorer', 'browse', array('entries' => $bugs,
			'class' => array('id' => '',
					'project' => 'Project',
					'date' => 'Date',
					'state' => 'State',
					'type' => 'Type',
					'priority' => 'Priority'),
			'view' => 'details',
			'toolbar' => $toolbar));
}


function project_bug_new($args)
{
	include('bug_update.tpl');
}


function project_default($args)
{
	if(isset($args['id']))
		return project_display($args);
	include('default.tpl');
}


function project_display($args)
{
	global $user_id;

	require_once('system/user.php');
	$project = _sql_array('SELECT name, title, content AS description'
			.', enabled, daportal_content.user_id, username'
			.' FROM daportal_content, daportal_project'
			.', daportal_user'
			." WHERE content_id='".$args['id']."'"
			.' AND daportal_content.user_id=daportal_user.user_id'
			.' AND daportal_content.content_id'
			.'=daportal_project.project_id;');
	if(!is_array($project) || count($project) != 1)
		return _error('Unable to display project');
	$project = $project[0];
	if($project['enabled'] != 't' && !_user_admin($user_id))
		return include('project_submitted.tpl');
	$title = $project['name'];
	//FIXME display members + administrator in an explorer
	$project['members'] = _sql_array('SELECT daportal_user.user_id AS id'
			.', username AS name'
			.' FROM daportal_project_user, daportal_user'
			." WHERE project_id='".$args['id']."'"
			.' AND daportal_project_user.user_id'
			.'=daportal_user.user_id;');
	_project_toolbar($args['id']);
	include('project_display.tpl');
}


function project_download($args)
{
	include('download.tpl');
}


function project_insert($args)
{
	global $user_id;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error('Permission denied');
	require_once('system/content.php');
	if(($id = _content_insert($args['title'], $args['content'])) == FALSE)
		return _error('Unable to insert project content');
	if(!_sql_query('INSERT INTO daportal_project (project_id, name) VALUES'
			."('$id', '".$args['name']."');"))
		return _error('Unable to insert project');
	project_display(array('id' => $id));
}


function project_installer($args)
{
	include('installer.tpl');
}


function project_list($args)
{
	$title = 'Projects list';
	$level = 1;
	$where = '';
	if(isset($args['user_id']) && ($username = _sql_single('SELECT username'
			." FROM daportal_user WHERE user_id='".$args['user_id']
			."';")) != FALSE)
	{
		$title = 'Projects by '.$username;
		$level = 2;
		$where = " AND daportal_content.user_id='".$args['user_id']."'";
	}
	print('<h'.$level.'><img src="modules/project/icon.png" alt=""/> '
			._html_safe($title).'</h'.$level.'>'."\n");
	$projects = _sql_array('SELECT content_id AS id, name, title AS desc'
			.', username AS admin'
			.' FROM daportal_content, daportal_user'
			.', daportal_project'
			." WHERE daportal_content.enabled='1'"
			.' AND daportal_content.user_id=daportal_user.user_id'
			.' AND daportal_content.content_id'
			.'=daportal_project.project_id'
			.$where
			.' ORDER BY name ASC;');
	if(!is_array($projects))
		return _error('Could not list projects');
	$count = count($projects);
	for($i = 0; $i < $count; $i++)
	{
		$projects[$i]['module'] = 'project';
		$projects[$i]['action'] = 'display';
		$projects[$i]['icon'] = 'modules/project/icon.png';
		$projects[$i]['thumbnail'] = 'modules/project/icon.png';
	}
	$toolbar = array();
	$toolbar[] = array('title' => 'New project',
			'icon' => 'modules/project/icon.png',
			'link' => 'index.php?module=project&action=new');
	_module('explorer', 'browse', array(
			'class' => array('admin' => 'Administrator',
					'desc' => 'Description'),
			'toolbar' => $toolbar,
			'view' => 'details',
			'entries' => $projects));
}


function project_new($args)
{
	global $user_id;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error('Permission denied');
	include('project_update.tpl');
}


function project_package($args)
{
	include('package.tpl');
}


function project_update($args)
{
	//FIXME TODO
	global $user_id;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error('Permission denied');
	include('project_update.tpl');
}

?>
