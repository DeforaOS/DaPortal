#!/bin/sh
#$Id$
#Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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
DAPORTALCONF='../tests/daportal.conf'
PROGNAME="widgets"
#executables
DEBUG="_debug"
PHP="/usr/bin/env php"


#functions
#widgets
_widgets()
{
	source="$1"
	target="$2"

	export DAPORTALCONF="$DAPORTALCONF"
	$DEBUG $PHP "$source" > "${OBJDIR}$target"
}


#debug
_debug()
{
	echo "$@" 1>&2
	"$@"
}


#usage
_usage()
{
	echo "Usage: $PROGNAME [-c][-P prefix] target" 1>&2
	return 1
}


#main
clean=0
while getopts "cP:" name; do
	case "$name" in
		c)
			clean=1
			;;
		P)
			#XXX ignored for compatibility
			;;
		?)
			_usage
			exit $?
			;;
	esac
done
shift $((OPTIND - 1))
if [ $# -ne 1 ]; then
	_usage
	exit $?
fi
target="$1"

if [ $clean -ne 0 ]; then
	exit 0
fi

_widgets "${target%.html}.php" "$target"
