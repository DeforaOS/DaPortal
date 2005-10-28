<?php
//modules/news/module.php



//check url
if(!ereg('/index.php$', $_SERVER['PHP_SELF']))
	exit(header('Location: ../../index.php'));


//lang
$text['NEWS_ADMINISTRATION'] = 'News administration';
$text['NEWS_BY'] = 'News by ';
$text['NEWS_ON'] = 'on';
$text['NEWS_PREVIEW'] = 'News preview';
global $lang;
if($lang == 'fr')
{
	$text['NEWS_ADMINISTRATION'] = 'Administration des news';
	$text['NEWS_BY'] = 'Actualit�s par ';
	$text['NEWS_ON'] = 'le';
	$text['NEWS_PREVIEW'] = 'Aper�u de la d�p�che';
}
_lang($text);


function _news_insert($news)
{
	global $user_id;

	if(!$user_id)
		return _error(PERMISSION_DENIED);
	require_once('system/content.php');
	return _content_insert($news['title'], $news['content']);
}


function news_admin($args)
{
	global $user_id;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error(PERMISSION_DENIED);
	print('<h1><img src="modules/news/icon.png" alt=""/> '
		.NEWS_ADMINISTRATION.'</h1>'."\n");
	switch($args['sort'])
	{
		case 'username':
			$order = 'username';
			break;
		case 'enabled':
			$order = 'enabled';
			break;
		case 'name':
			$order = 'title';
			break;
		default:
		case 'date':
			$order = 'timestamp';
			break;
	}
	$res = _sql_array('SELECT content_id AS id, timestamp'
		.', daportal_content.enabled, title, content'
		.', daportal_content.user_id, username'
		.' FROM daportal_content, daportal_user'
		.', daportal_module'
		.' WHERE daportal_user.user_id=daportal_content.user_id'
		." AND daportal_module.name='news'"
		.' AND daportal_module.module_id'
		.'=daportal_content.module_id'
		.' ORDER BY '.$order.' DESC;');
	if(!is_array($res))
		return _error('Unable to list news');
	for($i = 0, $cnt = count($res); $i < $cnt; $i++)
	{
		$res[$i]['module'] = 'news';
		$res[$i]['apply_module'] = 'news';
		$res[$i]['action'] = 'modify';
		$res[$i]['apply_id'] = $res[$i]['id'];
		$res[$i]['icon'] = 'modules/news/icon.png';
		$res[$i]['thumbnail'] = 'modules/news/icon.png';
		$res[$i]['name'] = $res[$i]['title'];
		$res[$i]['username'] = '<a href="index.php?module=user&id='
				.$res[$i]['user_id'].'">'
				._html_safe_link($res[$i]['username'])
				.'</a>';
		$res[$i]['enabled'] = $res[$i]['enabled'] == 't' ?
			'enabled' : 'disabled';
		$res[$i]['enabled'] = '<img src="icons/16x16/'
				.$res[$i]['enabled'].'" alt="'
				.$res[$i]['enabled'].'" title="'
				.($res[$i]['enabled'] == 'enabled'
						? ENABLED : DISABLED)
				.'"/>';
		$res[$i]['date'] = strftime('%d/%m/%y %H:%M', strtotime(substr(
						$res[$i]['timestamp'], 0, 19)));
	}
	$toolbar = array();
	$toolbar[] = array('icon' => 'modules/news/icon.png',
			'title' => 'Submit news',
			'link' => 'index.php?module=news&action=submit');
	$toolbar[] = array();
	$toolbar[] = array('title' => DISABLE,
			'icon' => 'icons/16x16/disabled.png',
			'action' => 'disable');
	$toolbar[] = array('title' => ENABLE,
			'icon' => 'icons/16x16/enabled.png',
			'action' => 'enable');
	_module('explorer', 'browse_trusted', array(
				'class' => array('username' => AUTHOR,
					'enabled' => ENABLED,
					'date' => DATE),
				'module' => 'news',
				'action' => 'admin',
				'sort' => isset($args['sort']) ? $args['sort']
						: 'date',
				'view' => 'details',
				'toolbar' => $toolbar,
				'entries' => $res));
}


function news_default($args)
{
	if(isset($args['id']))
		return news_display(array('id' => $args['id']));
	return news_list($args);
}


function news_disable($args)
{
	_module('content', 'disable', array('id' => $args['id']));
}


