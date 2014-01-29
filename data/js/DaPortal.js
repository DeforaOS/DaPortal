/* $Id$ */
/* Copyright (c) 2014 Pierre Pronchery <khorben@defora.org> */
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

	//expanders
	$('div.expander > div.title').on('click', function(event) {
		children = $(this).siblings();
		visible = (children.size() > 0
			&& children.slice(0).css('display') == 'none')
			? true : false;
		image = visible ? '../icons/generic/16x16/expanded.png'
			: '../icons/generic/16x16/collapsed.png';
		display = visible ? 'block' : 'none';

		$(this).css('background-image', "url('" + image + "')");
		children.css('display', display);
	});
});
