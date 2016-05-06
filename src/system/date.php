<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
//This file is part of DeforaOS Web DaPortal
//
//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, version 3 of the License.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with this program.  If not, see <http://www.gnu.org/licenses/>.



//Date
class Date
{
	//public
	//methods
	//useful
	//Date::formatDate
	static public function format($date, $outformat = FALSE,
			$informat = FALSE, $utc = FALSE)
	{
		$informats = array('%Y-%m-%dT%H:%M:%S', '%Y-%m-%d %H:%M:%S');

		if($informat !== FALSE)
			$informats = array($informat);
		foreach($informats as $informat)
		{
			if(($tm = strptime($date, $informat)) === FALSE)
				continue;
			$timestamp = gmmktime($tm['tm_hour'], $tm['tm_min'],
					$tm['tm_sec'], $tm['tm_mon'] + 1,
					$tm['tm_mday'], $tm['tm_year'] + 1900);
			return static::formatTimestamp($timestamp, $outformat,
					$utc);
		}
		return $date; //XXX better suggestions welcome
	}


	//Date::formatTimestamp
	static public function formatTimestamp($timestamp, $format = FALSE,
			$utc = FALSE)
	{
		global $config;
		$callback = ($utc || $config->get('defaults::date', 'utc'))
			? 'gmstrftime' : 'strftime';

		if($format === FALSE)
			if(($format = $config->get('defaults::date', 'format'))
					=== FALSE)
				$format = '%d/%m/%Y %H:%M:%S';
		return $callback($format, $timestamp);
	}
}

?>
