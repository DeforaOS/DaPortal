<div class="wiki">
<?php if(isset($title)) { ?>
	<h1 class="title wiki"><?php echo _html_safe($title); ?></h1>
<?php } ?>
	<script type="text/javascript"><!-- //FIXME put in a separate js file
function wikiStart()
{
	var wiki = document.getElementById('wiki');
	var wikitext = document.getElementById('wikitext');

	wikitext.style.visibility = 'hidden';
	wikitext.className = 'hidden';
	wiki.className = '';
	wiki.contentWindow.document.designMode = "on";
	try {
		wiki.contentWindow.document.execCommand("undo", false, null);
	}
	catch (e) {
		alert("This editor is not supported in your browser");
		return;
	}
	wiki.contentWindow.document.body.innerHTML = wikitext.value;
}


function wikiExec(cmd)
{
	var wiki = document.getElementById('wiki');

	wiki.contentWindow.document.execCommand(cmd, false, null);
}


function wikiSelect(id)
{
	var wiki = document.getElementById('wiki');
	var e = document.getElementById(id);
	var sel = e.selectedIndex;

	if(sel != 0)
	{
		wiki.contentWindow.document.execCommand(id, false,
				e.options[sel].value);
		e.selectedIndex = 0;
	}
	wiki.contentWindow.focus();
}


function wikiSubmit()
{
	var wiki = document.getElementById('wiki');
	var wikitext = document.getElementById('wikitext');

	wikitext.value = wiki.contentWindow.document.body.innerHTML;
}
	//--></script>
	<div class="toolbar">
		<div class="icon cut" title="Cut" onclick="wikiExec('cut')"></div>
		<div class="icon copy" title="Copy" onclick="wikiExec('copy')"></div>
		<div class="icon paste" title="Paste" onclick="wikiExec('paste')"></div>
		<div class="icon separator"></div>
		<div class="icon undo" title="Undo" onclick="wikiExec('undo')"></div>
		<div class="icon redo" title="Redo" onclick="wikiExec('redo')"></div>
	</div>
	<div class="toolbar">
		Style: <select id="formatblock" onchange="wikiSelect(this.id)">
			<option value="<h1>">Heading 1</option>
			<option value="<h2>">Heading 2</option>
			<option value="<h3>">Heading 3</option>
			<option value="<h4>">Heading 4</option>
			<option value="<h5>">Heading 5</option>
			<option value="<h6>">Heading 6</option>
			<option value="<p>" selected="selected">Normal</option>
			<option value="<Pre>">Preformatted</option>
		</select>
		Font size: <select id="fontsize" unselectable="on" onchange="wikiSelect(this.id)">
			<option value="Size">Size</option>
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
		</select>
		<div class="icon separator"></div>
		<div class="icon bold" title="Bold" onclick="wikiExec('bold')"></div>
		<div class="icon italic" title="Italic" onclick="wikiExec('italic')"></div>
		<div class="icon underline" title="Underline" onclick="wikiExec('underline')"></div>
		<div class="icon strikethrough" title="Strike" onclick="wikiExec('strikethrough')"></div>
		<div class="icon separator"></div>
		<div class="icon align_left" title="Align left" onclick="wikiExec('justifyleft')"></div>
		<div class="icon align_center" title="Align center" onclick="wikiExec('justifycenter')"></div>
		<div class="icon align_right" title="Align right" onclick="wikiExec('justifyright')"></div>
		<div class="icon align_justify" title="Align justify" onclick="wikiExec('justifyfull')"></div>
		<div class="icon separator"></div>
		<div class="icon bullet" title="Bullets" onclick="wikiExec('insertunorderedlist')"></div>
		<div class="icon enum" title="Enumerated" onclick="wikiExec('insertorderedlist')"></div>
		<div class="icon unindent" title="Unindent" onclick="wikiExec('outdent')"></div>
		<div class="icon indent" title="Indent" onclick="wikiExec('indent')"></div>
	</div>
	<form action="index.php" method="post" onsubmit="wikiSubmit()">
		<input type="hidden" name="module" value="wiki"/>
<?php if(!isset($wiki['id'])) { ?>
		<input type="hidden" name="action" value="insert"/>
<?php } else { ?>
		<input type="hidden" name="action" value="update"/>
		<input type="hidden" name="id" value="<?php echo _html_safe($wiki['id']); ?>"/>
<?php } ?>
		<table width="100%">
<?php if(!isset($wiki['id'])) { ?>
		<tr><td class="field"><?php echo _html_safe(TITLE); ?>:</td><td><input type="text" name="title" value="<?php if(isset($wiki['title'])) echo _html_safe($wiki['title']); ?>"/></td></tr>
<?php } ?>
		<tr><td colspan="2"><textarea id="wikitext" name="content" cols="80" rows="20"><?php if(isset($wiki['content'])) echo _html_safe($wiki['content']); ?></textarea></td></tr>
		<tr><td colspan="2"><iframe id="wiki" class="hidden" width="100%" height="260px" onload="wikiStart()"></iframe></td></tr>
		<tr><td colspan="2"><input type="submit" name="preview" value="<?php echo _html_safe(PREVIEW); ?>"/> <input type="submit" name="send" value="<?php echo _html_safe(SUBMIT); ?>"/></td></tr>
		</table>
	</form>
</div>
