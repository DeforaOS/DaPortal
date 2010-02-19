<form method="get" action="index.php">
	<input type="hidden" name="module" value="project"/>
	<input type="hidden" name="action" value="bug_list"/>
	<table>
		<tr><td class="field"><?php echo _html_safe(PROJECT_NAME); ?>:</td><td><input type="text" name="project" value="<?php if(isset($args['project'])) echo _html_safe(stripslashes($args['project'])); ?>" size="20"/></td>
		<td class="field"><?php echo _html_safe(SUBMITTER); ?>:</td><td><input type="text" name="username" value="<?php if(isset($args['username'])) echo _html_safe(stripslashes($args['username'])); ?>" size="20"/></td></tr>
		<tr><td class="field"><?php echo _html_safe(STATE); ?>:</td><td><select name="state">
				<option value=""<?php if(isset($args['state']) && $args['state'] == '') { ?> selected="selected"<?php } ?>>All</option>
<?php $states = _sql_enum('daportal_bug', 'state');
foreach($states as $s) { ?>
				<option value="<?php echo _html_safe($s); ?>"<?php if(isset($args['state']) && $args['state'] == $s) { ?> selected="selected"<?php } ?>><?php echo _html_safe($s); ?></option>
<?php } ?>
			</select></td>
		<td class="field"><?php echo _html_safe(TYPE); ?>:</td><td><select name="type">
				<option value=""<?php if(isset($args['type']) && $args['type'] == '') { ?> selected="selected"<?php } ?>>All</option>
<?php $types = _sql_enum('daportal_bug', 'type');
foreach($types as $t) { ?>
				<option value="<?php echo _html_safe($t); ?>"<?php if(isset($args['type']) && $args['type'] == $t) { ?> selected="selected"<?php } ?>><?php echo _html_safe($t); ?></option>
<?php } ?>
			</select></td></tr>
		<tr><td class="field"><?php echo _html_safe(PRIORITY); ?>:</td><td><select name="priority">
				<option value=""<?php if(isset($args['priority']) && $args['priority'] == '') { ?> selected="selected"<?php } ?>>All</option>
<?php $priorities = _sql_enum('daportal_bug', 'priority');
foreach($priorities as $p) { ?>
				<option value="<?php echo _html_safe($p); ?>"<?php if(isset($args['priority']) && $args['priority'] == $p) { ?> selected="selected"<?php } ?>><?php echo _html_safe($p); ?></option>
<?php } ?>
			</select></td>
		<td></td><td><button type="reset" class="icon reset"><?php echo _html_safe(RESET); ?></button> <input type="submit" value="<?php echo _html_safe(FILTER); ?>" class="icon submit"/></td></tr>
	</table>
</form>
