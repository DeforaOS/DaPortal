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



require_once('./tests.php');


class TestObservable implements Observable
{
	public function addObserver(Observer $observer)
	{
		$this->observers[] = $observer;
	}

	public function notifyObservers()
	{
		foreach($this->observers as $o)
			$o->notify($this);
	}

	public function removeObserver(Observer $observer)
	{
		//XXX not supported
	}

	private $observers = array();
}

class TestObserver implements Observer
{
	public function getProperty()
	{
		return $this->property;
	}

	public function notify(Observable $observable)
	{
		$this->property++;
	}

	private $property = 0;
}


//functions
function observer()
{
	$observable = new TestObservable;
	$observer1 = new TestObserver;
	$observer2 = new TestObserver;

	$observable->addObserver($observer1);
	$observable->addObserver($observer2);
	$observable->notifyObservers();
	return $observer1->getProperty() == 1 && $observer2->getProperty() == 1;
}

function test($engine)
{
	if(observer() === FALSE)
		exit(2);
}

test($engine);
exit(0);

?>
