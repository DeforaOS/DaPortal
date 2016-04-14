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
DEBUG=
DEVNULL="/dev/null"
FORCE=0
PACKAGE=
PROGNAME="deploy.sh"
VERSION=
PREFIX="/usr/local"
TMPDIR="/tmp"
VERBOSE=0
#executables
CP="cp -f"
GIT="git"
MAKE="make"
MKDIR="mkdir -p"
MKTEMP="mktemp"
RM="rm -f"
SCP="scp -q"
SSH="ssh"
TAR="tar"

[ -f "./config.sh" ] && . "./config.sh"


#functions
_deploy()
{
	DAPORTALDIR="$1"
	REMOTE="$2"
	TARX="$TAR -xzpf"

	[ "$VERBOSE" -ne 0 ] && TARX="$TAR -xzvf"
	$DEBUG $MAKE dist					|| return 2
	tmpfile=$($SSH "$REMOTE" "$MKTEMP -p $TMPDIR daportal-deploy.XXXXXX")
	[ $? -eq 0 ]						|| return 2
	$DEBUG $SCP -- "$ARCHIVE" "$REMOTE:$tmpfile"
	if [ $? -ne 0 ]; then
		#try to delete the remote temporary file
		$DEBUG $SSH "$REMOTE" "$RM -- '$tmpfile'"
		return 2
	fi
	$DEBUG $SSH "$REMOTE" "sh -c \"$MKDIR -- '$DAPORTALDIR' &&
		cd '$DAPORTALDIR' &&
		$TARX '$tmpfile' &&
		$RM -- '$tmpfile' &&
		$CP -R -- '$PACKAGE-$VERSION/AUTHORS' \
			'$PACKAGE-$VERSION/BUGS' \
			'$PACKAGE-$VERSION/COPYING' \
			'$PACKAGE-$VERSION/INSTALL' \
			'$PACKAGE-$VERSION/Makefile' \
			'$PACKAGE-$VERSION/README.md' \
			'$PACKAGE-$VERSION/config.sh' \
			'$PACKAGE-$VERSION/data/' \
			'$PACKAGE-$VERSION/doc/' \
			'$PACKAGE-$VERSION/po/' \
			'$PACKAGE-$VERSION/project.conf' \
			'$PACKAGE-$VERSION/src/' \
			'$PACKAGE-$VERSION/tests/' \
			'$PACKAGE-$VERSION/tools/' . &&
			$MAKE &&
			$RM -r -- $PACKAGE-$VERSION\""		|| return 2
	$DEBUG $RM -- "$ARCHIVE"
}


#tests
_tests()
{
	(cd "tests" && $MAKE distclean all)
}


#debug
_debug()
{
	[ "$VERBOSE" -ne 0 ] && echo "$@"
	"$@"
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
	echo "Usage: $PROGNAME [-f][-P prefix][-v] hostname" 1>&2
	echo "  -f    Skip running the tests before deploying" 1>&2
	echo "  -v    Verbose mode" 1>&2
	return 1
}


#main
if [ -z "$PACKAGE" -o -z "$VERSION" ]; then
	_error "PACKAGE and VERSION must be set"
	exit $?
fi
ARCHIVE="$PACKAGE-$VERSION.tar.gz"
while getopts "fP:v" name; do
	case "$name" in
		f)
			FORCE=1
			;;
		P)
			PREFIX="$OPTARG"
			;;
		v)
			DEBUG="_debug"
			SCP="scp"
			VERBOSE=1
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
DAPORTALDIR="$PREFIX/share/daportal"
REMOTE="$1"

if [ $FORCE -eq 0 ]; then
	#check for uncommitted changes
	if [ -d ".git" ]; then
		$GIT diff --quiet
		if [ $? -ne 0 ]; then
			_error "Could not deploy: Uncommitted changes"
			return $?
		fi
	fi
	#run the tests locally
	if [ $VERBOSE -ne 0 ]; then
		_tests
	else
		_tests > "$DEVNULL"
	fi
	if [ $? -ne 0 ]; then
		_error "Could not deploy: Some tests failed"
		exit $?
	fi
fi
_deploy "$DAPORTALDIR" "$REMOTE"
