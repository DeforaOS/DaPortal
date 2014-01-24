#!/bin/sh
#$Id$
#Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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
#executables
DATE="date"
PHP="/usr/bin/env php"


#functions
#test
_test()
{
	test="$1"

	shift
	echo -n "$test:" 1>&2
	(echo
	echo "Testing: $test" "$@"
	$PHP "./$test.php" "$@") >> "$target" 2>&1
	res=$?
	if [ $res -ne 0 ]; then
		echo " FAILED" 1>&2
		FAILED="$FAILED $test(error $res)"
		return 2
	else
		echo " PASS" 1>&2
		return 0
	fi
}


#usage
_usage()
{
	echo "Usage: tests.sh [-c][-P prefix] target" 1>&2
	return 1
}


#main
clean=0
while getopts "cP:" "name"; do
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

[ $clean -ne 0 ] && exit 0

$DATE > "$target"
FAILED=
echo "Performing tests:" 1>&2
_test "auth"
_test "coverage"
_test "module"
_test "user"
#echo "Expected failures:" 1>&2
#_fail "test"
if [ -n "$FAILED" ]; then
	echo "Failed tests:$FAILED" 1>&2
	exit 2
fi
echo "All tests completed" 1>&2
