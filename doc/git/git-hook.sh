#!/bin/sh
#$Id$
#Copyright (c) 2017 Pierre Pronchery <khorben@defora.org>
#This file is part of DeforaOS Web DaPortal
#This program is free software: you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation, version 3 of the License.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#
#You should have received a copy of the GNU General Public License
#along with this program.  If not, see <http://www.gnu.org/licenses/>.



#variables
PROGNAME="git-hook.sh"
REPO="DaPortal"
#executables
GIT="git"
MAKE="make"
MKTEMP="mktemp"
RM="rm -f"


#functions
#deploy
_deploy()
{
	branch="$1"
	tmpdir=$($MKTEMP -d)
	[ $? -eq 0 ]						|| return 2

	$GIT clone -q --single-branch --branch "$branch" \
		"$HOME/Projects/$REPO" "$tmpdir/$REPO"		|| ret=3
	if [ $ret -eq 0 ]; then
		(cd "$tmpdir/$REPO" && $MAKE && $MAKE install)	|| ret=4
	fi
	$RM -r -- "$tmpdir"
	return $ret
}


#hook_post_receive
_hook_post_receive()
{
	if [ $# -ne 0 ]; then
		_usage "post-receive"
		return $?
	fi
	while read oldrev newrev refname; do
		#XXX ignore errors
		_hook_update "$refname" "$oldrev" "$newrev"
	done
}


#hook_update
_hook_update()
{
	if [ $# -ne 3 ]; then
		_usage "update refname oldrev newrev"
		return $?
	fi
	refname="$1"
	oldrev="$2"
	newrev="$3"

	case "$refname" in
		refs/heads/*)
			branch="${refname#refs/heads/}"
			if [ "$branch" = "deploy" ]; then
				_deploy "$branch"
				ret=$?
			fi
			;;
	esac
	return $ret
}


#error
_error()
{
	echo "$PROGNAME: $@" 1>&2
	return 2
}


#usage
_usage()
{
	if [ $# -gt 0 ]; then
		echo "Usage: $PROGNAME $@" 1>&2
	else
		echo "Usage: $PROGNAME [-O name=value...] hook [argument...]" 1>&2
	fi
	return 1
}


#main
ret=0
hook="${0##*/}"
case "$hook" in
	"post-receive")
		_hook_post_receive "$@"
		ret=$?
		;;
	"update")
		_hook_update "$@"
		ret=$?
		;;
	*)
		_error "$hook: Unsupported hook"
		ret=$?
		;;
esac
exit $ret
