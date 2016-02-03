<?php //$Id$
//Copyright (c) 2013-2016 Pierre Pronchery <khorben@defora.org>
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



//SCMProject
abstract class SCMProject
{
	//public
	//essential
	//SCMProject::attach
	public function attach($engine)
	{
		$this->engine = $engine;
	}


	//actions
	abstract public function browse($project, $request);
	abstract public function download($project, $request);
	abstract public function timeline($project, $request);


	//useful
	//SCMProject::listAll
	static public function listAll($engine, $module)
	{
		$name = $module->getName();
		$folder = 'modules/'.$name.'/scm';

		if(($dir = opendir($folder)) === FALSE)
			return FALSE;
		$ret = array();
		while(($de = readdir($dir)) !== FALSE)
			if(strlen($de) > 4 && substr($de, -4) == '.php')
				$ret[] = substr($de, 0, -4);
		closedir($dir);
		return $ret;
	}


	//protected
	//properties
	protected $engine;
}

?>
