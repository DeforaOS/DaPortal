<?php //$Id$
//Copyright (c) 2013 Pierre Pronchery <khorben@defora.org>
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



require_once('./system/common.php');
require_once('./system/content.php');
require_once('./system/mime.php');


//DownloadContent
abstract class DownloadContent extends Content
{
	//protected
	//methods
	//accessors
	//DownloadContent::getIcon
	protected function getIcon($engine, $size = 16)
	{
		if($this->isDirectory())
			return Mime::getIconByType($engine,
				'inode/directory', $size);
		return Mime::getIcon($engine, $this->getTitle(), $size);
	}


	//DownloadContent::getPermissions
	protected function getPermissions($mode = FALSE)
	{
		if($mode === FALSE)
			$mode = $this->get('mode');
		return Common::getPermissions($mode, $this->S_IFDIR);
	}


	//DownloadContent::isDirectory
	protected function isDirectory()
	{
		return ($this->get('mode') & $this->S_IFDIR) ? TRUE : FALSE;
	}


	//properties
	protected $S_IFDIR = 512;
}

?>
