#!/bin/sh
#$Id$
#Copyright (c) 2013-2015 Pierre Pronchery <khorben@defora.org>
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
PROGNAME="tests.sh"
#executables
CP="cp -f"
DATE="date"
DEBUG="_debug"
PHP="/usr/bin/env php"


#functions
#fail
_fail()
{
	test="$1"

	shift
	echo -n "$test:" 1>&2
	(echo
	echo "Testing: $test" "$@"
	export DAPORTALCONF="$DAPORTALCONF"
	$CP -- "sqlite.db3" "sqlite-tests.db3"
	$DEBUG $PHP "./$test.php" "$@" 2>&1) >> "$target"
	res=$?
	if [ $res -ne 0 ]; then
		echo " FAIL (error $res)" 1>&2
	else
		echo " PASS" 1>&2
	fi
}


#test
_test()
{
	test="$1"

	shift
	echo -n "$test:" 1>&2
	(echo
	echo "Testing: $test" "$@"
	export DAPORTALCONF="$DAPORTALCONF"
	$CP -- "sqlite.db3" "sqlite-tests.db3"
	$DEBUG $PHP "./$test.php" "$@" 2>&1) >> "$target"
	res=$?
	if [ $res -ne 0 ]; then
		echo " FAIL" 1>&2
		FAILED="$FAILED $test(error $res)"
		return 2
	else
		echo " PASS" 1>&2
		return 0
	fi
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

[ $clean -ne 0 ] && exit 0

$DATE > "$target"
FAILED=
echo "Performing tests:" 1>&2
_test "auth"
_test "config"
_test "coverage"
_test "database"
_test "mail"
_test "module"
_test "user"
#echo "Expected failures:" 1>&2
#_fail "test"
if [ -n "$FAILED" ]; then
	echo "Failed tests:$FAILED" 1>&2
	exit 2
fi
echo "All tests completed" 1>&2
