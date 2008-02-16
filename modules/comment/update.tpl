<a name="edit"></a>
<form action="index.php" method="post">
	<input type="hidden" name="module" value="<?php echo $comment['module']; ?>"/>
	<input type="hidden" name="action" value="reply"/>
	<input type="hidden" name="id" value="<?php echo _html_safe($comment['id']); ?>"/>
<?php if(isset($comment['parent'])) { ?>
	<input type="hidden" name="parent" value="<?php echo _html_safe($comment['parent']); ?>"/>
<?php } ?>
	<table>
		<tr>
			<td class="field"><?php echo _html_safe(TITLE); ?>&nbsp;:</td>
			<td><input type="text" name="title" value="<?php echo _html_safe($comment['title']); ?>" size="50"/></td>
		</tr>
		<tr>
			<td class="field"><?php echo _html_safe(CONTENT); ?>&nbsp;:</td>
			<td><textarea name="content" cols="50" rows="10"><?php if(isset($comment['content'])) echo _html_safe($comment['content']); ?></textarea></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="preview" value="<?php echo _html_safe(PREVIEW); ?>"/> <input type="submit" name="submit" value="<?php echo _html_safe(SEND); ?>"/></td>
		</tr>
	</table>
</form>
