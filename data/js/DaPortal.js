/* $Id$ */
/* Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org> */
/* This file is part of DeforaOS Web DaPortal */
/* This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */



$(document).ready(function() {
	$('body').addClass('js-activated');

	//dialogs
	$('div.dialog').each(function(index) {
		dialog = $(this);

		$(this).children('button.close').each(function(index) {
			$(this).on('click', { dialog: dialog },
				function(event) {
				dialog = event.data.dialog;

				dialog.addClass('hidden');
			});
			$(this).removeClass('hidden');
		});
	});

	//editor
	$('iframe.editor').each(function(index) {
		editor = $(this);

		//hide the textarea and copy its content over to the iframe
		textarea = $(this).siblings('textarea');
		if(textarea.size() == 1)
		{
			textarea = textarea.slice(0);
			textarea.css('display', 'none');
			$(this).contents().find('body').html(
				textarea.text());
		}

		//enable design mode
		$(this).get(0).contentWindow.document.designMode = 'on';

		//set the focus to the first instance
		if(index == 0)
			$(this).get(0).contentWindow.focus();

		//configure the toolbar buttons
		$(this).siblings('.toolbar').find('.button').each(function() {
			if((classes = $(this).attr('class')) === undefined)
				return;
			arg = null;
			classes = classes.split(' ');
			for(i = 0; i < classes.length; i++)
				switch(classes[i])
				{
					case 'bold':
					case 'copy':
					case 'cut':
					case 'indent':
					case 'italic':
					case 'paste':
					case 'redo':
					case 'strikethrough':
					case 'subscript':
					case 'superscript':
					case 'underline':
					case 'undo':
						command = classes[i];
						break;
					case 'bullets':
						command = 'insertunorderedlist';
						break;
					case 'insert-hrule':
						command = 'inserthorizontalrule';
						break;
					case 'insert-image':
						$(this).on('click', { editor: editor }, function(event) {
							editor = event.data.editor.get(0).contentWindow;

							url = prompt('Enter URL:', 'http://');
							if(url == null || url == '')
								return;
							editor.document.execCommand('insertimage', false, url);
							editor.focus();
						});
						return;
					case 'insert-link':
						$(this).on('click', { editor: editor }, function(event) {
							editor = event.data.editor.get(0).contentWindow;

							url = prompt('Enter URL:', 'http://');
							if(url == null || url == '')
								return;
							editor.document.execCommand('createlink', false, url);
							editor.focus();
						});
						return;
					case 'insert-table':
						command = 'inserthtml';
						arg = '<table border="1">'
							+ '<tr><td></td><td></td></tr>'
							+ '<tr><td></td><td></td></tr>'
							+ '</table>';
						break;
					case 'insert-text':
						$(this).on('click', { editor: editor }, function(event) {
							editor = event.data.editor.get(0).contentWindow;

							text = prompt('Enter text:', '');
							if(text == null || text == '')
								return;
							editor.document.execCommand('inserttext', false, text);
							editor.focus();
						});
						return;
					case 'justify-center':
						command = 'justifycenter';
						break;
					case 'justify-fill':
						command = 'justifyfill';
						break;
					case 'justify-left':
						command = 'justifyleft';
						break;
					case 'justify-right':
						command = 'justifyright';
						break;
					case 'numbering':
						command = 'insertorderedlist';
						break;
					case 'remove-format':
						command = 'removeformat';
						break;
					case 'unindent':
						command = 'outdent';
						break;
				}
			if(command === undefined)
				return;
			$(this).on('click', { editor: editor, command: command,
					arg: arg }, function(event) {
				editor = event.data.editor.get(0).contentWindow;
				command = event.data.command;
				arg = event.data.arg;

				editor.document.execCommand(command, false, arg);
				editor.focus();
			});
		});

		//configure the toolbar selectors
		$(this).siblings('.toolbar').find('.combobox > select').each(function() {
			if((classes = $(this).attr('class')) === undefined)
				return;
			classes = classes.split(' ');
			for(i = 0; i < classes.length; i++)
				switch(classes[i])
				{
					case 'fontname':
					case 'fontsize':
					case 'formatblock':
						command = classes[i];
						break;
				}
			if(command === undefined)
				return;
			$(this).val(0);
			$(this).on('change', { editor: editor, command: command },
					function(event) {
				editor = event.data.editor.get(0).contentWindow;
				command = event.data.command;

				editor.document.execCommand(command, false,
						$(this).val());
				$(this).val(0);
				editor.focus();
			});
		});

		//copy the HTML content back to the form when submitting
		$(this).closest('form').on('submit',
			{ iframe: $(this), textarea: textarea},
			function(event) {
				iframe = event.data.iframe;
				text = iframe.contents().find('body').html();
				textarea = event.data.textarea;

				textarea.text(text);
			});
	});

	//entry
	$('input.entry[type=text]').each(function() {
		//locate the "more" button
		button = $(this).siblings('input[type=button]');

		if(button.size() == 1)
		{
			button.removeClass('hidden');
			button.on('click', { entry: $(this) },
				function(event) {
				entry = event.data.entry;
				name = entry.attr('name');

				//add an entry
				entry.parent().append('<br/>'
					+ '<input type="text" name="'
					+ name + '"/>');
			});
		}
	});

	//expander
	$('div.expander > div.title').on('click', function(event) {
		children = $(this).siblings();
		visible = (children.size() > 0
			&& children.slice(0).css('display') == 'none')
			? true : false;
		image = visible ? '../icons/generic/24x24/expanded.png'
			: '../icons/generic/24x24/collapsed.png';
		display = visible ? 'block' : 'none';

		//update the expander's icon
		$(this).css('background-image', "url('" + image + "')");
		//hide or show the content
		children.css('display', display);
	});

	//file chooser
	$('input.filechooser[type=file]').each(function() {
		//locate the "more" button
		button = $(this).siblings('input[type=button]');

		if(button.size() == 1)
		{
			button.removeClass('hidden');
			button.on('click', { filechooser: $(this) },
				function(event) {
				filechooser = event.data.filechooser;
				name = filechooser.attr('name');

				//add a file chooser
				filechooser.parent().append('<br/>'
					+ '<input type="file" name="'
					+ name + '"/>');
			});
		}
	});
});
