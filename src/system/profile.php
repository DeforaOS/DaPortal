<?php //$Id$
//Copyright (c) 2013-2014 Pierre Pronchery <khorben@defora.org>
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



//Profile
class Profile
{
	//public
	//methods
	//static
	//Profile::start
	static public function start(Engine $engine)
	{
		Profile::$time = microtime(TRUE);
	}


	//Profile::stop
	static public function stop(Engine $engine)
	{
		$database = $engine->getDatabase();
		$query = Profile::$query_insert;
		if(function_exists('sys_getloadavg'))
		{
			$load = sys_getloadavg();
			$load[0] = round($load[0] * 1000);
			$load[1] = round($load[1] * 1000);
			$load[2] = round($load[2] * 1000);
		}
		else
			$load = array(NULL, NULL, NULL);
		$time = (Profile::$time !== FALSE)
			? microtime(TRUE) - Profile::$time : NULL;
		$args = array('load1' => $load[0], 'load5' => $load[1],
			'load15' => $load[2], 'time' => round($time * 1000),
			'mem_usage' => memory_get_usage(),
			'mem_usage_real' => memory_get_usage(TRUE),
			'mem_peak' => memory_get_peak_usage(),
			'mem_peak_real' => memory_get_peak_usage(TRUE));

		//XXX ignore errors
		$database->query($engine, $query, $args);
	}


	//private
	//variables
	//static
	static private $query_insert = 'INSERT INTO daportal_profile
		(load1, load5, load15, time, mem_usage, mem_usage_real,
		mem_peak, mem_peak_real)
		VALUES (:load1, :load5, :load15, :time,
			:mem_usage, :mem_usage_real,
			:mem_peak, :mem_peak_real)';
	static private $time = FALSE;
}

?>
