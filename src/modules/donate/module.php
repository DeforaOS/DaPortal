<?php //$Id$
//Copyright (c) 2016 Pierre Pronchery <khorben@defora.org>
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



//DonateModule
class DonateModule extends Module
{
	//public
	//methods
	//DonateModule::call
	public function call(Engine $engine, Request $request, $internal = 0)
	{
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'widget':
					return $this->$action();
				default:
					return FALSE;
			}
		switch($action)
		{
			case 'thanks':
				$action = 'call'.$action;
				return $this->$action($engine, $request);
			default:
				return new ErrorResponse(_('Invalid action'),
					Response::$CODE_ENOENT);
		}
	}


	//protected
	//methods
	//calls
	//DonateModule::callThanks
	protected function callThanks(Engine $engine, Request $request)
	{
		$title = 'Thank you!';
		$text = $this->configGet('message')
			?: 'Thank you so much for donating!';
		$trusted = $this->configGet('filter') ? FALSE : TRUE;

		$page = new Page(array('title' => $title));
		$page->append('title', array('text' => $title,
				'stock' => $this->name));
		if($text === FALSE)
		{
			$text = 'Thank you so much for donating!';
			$page->append('label', array('text' => $text));
			$text = 'We really appreciate your support.';
			$page->append('label', array('text' => $text));
			$page->append('link', array('request' => new Request(),
					'stock' => 'back',
					'text' => 'Back to the homepage'));
		}
		else
			$page->append('htmlview', array('text' => $text,
					'trusted' => $trusted));
		return new PageResponse($page);
	}


	//useful
	//widget
	protected function widget()
	{
		$text = $this->configGet('widget');
		$trusted = $this->configGet('filter') ? FALSE : TRUE;

		if($text === FALSE)
			return FALSE;
		return new PageElement('htmlview', array('text' => $text,
				'trusted' => $trusted));
	}
}

?>
