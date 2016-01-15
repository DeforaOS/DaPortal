#!/bin/sh
#$Id$
#Copyright (c) 2014-2016 Pierre Pronchery <khorben@defora.org>
#This file is part of DeforaOS Artwork
#Redistribution and use in source and binary forms, with or without
#modification, are permitted provided that the following conditions are met:
#
# * Redistributions of source code must retain the above copyright notice, this
#   list of conditions and the following disclaimer.
# * Redistributions in binary form must reproduce the above copyright notice,
#   this list of conditions and the following disclaimer in the documentation
#   and/or other materials provided with the distribution.
#
#THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
#AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
#IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
#DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
#FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
#DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
#SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
#CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
#OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
#OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.



#variables
ICONS="
actions/back				back
actions/bottom				bottom
actions/changes-allow			unlock
actions/changes-prevent			lock
actions/document-send			submit
actions/filenew				file-new
actions/folder-new			folder-new
actions/forward				forward
actions/gnome-logout			logout
actions/go-jump				connect
actions/gtk-about			about
actions/gtk-add				add more subscribe
actions/gtk-bold			bold
actions/gtk-cancel			cancel disable
actions/gtk-clear			remove-format
actions/gtk-close			close
actions/gtk-copy			copy
actions/gtk-cut				cut
actions/gtk-delete			delete
actions/gtk-execute			enable
actions/gtk-find			search
actions/gtk-go-up			updir
actions/gtk-goto-bottom			subscript
actions/gtk-goto-top			superscript
actions/gtk-home			home homepage
actions/gtk-indent-ltr			indent
actions/gtk-italic			italic
actions/gtk-justify-center		justify-center
actions/gtk-justify-fill		justify-fill
actions/gtk-justify-left		justify-left
actions/gtk-justify-right		justify-right
actions/gtk-new				new
actions/gtk-open			open read
actions/gtk-paste			paste
actions/gtk-print-preview		preview
actions/gtk-redo-ltr			redo
actions/gtk-remove			insert-hrule remove unsubscribe
actions/gtk-save			save
actions/gtk-save-as			upload
actions/gtk-sort-ascending		numbering
actions/gtk-sort-descending		bullets
actions/gtk-strikethrough		strikethrough
actions/gtk-underline			underline
actions/gtk-undo-ltr			undo
actions/gtk-unindent-ltr		unindent
actions/insert-image			insert-image
actions/insert-link			insert-link
actions/insert-object			insert-object
actions/insert-text			insert-text
actions/mail-reply-all			comment
actions/mail-reply-sender		reply
actions/mail-send			email webmail
actions/next				next
actions/previous			previous
actions/revert				reset
actions/stock_first			gotofirst
actions/stock_last			gotolast
actions/stock_refresh			refresh
actions/top				top
apps/config-language			translate
apps/file-manager			browser
apps/gnome-monitor			monitor
apps/help-browser			help wiki
apps/system-users			members
apps/text-editor			content edit update
apps/user-info				login register user
apps/utilities-system-monitor		probe
apps/web-browser			news
categories/applications-development	bug bug_list development project
categories/gtk-preferences		admin preferences
categories/stock_internet		link
mimetypes/application-certificate	pki
mimetypes/gtk-file			file
mimetypes/html				article
mimetypes/image-x-generic		gallery
mimetypes/stock_calendar		blog timeline
places/folder				browse folder
places/folder-download			download
places/user-bookmarks			bookmark
status/stock_dialog-error		error
status/stock_dialog-info		info
status/stock_dialog-question		question
status/stock_dialog-warning		warning"
PROGNAME="icontheme.sh"
#executables
CAT="cat"
DEBUG="_debug"
INSTALL="install -m 0644"
MKDIR="mkdir -m 0755 -p"
RM="rm -f"


#functions
#icontheme
_icontheme()
{
	target="$1"
	dirname="${target%/*}"
	filename="${target#$OBJDIR}"
	theme="${filename%%/*}"
	size="${filename#*/}"
	size="${size%%/*}"
	size="${size%%x*}"
	ext=".png"

	#XXX exceptions
	case "$theme" in
		"gnome")
			theme="gnome-icon-theme"
			;;
	esac
	$MKDIR -- "$dirname"					|| return 2
	($CAT << EOF
/* \$Id\$ */
/* Copyright (c) 2014-2016 Pierre Pronchery <khorben@defora.org> */
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


EOF
	echo "$ICONS" | while read stock icons; do
		[ -z "$stock" ] && continue
		echo
		sep=
		for i in $icons; do
			echo -n "${sep}.stock${size}.$i"
			sep=", "
		done
		echo " {"
		echo "	background-image:	url('$theme/${size}x$size/$stock$ext');"
		echo "}"
	done) > "$target"					|| return 2
}


#clean
_clean()
{
	:
}


#debug
_debug()
{
	echo "$@" 1>&2
	"$@"
}


#install
_install()
{
	target="$1"
	filename="${target#$OBJDIR}"
	dirname="${filename%/*}"

	$DEBUG $MKDIR -- "$PREFIX/$dirname"			|| return 2
	$DEBUG $INSTALL "$target" "$PREFIX/$filename"		|| return 2
}


#uninstall
_uninstall()
{
	target="$1"
	instdir="${target%%/*}"

	$DEBUG $RM -- "$PREFIX/$target"				|| return 2
}


#usage
_usage()
{
	echo "Usage: $PROGNAME [-c|-i|-u][-P prefix] target..." 1>&2
	return 1
}


#main
clean=0
install=0
uninstall=0
while getopts "ciuP:" name; do
	case "$name" in
		c)
			clean=1
			;;
		i)
			install=1
			uninstall=0
			;;
		u)
			install=0
			uninstall=1
			;;
		P)
			PREFIX="$OPTARG"
			;;
		?)
			_usage
			exit $?
			;;
	esac
done
shift $((OPTIND - 1))
if [ $# -eq 0 ]; then
	_usage
	exit $?
fi

while [ $# -gt 0 ]; do
	target="$1"
	shift

	#clean
	if [ $clean -ne 0 ]; then
		_clean "$theme" "$size"				|| exit 2
		continue
	fi

	#uninstall
	if [ $uninstall -eq 1 ]; then
		_uninstall "$target"				|| exit 2
		continue
	fi

	#install
	if [ $install -eq 1 ]; then
		_install "$target"				|| exit 2
		continue
	fi

	#create
	_icontheme "$target"					|| exit 2
done