function news_display($args)
{
	require_once('system/content.php');
	if(($news = _content_select($args['id'], 1)) == FALSE)
		return _error('Invalid news');
	if(($news['username'] = _sql_single('SELECT username'
			.' FROM daportal_user'
			." WHERE user_id='".$news['user_id']."';"))
			== FALSE)
		return _error('Invalid user');
	$long = 1;
	$title = $news['title'];
	$news['date'] = strftime(DATE_FORMAT,
			strtotime(substr($news['timestamp'], 0, 19)));
	include('news_display.tpl');
}


function news_enable($args)
{
	_module('content', 'enable', array('id' => $args['id']));
}


function news_list($args)
{
	$title = NEWS;
	$where = '';
	if(isset($args['user_id']) && ($username = _sql_single('SELECT username'
			.' FROM daportal_user'
			." WHERE user_id='".$args['user_id']."';")))
	{
		$title = NEWS_BY.$username;
		$where = " AND daportal_content.user_id='".$args['user_id']."'";
	}
	print('<h1><img src="modules/news/icon.png" alt=""/> '.$title.'</h1>'
			."\n");
	$res = _sql_array('SELECT content_id AS id, timestamp'
			.', title, content, daportal_content.user_id, username'
			.' FROM daportal_content, daportal_user'
			.', daportal_module'
			.' WHERE daportal_user.user_id=daportal_content.user_id'
			." AND daportal_content.enabled='1'"
			." AND daportal_module.name='news'"
			.' AND daportal_module.module_id'
			.'=daportal_content.module_id'
			.$where
			.' ORDER BY timestamp DESC;');
	if(!is_array($res))
		return _error('Unable to list news');
	if(!isset($username))
	{
		foreach($res as $news)
		{
			$news['date'] = strftime(DATE_FORMAT, strtotime(substr(
						$news['timestamp'], 0, 19)));
			include('news_display.tpl');
		}
		return;
	}
	for($i = 0, $cnt = count($res); $i < $cnt; $i++)
	{
		$res[$i]['module'] = 'news';
		$res[$i]['action'] = 'default';
		$res[$i]['icon'] = 'modules/news/icon.png';
		$res[$i]['thumbnail'] = 'modules/news/icon.png';
		$res[$i]['name'] = $res[$i]['title'];
		$res[$i]['date'] = strftime('%d/%m/%y %H:%M', strtotime(substr(
						$res[$i]['timestamp'], 0, 19)));
	}
	_module('explorer', 'browse', array(
				'class' => array('date' => 'Date'),
				'view' => 'details',
				'entries' => $res));
}


function news_modify($args)
{
	global $user_id;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error(PERMISSION_DENIED);
	if(!($module_id = _module_id('news')))
		return _error('Could not verify module');
	$news = _sql_array('SELECT content_id AS id, title, content'
			.' FROM daportal_content'
			." WHERE module_id='$module_id'"
			." AND content_id='".$args['id']."';");
	if(!is_array($news) || count($news) != 1)
		return _error('Unable to modify news');
	$news = $news[0];
	$title = 'Modification of news "'.$news['title'].'"';
	include('news_update.tpl');
}


function news_submit($news)
{
	global $user_id, $user_name;

	if(!$user_id)
		return _error(PERMISSION_DENIED);
	if(isset($news['preview']))
	{
		$long = 1;
		$title = NEWS_PREVIEW;
		$news['title'] = stripslashes($news['title']);
		$news['user_id'] = $user_id;
		$news['username'] = $user_name;
		$news['date'] = strftime(DATE_FORMAT);
		$news['content'] = stripslashes($news['content']);
		include('news_display.tpl');
		unset($title);
		return include('news_update.tpl');
	}
	if(!isset($news['send']))
	{
		$title = 'News submission';
		return include('news_update.tpl');
	}
	if(!_news_insert($news))
		return _error('Could not insert news');
	return include('news_posted.tpl');
}


function news_system($args)
{
	global $title;

	$title.=' - News';
}


function news_update($news)
{
	global $user_id, $user_name;

	require_once('system/user.php');
	if(!_user_admin($user_id))
		return _error(PERMISSION_DENIED);
	if(isset($news['preview']))
	{
		$long = 1;
		$title = NEWS_PREVIEW;
		$news['id'] = stripslashes($news['id']);
		$news['title'] = stripslashes($news['title']);
		$news['user_id'] = $user_id;
		$news['username'] = $user_name;
		$news['date'] = strftime(DATE_FORMAT);
		$news['content'] = stripslashes($news['content']);
		include('news_display.tpl');
		unset($title);
		return include('news_update.tpl');
	}
	require_once('system/content.php');
	if(!_content_update($news['id'], $news['title'], $news['content']))
		return _error('Could not update news');
	return news_display(array('id' => $news['id']));
}

?>
