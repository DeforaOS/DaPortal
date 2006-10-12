<h1><img src="modules/user/user.png" alt=""/> <?php echo _html_safe($user['username']); ?></h1>
<style type="text/css"><!-- @import url('css/explorer.css'); --></style>
<div class="explorer">
	<div class="listing_thumbnails">
<?php if(_module_id('article')) { ?>
		<div class="entry">
			<div class="thumbnail"><img src="modules/article/icon.png" alt=""/></div>
			<div class="name"><a href="index.php?module=article&amp;user_id=<?php echo _html_safe_link($user['user_id']); ?>" title="Articles">Articles</a></div>
		</div>
<?php } ?>
<?php global $user_id; if(_module_id('bookmark') && $user_id != 0) { ?>
		<div class="entry">
			<div class="thumbnail"><img src="modules/bookmark/icon.png" alt=""/></div>
			<div class="name"><a href="index.php?module=bookmark&amp;user_id=<?php echo _html_safe_link($user['user_id']); ?>" title="Bookmarks">Bookmarks</a></div>
		</div>
<?php } ?>
<?php if(_module_id('news')) { ?>
		<div class="entry">
			<div class="thumbnail"><img src="modules/news/icon.png" alt=""/></div>
			<div class="name"><a href="index.php?module=news&amp;user_id=<?php echo _html_safe_link($user['user_id']); ?>" title="News">News</a></div>
		</div>
<?php } ?>
<?php if(_module_id('comment')) { ?>
		<div class="entry">
			<div class="thumbnail"><img src="modules/comment/icon.png" alt=""/></div>
			<div class="name"><a href="index.php?module=comment&amp;user_id=<?php echo _html_safe_link($user['user_id']); ?>" title="Comments">Comments</a></div>
		</div>
<?php } ?>
<?php if(_module_id('project')) { ?>
		<div class="entry">
			<div class="thumbnail"><img src="modules/project/icon.png" alt=""/></div>
			<div class="name"><a href="index.php?module=project&amp;action=list&amp;user_id=<?php echo _html_safe_link($user['user_id']); ?>" title="Projects">Projects</a></div>
		</div>
		<div class="entry">
			<div class="thumbnail"><img src="modules/project/bug.png" alt=""/></div>
			<div class="name"><a href="index.php?module=project&amp;action=bug_list&amp;user_id=<?php echo _html_safe_link($user['user_id']); ?>" title="Bug reports">Bug reports</a></div>
		</div>
<?php } ?>
	</div>
	<div style="clear: left">&nbsp;</div>
</div>
