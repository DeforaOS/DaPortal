<?php
//Copyright 2004 Pierre Pronchery
//This file is part of DaPortal
//
//DaPortal is free software; you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation; either version 2 of the License, or
//(at your option) any later version.
//
//DaPortal is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with DaPortal; if not, write to the Free Software
//Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA



//check url
if(eregi("module.php", $_SERVER["REQUEST_URI"]))
{
	header("Location: ../../index.php");
	exit(1);
}


function display($title, $author, $date, $content)
{
	print("\t\t<div class=\"news\">
\t\t\t<div class=\"news_title\">$title</div>
\t\t\t<div class=\"news_author\">Posted by <a href=\"index.php?module=user&user=$author\">$author</a>, on $date</div>
\t\t\t<div class=\"news_content\">$content</div>
\t\t</div>\n");
}


function news_admin()
{
	global $administrator, $moderator, $moduleid;

	if($administrator != 1 && $moderator != 1)
		return 0;
	print("\t\t<h1>News administration</h1>
\t\t<div>
\t\t\tYou can <a href=\"index.php?module=news&action=propose\">propose news</a>.
\t\t</div>\n");
	if(($res = sql_query("select newsid, title, username, date, enable from daportal_contents, daportal_news, daportal_users where moduleid='$moduleid' and contentid=newsid and userid=author;")) == FALSE)
	{
		print("\t\t<div>Not any news yet.</div>\n");
		return 0;
	}
	print("\t\t<table>
\t\t\t<tr>
\t\t\t\t<th>Title</th>
\t\t\t\t<th>Author</th>
\t\t\t\t<th>Date</th>
\t\t\t\t<th>Enabled</th>
\t\t\t<tr/>\n");
	while(sizeof($res) >= 1)
	{
		$author = $res[0]["username"];
		$enable = $res[0]["enable"] == "t";
		print("\t\t\t<tr>
\t\t\t\t<td><a href=\"index.php?module=news&action=view&newsid=".$res[0]["newsid"]."\">".$res[0]["title"]."</a></td>
\t\t\t\t<td><a href=\"index.php?module=user&user=$author\">$author</a></td>
\t\t\t\t<td>".$res[0]["date"]."</td>
\t\t\t\t<td>".($enable ? "yes" : "no")." <form method=\"post\" action=\"index.php\" style=\"display: inline\">
\t<input type=\"submit\" value=\"".($enable ? "Disable" : "Enable")."\">
\t<input type=\"hidden\" name=\"module\" value=\"news\">
\t<input type=\"hidden\" name=\"action\" value=\"moderate\">
\t<input type=\"hidden\" name=\"newsid\" value=\"".$res[0]["newsid"]."\">
\t<input type=\"hidden\" name=\"enable\" value=\"".($enable ? "0" : "1")."\">
</form></td>
\t\t\t<tr>\n");
		array_shift($res);
	}
	print("\t\t</table>\n");
	return 0;
}


function news_default()
{
	print("\t\t<h1>News</h1>\n");
	if(($res = sql_query("select title, username, date, content from daportal_news, daportal_contents, daportal_users where enable='1' and newsid=contentid and author=userid order by date desc;")) == FALSE)
	{
		print("\t\t<div>
\t\t\tNot any news yet.
\t\t</div>\n");
		return 0;
	}
	while(sizeof($res) >= 1)
	{
		display($res[0]["title"], $res[0]["username"], $res[0]["date"], $res[0]["content"]);
		array_shift($res);
	}
	return 0;
}


function news_install()
{
	global $administrator, $moduleid;

	if($administrator != 1)
		return 0;
//	sql_sequence_create("daportal_news_newsid_seq");
	sql_table_create("daportal_news", "(
	newsid integer,
	author integer,
	date date NOT NULL DEFAULT ('now'),
	enable bool NOT NULL DEFAULT '0',
	FOREIGN KEY (newsid) REFERENCES daportal_contents (contentid),
	FOREIGN KEY (author) REFERENCES daportal_users (userid)
)");
	return 0;
}


function news_moderate()
{
	global $administrator, $moderator;

	if($_SERVER["REQUEST_METHOD"] != "POST")
		return 0;
	if($administrator != 1 && $moderator != 1)
		return 0;
	$newsid = $_POST["newsid"];
	$enable = $_POST["enable"];
	sql_query("update daportal_news set enable='$enable' where newsid='$newsid';");
	header("Location: index.php?module=news&action=admin");
	return 0;
}


function news_propose()
{
	global $userid;

	print("\t\t<h1>News proposal</h1>\n");
	if($userid == 0)
	{
		print("\t\t<div>
\t\t\tYou must be <a href=\"index.php?module=user\">identified</a> to propose a news.
\t\t</div>\n");
		return 0;
	}

	print("\t\t<form method=\"post\" action=\"index.php\">
\t\t\tTitle: <input type=\"text\" size=\"40\" name=\"title\"/><br/>
\t\t\tContent:<br/>
\t\t\t<textarea cols=\"80\" rows=\"10\" name=\"content\"></textarea><br/>
\t\t\t<input type=\"submit\" value=\"Submit\"/>
\t\t\t<input type=\"hidden\" name=\"module\" value=\"news\"/>
\t\t\t<input type=\"hidden\" name=\"action\" value=\"submit\"/>
\t\t</form>\n");
	return 0;
}


function news_submit()
{
	global $moduleid, $userid;

	if($userid == 0)
		return 1;
	$title = htmlentities($_POST["title"]);
	$content = htmlentities($_POST["content"]);
	$res = sql_query("select nextval('daportal_contents_contentid_seq');");
	$contentid = $res[0]["nextval"];
	sql_query("insert into daportal_contents (contentid, moduleid, title, content) values ('$contentid', '$moduleid', '$title', '$content');");
	sql_query("insert into daportal_news (newsid, author) values ('$contentid', '$userid');");
	header("Location: index.php?module=news&action=thanks");
	exit(0);
}


function news_thanks()
{
	print("\t\t<h1>Thanks!</h1>
\t\t<div>
\t\t\tYour news has been submitted to the webmasters, they will check it as soon as possible. Thanks!
\t\t</div>
\t\t<div>
\t\t\tReturn to the <a href=\"index.php\">main page</a>.
\t\t</div>\n");
	return 0;
}


function news_uninstall()
{
	global $administrator;

	if($administrator != 1)
		return 0;
	return 0;
}


function news_view()
{
	global $administrator, $moderator;

	print("\t\t<h1>News</h1>\n");
	$newsid = $_GET["newsid"];
	if(($res = sql_query("select title, content, date, enable, username from daportal_news, daportal_contents, daportal_users where newsid='$newsid' and newsid=contentid and author=userid;")) == FALSE
			|| (($res[0]["enable"] == "f" && $administrator != 1)
			&& ($res[0]["enable"] == "f" && $moderator != 1)))
	{
	print("\t\t<div>
\t\t\tUnknown news.
\t\t</div>\n");
		return 0;
	}
	display($res[0]["title"], $res[0]["username"], $res[0]["date"], $res[0]["content"]);
	return 0;
}


switch($action)
{
	case "admin":
		return news_admin();
	case "install":
		return news_install();
	case "moderate":
		return news_moderate();
	case "propose":
		return news_propose();
	case "submit":
		return news_submit();
	case "thanks":
		return news_thanks();
	case "uninstall":
		return news_uninstall();
	case "view":
		return news_view();
	default:
		return news_default();
}


?>
