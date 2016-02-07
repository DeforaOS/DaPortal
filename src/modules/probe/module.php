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



//ProbeModule
class ProbeModule extends Module
{
	//public
	//methods
	//ProbeModule::call
	function call(Engine $engine, Request $request, $internal = 0)
	{
		//XXX should be saved in the constructor
		$this->engine = $engine;
		if(($action = $request->getAction()) === FALSE)
			$action = 'default';
		if($internal)
			switch($action)
			{
				case 'actions':
					return $this->$action($request);
				default:
					return FALSE;
			}
		$method = 'call'.$action;
		if(!method_exists($this, $method))
			return new ErrorResponse(_('Invalid action'),
				Response::$CODE_ENOENT);
		return $this->$method($request);
	}


	//protected
	//properties
	static protected $times = array('day', 'week', 'month', 'year');


	//methods
	//accessors
	protected function getRoot()
	{
		if(($root = $this->configGet('root')) === FALSE
				|| strlen($root) == 0)
		{
			$this->engine->log('LOG_ERR',
					'The RRD repository is not configured');
			return FALSE;
		}
		return $root;
	}


	//actions
	//ProbeModule::actions
	protected function actions($request)
	{
		return array();
	}


	//calls
	//ProbeModule::callDefault
	protected function callDefault(Request $request)
	{
		$root = $this->getRoot();
		$title = _('Monitoring');
		$hostname = $request->get('host');

		if($root === FALSE)
			return new ErrorResponse(_('Internal server error'));
		$page = new Page(array('title' => $title));
		$page->append('title', array('stock' => 'monitor',
				'text' => $title));
		$vbox = $page->append('vbox');
		$this->_defaultForm($hostname, $vbox);
		if(!is_string($hostname) || strlen($hostname) == 0)
			$this->_defaultList($root, $vbox);
		else
			$this->_defaultHost($request, $root, $hostname, $vbox);
		return new PageResponse($page);
	}

	private function _defaultForm($hostname, PageElement $page)
	{
		$form = $page->append('form', array(
				'idempotent' => TRUE,
				'request' => $this->getRequest()));
		$hbox = $form->append('hbox');
		$hbox->append('entry', array('text' => _('Host: '),
				'placeholder' => _('Hostname'),
				'name' => 'host', 'value' => $hostname));
		$hbox->append('button', array('type' => 'submit',
				'text' => _('Monitor')));
	}

	private function _defaultHost(Request $request, $root, $hostname,
			PageElement $page)
	{
		$time = $request->get('time');
		$graphs = array(
			'load' => array('title' => _('Load average')),
			'procs' => array('title' => _('Process count')),
			'upgrades' => array(
				'title' => _('Package upgrades pending')),
			'users' => array('title' => _('Users logged')),
			'volume' => array('title' => _('Volume usage: ').'/')
		);

		$page->append('title', array('text' => $hostname));
		$this->_defaultToolbar($page, $hostname);
		if(!in_array($time, static::$times, TRUE))
			$time = FALSE;
		if(strstr($hostname, '/') !== FALSE
				|| $hostname == '.' || $hostname == '..')
			//FIXME report (error dialog)
			return;
		if(($dir = opendir($root.'/'.$hostname)) === FALSE)
		{
			//FIXME report (error dialog)
			return;
		}
		while(($de = readdir($dir)) !== FALSE)
		{
			if($de == '.' || $de == '..')
				continue;
			if(is_dir($root.'/'.$hostname.'/'.$de))
				if($de == 'volume')
					$this->_defaultHostVolume($request,
							$root, $hostname, $time,
							$page, '');
				else
					continue;
			if(substr($de, -4) != '.rrd')
				continue;
			$graph = substr($de, 0, -4);
			$title = isset($graphs[$graph])
				? $graphs[$graph]['title'] : FALSE;
			$dialog = $page->append('dialog', array(
					'type' => 'info',
					'title' => $title,
					'text' => ''));
			$request = $this->getRequest('widget', array(
					'host' => $hostname, 'type' => $graph,
					'time' => $time));
			$link = $dialog->append('link', array(
					'request' => $request, 'text' => ''));
			$link->append('image', array('request' => $request,
					'text' => $title));
			//FIXME also append links in the toolbar
		}
		closedir($dir);
	}

