#!/bin/sh
#$Id$
#Copyright (c) 2014-2016 Pierre Pronchery <khorben@defora.org>
#
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
PROGNAME="shlint.sh"
PROJECTCONF="../project.conf"
#executables
DATE="date"
DEBUG="_debug"
FIND="find"
SHLINT="sh -n"
SORT="sort -n"
TR="tr"


#functions
#shlint
_shlint()
{
	ret=0
	subdirs="data doc src tests tools"

	$DATE
	echo
	while read line; do
		case "$line" in
			"["*)
				break
				;;
			"subdirs="*)
				subdirs=${line#subdirs=}
				subdirs=$(echo "$subdirs" | $TR ',' ' ')
				;;
		esac
	done < "$PROJECTCONF"
	for subdir in $subdirs; do
		[ -d "../$subdir" ] || continue
		for filename in $($FIND "../$subdir" -name '*.sh' | $SORT); do
			_shlint_file "$filename"
			if [ $? -eq 0 ]; then
				echo "$filename:"
			else
				echo "$PROGNAME: $filename: FAIL" 1>&2
				ret=2
			fi
		done
	done
	return $ret
}

_shlint_file()
{
	$DEBUG $SHLINT "$filename" 2>&1				|| return 2
	#try to detect invalid use of return
	#XXX this test is not accurate (therefore a warning)
	warn=0
	while read line; do
		case "$line" in
			*return*)
				warn=1
				;;
			*)
				warn=0
				;;
		esac
	done < "$filename"
	if [ $warn -ne 0 ]; then
		_warning "$filename: return instead of exit in the global scope"
	fi
	return 0
}


#debug
_debug()
{
	echo "$@" 1>&3
	"$@"
	res=$?
	#ignore errors when the command is not available
	[ $res -eq 127 ]					&& return 0
	return $res
}


#usage
_usage()
{
	echo "Usage: $PROGNAME [-c] target" 1>&2
	return 1
}


#warning
_warning()
{
	echo "$PROGNAME: $@" 1>&2
	return 2
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

#clean
[ $clean -ne 0 ] && exit 0

exec 3>&1
_shlint > "$target"
