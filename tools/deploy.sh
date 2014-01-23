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
PACKAGE=
VERSION=
PREFIX="/usr/local"
VERBOSE=0
[ -f "./config.sh" ] && . "./config.sh"

CP="cp -f"
MAKE="make"
MKDIR="mkdir -p"
RM="rm -f"
SCP="scp -q"
SSH="ssh"
TAR="tar"
TMPDIR="/tmp"


#functions
_deploy()
{
	DAPORTALDIR="$1"
	REMOTE="$2"
	TARX="$TAR -xzf"

	[ "$VERBOSE" -ne 0 ] && TARX="$TAR -xzvf"
	$DEBUG $MAKE dist					|| return 2
	$DEBUG $SCP -- "$ARCHIVE" "$REMOTE:$TMPDIR"		|| return 2
	$DEBUG $SSH "$REMOTE" "sh -c \"$MKDIR -- '$DAPORTALDIR' &&
		cd '$DAPORTALDIR' &&
		$TARX '$TMPDIR/$ARCHIVE' &&
		$RM -- '$TMPDIR/$ARCHIVE' &&
		$CP -r -- '$PACKAGE-$VERSION/AUTHORS' \
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
			$RM -r -- $PACKAGE-$VERSION\""		|| return 2
	$DEBUG $RM -- "$ARCHIVE"
}


#debug
_debug()
{
	[ "$VERBOSE" -ne 0 ] && echo "$@"
	"$@"
}


#usage
_usage()
{
	echo "Usage: deploy.sh [-P prefix][-v] hostname" 1>&2
	echo "  -v    Verbose mode" 1>&2
	return 1
}


#main
if [ -z "$PACKAGE" -o -z "$VERSION" ]; then
	echo "deploy.sh: PACKAGE and VERSION must be set" 1>&2
	exit 2
fi
ARCHIVE="$PACKAGE-$VERSION.tar.gz"
while getopts "P:v" name; do
	case "$name" in
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
DAPORTALDIR="$PREFIX/daportal"
REMOTE="$1"

_deploy "$DAPORTALDIR" "$REMOTE"
