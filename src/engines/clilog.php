<?php //$Id$
//Copyright (c) 2014-2015 Pierre Pronchery <khorben@defora.org>
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



//CLILogEngine
class CLILogEngine extends CLIEngine
{
	//essential
	//CLILogEngine::match
	public function match()
	{
		//never match by default
		return 0;
	}


	//useful
	//CLILogEngine::log
	public function log($priority, $message)
	{
		$priority = $this->logPriority($priority);
		switch($priority)
		{
			case LOG_DEBUG:
				if(!$this->getDebug())
					return FALSE;
				break;
			case LOG_INFO:
				if($this->verbose < 2
						&& !$this->getDebug())
					return FALSE;
				break;
		}
		if(!is_string($message))
		{
			ob_start();
			var_dump($message); //XXX potentially multi-line
			$message = ob_get_contents();
			ob_end_clean();
		}
		$message = $_SERVER['SCRIPT_FILENAME'].": $message";
		syslog($priority, $message);
		return FALSE;
	}
}

?>
