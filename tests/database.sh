#!/bin/sh
#$Id$
#Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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
DEBUG="_debug"
DEVNULL="/dev/null"
PROGNAME="database.sh"
#executables
RM="rm -f"
SQLITE2="sqlite"
SQLITE3="sqlite3"


#functions
#debug
_debug()
{
	echo "$@" 1>&2
	"$@"
	ret=$?
	#ignore errors when the command is not available
	[ $ret -eq 127 ]					&& return 0
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
	echo "Usage: $PROGNAME [-P prefix] target" 1>&2
	return 1;
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
database="${target#$OBJDIR}"

[ "$clean" -ne 0 ] && exit 0

case "$database" in
	sqlite.db)
		$RM -- "$target"				|| exit 2
		#XXX run twice to ensure there are no false positives
		echo .read "../doc/sql/sqlite.sql" | $DEBUG $SQLITE2 "$target" > "$DEVNULL" 2>&1
		echo .read "../doc/sql/sqlite.sql" | $DEBUG $SQLITE2 "$target"
		res=$?
		echo .read "../doc/sql/dataset.sql" | $DEBUG $SQLITE2 "$target"
		[ $? -eq 0 ] || res=$?
		if [ $res -ne 0 ]; then
			_error "$target: Error $res"
			exit $?
		fi
		;;
	sqlite.db3)
		$RM -- "$target"				|| exit 2
		#XXX run twice to ensure there are no false positives
		echo .read "../doc/sql/sqlite.sql" | $DEBUG $SQLITE3 "$target" > "$DEVNULL" 2>&1
		echo .read "../doc/sql/sqlite.sql" | $DEBUG $SQLITE3 "$target"
		res=$?
		echo .read "../doc/sql/dataset.sql" | $DEBUG $SQLITE3 "$target"
		[ $? -eq 0 ] || res=$?
		if [ $res -ne 0 ]; then
			_error "$target: Error $res"
			exit $?
		fi
		;;
	*)
		_error "$database: Unknown database target"
		exit 2
		;;
esac