	private function _defaultHostVolume(Request $request, $root, $hostname,
			$time, PageElement $page, $volume)
	{
		$parent = $root.'/'.$hostname.'/volume'.$volume;

		if(($dir = opendir($parent)) === FALSE)
		{
			//FIXME report (error dialog)
			return;
		}
		while(($de = readdir($dir)) !== FALSE)
		{
			if($de == '.' || $de == '..')
				continue;
			if(is_dir($parent.'/'.$de))
			{
				$this->_defaultHostVolume($request, $root,
						$hostname, $time, $page,
						$volume.'/'.$de);
				continue;
			}
			if(substr($de, -4) != '.rrd')
				continue;
			$v = $volume.'/'.substr($de, 0, -4);
			$title = _('Volume usage: ').$v;
			$dialog = $page->append('dialog', array(
					'type' => 'info',
					'title' => $title,
					'text' => ''));
			$request = $this->getRequest('widget', array(
					'host' => $hostname, 'type' => 'volume',
					'time' => $time, 'volume' => $v));
			$link = $dialog->append('link', array(
					'request' => $request, 'text' => ''));
			$link->append('image', array('request' => $request,
					'text' => $title));
		}
		closedir($dir);
	}

	private function _defaultList($root, PageElement $page)
	{
		$hosts = array();

		if(($dir = opendir($root)) === FALSE)
		{
			//FIXME report (error dialog)
			return;
		}
		while(($de = readdir($dir)) !== FALSE)
		{
			if($de == '.' || $de == '..')
				continue;
			if(is_dir($root.'/'.$de))
				$hosts[] = $de;
		}
		closedir($dir);
		$view = $page->append('iconview');
		$icon = new PageElement('image', array('stock' => 'server'));
		foreach($hosts as $hostname)
		{
			$request = $this->getRequest(FALSE,
				array('host' => $hostname));
			$link = new PageElement('link', array(
					'request' => $request,
					'text' => $hostname));
			$view->append('row', array('icon' => $icon,
					'label' => $link));
		}
	}

	private function _defaultToolbar(PageElement $page, $hostname)
	{
		$request = $this->getRequest();

		$toolbar = $page->append('toolbar');
		$toolbar->append('button', array('stock' => 'server',
				'text' => _('List hosts'),
				'request' => $request));
		foreach(static::$times as $time)
		{
			$r = $this->getRequest(FALSE, array('host' => $hostname,
					'time' => $time));
			$text = sprintf(_('Last %s'), $time);
			$toolbar->append('button', array('stock' => 'refresh',
					'text' => $text, 'request' => $r));
		}
	}


