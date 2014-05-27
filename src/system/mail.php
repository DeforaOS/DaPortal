<?php //$Id$
//Copyright (c) 2012-2014 Pierre Pronchery <khorben@defora.org>
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



@include_once('Mail.php');
@include_once('Mail/mime.php');


//Mail
class Mail
{
	//public
	//methods
	//static
	//useful
	//Mail::send
	static public function send($engine, $from, $to, $subject, $page,
			$headers = FALSE, $attachments = FALSE)
	{
		global $config;

		if(($from = $config->get('defaults::email', 'from')) === FALSE
				&& isset($_SERVER['SERVER_ADMIN']))
			$from = $_SERVER['SERVER_ADMIN'];
		if($from === FALSE)
		{
			$pw = posix_getpwuid(posix_getuid());
			$from = $pw['name'];
		}
		//verify parameters
		$error = 'Could not send e-mail (invalid parameters)';
		if(strpos($from, "\n") !== FALSE || strpos($to, "\n") !== FALSE
				|| strpos($subject, "\n") !== FALSE)
			return $engine->log('LOG_ERR', $error);
		//verify the headers
		if(!is_array($headers))
			$headers = array();
		foreach($headers as $h => $v)
			if(strpos($h, "\n") !== FALSE
					|| strpos($v, "\n") !== FALSE)
				return $engine->log('LOG_ERR', $error);
		//prepare the headers
		if(($charset = $config->get('defaults', 'charset')) === FALSE)
			$charset = 'UTF-8';
		else
			$charset = strtoupper($charset);
		$headers['Content-Type'] = "text/plain; charset=$charset\n";
		//prepare the message
		$page = Mail::render($engine, $page, $headers, $attachments);
		//assemble the headers
		$hdr = "From: $from\n";
		if(is_array($headers))
			foreach($headers as $h => $v)
				$hdr .= "$h: $v\n";
		//send the message
		$error = 'Could not send e-mail';
		if(mail($to, $subject, $page, $hdr) === FALSE)
			return $engine->log('LOG_ERR', $error);
		$engine->log('LOG_DEBUG', 'e-mail sent to '.$to);
		return TRUE;
	}


	//protected
	//methods
	//static
	//useful
	//Mail::pageToHTML
	static protected function pageToHTML($engine, $page)
	{
		if(($format = Format::attachDefault($engine, 'text/html'))
				=== FALSE)
			return FALSE;
		ob_start();
		$format->render($engine, $page);
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}


	//Mail::pageToText
	static protected function pageToText($engine, $page)
	{
		if(($format = Format::attachDefault($engine, 'text/plain'))
				=== FALSE)
			return FALSE;
		$format->setParameter('wrap', 72);
		ob_start();
		$format->render($engine, $page);
		$str = ob_get_contents();
		ob_end_clean();
		return $str;
	}


	//Mail::render
	static protected function render($engine, $page, &$headers,
			$attachments = FALSE)
	{
		$text = Mail::pageToText($engine, $page);
		if(!class_exists('Mail_Mime'))
		{
			$engine->log('LOG_WARNING',
					'Mail_Mime: Class not found');
			return $text;
		}
		$mime = new Mail_Mime(array('eol' => "\n"));
		//plain text content
		$mime->setTXTBody($text);
		//HTML contents
		$html = Mail::pageToHTML($engine, $page);
		$mime->setHTMLBody($html);
		//attachments
		if(!is_array($attachments))
			$attachments = array();
		foreach($attachments as $filename => $data)
		{
			if(!is_string($filename) || !is_string($data))
				continue;
			$type = Mime::getType($engine, $filename,
					'application/octet-stream');
			if(($e = $mime->addAttachment($data, $type, $filename,
					FALSE)) !== TRUE)
				$engine->log('LOG_ERR', $e->getMessage());
		}
		//headers
		$hdrs = $mime->headers(array());
		foreach($hdrs as $h => $v)
			$headers[$h] = $v;
		return $mime->get();
	}
}

?>
