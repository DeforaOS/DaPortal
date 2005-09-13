<? global $user_id, $title, $module_name; $module = $module_name; ?>
<? _module('top'); ?>
		<div class="container">
			<div class="top_search">
				<form action="index.php" method="get">
					<input type="hidden" name="module" value="search"/>
					<input type="text" name="q" value="search..." size="20" onfocus="if(value == 'search...') value=''"/>
					<input id="search" type="submit" value="Search"/>
				</form>
				<script type="text/javascript">
<!--
document.getElementById('search').style.display='none';
//-->
				</script>
			</div>
			<div class="logo"></div>
			<div class="style1"><a href="index.php">DeforaOS</a> :: <? echo (strlen($module) ? '<a href="index.php?module='.$module.'">'.ucfirst($module).'</a>' : 'Homepage'); ?></div>
			<div class="menu">
<? if($user_id) { _module('menu'); } else { ?>
			<ul>
				<li><a href="index.php">About</a><ul>
					<li><a href="index.php?module=news">News</a></li>
					<li><a href="index.php?module=project">Project</a></li>
					<li><a href="roadmap.html">Roadmap</a></li>
					</ul></li>
				<li><a href="development.html">Development</a><ul>
					<li><a href="policy.html">Policy</a></li>
					<li><a href="index.php?module=project&amp;action=list">Projects</a></li>
					</ul></li>
				<li><a href="index.php?module=project&amp;action=download">Download</a><ul>
					<li><a href="index.php?module=project&amp;action=installer">Installer</a></li>
					<li><a href="index.php?module=project&amp;action=package">Packages</a></li>
					</ul></li>
				<li><a href="support.html">Support</a><ul>
					<li><a href="documentation.html">Documentation</a></li>
					<li><a href="index.php?module=project&amp;action=bug_list">Reports</a></li>
					</ul></li>
			</ul>
<? } ?>
<? if(is_array(($langs = _sql_array('SELECT lang_id AS id, name'
		.' FROM daportal_lang'
		." WHERE enabled='t' ORDER BY name ASC;")))) { ?>
			<form class="lang" action="index.php" method="post">
				<div>
					<select name="lang" onchange="submit()">
<? global $lang; foreach($langs as $l) { ?>
						<option value="<? echo _html_safe($l['id']); ?>"<? if($lang == $l['id']) { ?> selected="selected"<? } ?>><? echo _html_safe($l['name']); ?></option>
<? } ?>
					</select>
					<input id="lang" type="submit" value="Choose"/>
				</div>
				<script type="text/javascript">
<!--
document.getElementById('lang').style.display='none';
//-->
				</script>
			</form>
<? } ?>
			</div>
			<div class="main">
<? if(strlen($module)) { _module(); } else { ?>
		<h1>DeforaOS Homepage</h1>
		<h3>About the project</h3>
		<p>
This project aims at the implementation of a micro-kernel based operating
system. The primary goals include:
		</p>
		<ul>
			<li>clean design;</li>
			<li>simple code;</li>
			<li>usability.</li>
		</ul>
		<p>
The project is still at its <a href="index.php?module=project">earliest
stage</a>.
		</p>

		<h3>Latest news</h3>
		<p>
Will hopefully be filled with <a href="index.php?module=news">every significant
work made</a> for the project. This will be eased with the implementation of
the site content management system.
		</p>
<? } ?>
<? _debug(); ?>
			</div>
			<div class="style1" style="padding-right: 33px; text-align: right;">Validate <a href="http://validator.w3.org/check/referer"><img src="images/xhtml.png" alt=""/></a> <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="images/css.png" alt=""/></a></div>
		</div>