	//ProbeModule::callWidget
	public function callWidget(Request $request)
	{
		$root = $this->getRoot();
		$rrdtool = $this->configGet('rrdtool') ?: 'rrdtool';
		$hostname = $request->get('host');

		if($root === FALSE)
			return new ErrorResponse(_('Internal server error'));
		if(strstr($hostname, '/') !== FALSE
				|| $hostname == '.' || $hostname == '..')
			return new ErrorResponse('Invalid hostname');
		$rrd = $root.'/'.$hostname;
		$rrdtool .= ' graph - --imgformat PNG';
		switch($request->get('time'))
		{
			case 'month':
				$rrdtool .= ' --start -2419200';
				$title = '(last month)';
				break;
			case 'week':
				$rrdtool .= ' --start -604800';
				$title = '(last week)';
				break;
			case 'year':
				$rrdtool .= ' --start -31449600';
				$title = '(last year)';
				break;
			case 'day':
			default:
				$rrdtool .= ' --start -86400';
				$title = '(last day)';
				break;
		}
		switch($request->get('type'))
		{
			case 'load':
				$rrd .= '/load.rrd';
				$title = _('load average').' '.$title;
				$label = _('load');
				$rrdtool .= ' --lower-limit 0.0'
					.' '.escapeshellarg("DEF:load1=$rrd:load1:AVERAGE")
					.' '.escapeshellarg("DEF:load5=$rrd:load5:AVERAGE")
					.' '.escapeshellarg("DEF:load15=$rrd:load15:AVERAGE")
					.' '.escapeshellarg('CDEF:rload1=load1,1000,/')
					.' '.escapeshellarg('CDEF:rload5=load5,1000,/')
					.' '.escapeshellarg('CDEF:rload15=load15,1000,/')
					.' '.escapeshellarg('AREA:rload1#ffef00')
					.' '.escapeshellarg('AREA:rload5#ffbf00')
					.' '.escapeshellarg('AREA:rload15#ff8f00')
					.' '.escapeshellarg('LINE1:rload1#ffdf00:Load 1 min')
					.' '.escapeshellarg('GPRINT:rload1:LAST: %.2lf')
					.' '.escapeshellarg('LINE1:rload5#ffaf00:Load 5 min')
					.' '.escapeshellarg('GPRINT:rload5:LAST: %.2lf')
					.' '.escapeshellarg('LINE1:rload15#ff7f00:Load 15 min')
					.' '.escapeshellarg('GPRINT:rload15:LAST: %.2lf');
				break;
			case 'procs':
				$rrd .= '/procs.rrd';
				$title = _('process count').' '.$title;
				$label = _('processes');
				$rrdtool .= ' --lower-limit 0'
					.' '.escapeshellarg("DEF:procs=$rrd:procs:AVERAGE")
					.' '.escapeshellarg('AREA:procs#7f7fff')
					.' '.escapeshellarg('LINE1:procs#4f4fff:Process count')
					.' '.escapeshellarg('GPRINT:procs:LAST: %.0lf');
				break;
			case 'upgrades':
				$rrd .= '/upgrades.rrd';
				$title = _('package upgrades pending').' '
					.$title;
				$label = _('packages');
				$rrdtool .= ' --lower-limit 0'
					.' '.escapeshellarg("DEF:upgrades=$rrd:upgrades:AVERAGE")
					.' '.escapeshellarg('AREA:upgrades#7f7fff')
					.' '.escapeshellarg('LINE1:upgrades#4f4fff:Process count')
					.' '.escapeshellarg('GPRINT:upgrades:LAST: %.0lf');
				break;
			case 'users':
				$rrd .= '/users.rrd';
				$title = _('users logged').' '.$title;
				$label = _('users');
				$rrdtool .= ' --lower-limit 0'
					.' '.escapeshellarg("DEF:users=$rrd:users:AVERAGE")
					.' '.escapeshellarg('AREA:users#ff7f7f')
					.' '.escapeshellarg('LINE1:users#ff4f4f:Users logged')
					.' '.escapeshellarg('GPRINT:users:LAST: %.0lf');
				break;
			case 'volume':
				if(($volume = $request->get('volume')) === FALSE
						|| strlen($volume) == 0)
				{
					$volume = '/';
					$rrd .= '/volume.rrd';
				}
				else if(strstr($volume, '/..') !== FALSE)
					return new ErrorResponse($error);
				else
					$rrd .= '/volume/'.$volume.'.rrd';
				$title = _('volume used: ').$volume.' '.$title;
				$label = _('bytes');
				$rrdtool .= ' --lower-limit 0'
					.' '.escapeshellarg("DEF:used=$rrd:used:AVERAGE")
					.' '.escapeshellarg("DEF:total=$rrd:total:AVERAGE")
					.' '.escapeshellarg("CDEF:gbused=used,1024,/,1024,/,1024,/")
					.' '.escapeshellarg("CDEF:gbtotal=total,1024,/,1024,/,1024,/")
					.' '.escapeshellarg('AREA:used#7f7fff')
					.' '.escapeshellarg('LINE1:used#4f4fff:Volume used')
					.' '.escapeshellarg('GPRINT:gbused:LAST:%.2lf GB')
					.' '.escapeshellarg('LINE1:total#4f4fff:Volume total')
					.' '.escapeshellarg('GPRINT:gbtotal:LAST:%.2lf GB');
				break;
			default:
				$error = _('Could not create graph for this type');
				return new ErrorResponse($error);
		}
		$title = $hostname.' '.$title;
		$rrdtool .= ' --title '.escapeshellarg($title)
			.' --vertical-label '.escapeshellarg($label);
		//render the graph
		$this->engine->log('LOG_DEBUG', $rrdtool);
		if(($fp = popen($rrdtool, 'r')) === FALSE)
		{
			$error = _('Could not create the graph');
			return new ErrorResponse($error);
		}
		$response = new CachePipeResponse($fp);
		$response->setType('image/png');
		return $response;
	}
}

?>
