<?php //$Id$
//Copyright (c) 2012-2016 Pierre Pronchery <khorben@defora.org>
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



//HTMLFormat
class HTMLFormat extends FormatElements
{
	//protected
	//properties
	protected $doctype = FALSE;
	protected $encoding = FALSE;
	static protected $filter_class = 'HTML';


	//methods
	//essential
	//HTMLFormat::match
	protected function match(Engine $engine, $type = FALSE)
	{
		switch($type)
		{
			case 'text/html':
				return 10;
			default:
				return 0;
		}
	}


	//HTMLFormat::attach
	protected function attach(Engine $engine, $type = FALSE)
	{
		global $config;

		if(($this->encoding = $config->get('defaults', 'charset'))
				=== FALSE)
			$this->encoding = ini_get('default_charset');
		if($this->encoding == '')
			$this->encoding = FALSE;
		//configuration
		$this->javascript = $this->configGet('javascript')
			? TRUE : FALSE;
		//for escaping
		if(!defined('ENT_HTML401'))
			define('ENT_HTML401', 0);
	}


	//accessors
	//HTMLFormat::configGet
	protected function configGet($variable)
	{
		global $config;

		if(($ret = parent::configGet($variable)) !== FALSE)
			return $ret;
		return $config->get('format::html', $variable);
	}


	//HTMLFormat::getJavascript
	protected function getJavascript()
	{
		return $this->javascript;
	}


	//useful
	//escaping
	//HTMLFormat::escape
	protected function escape($text)
	{
		return htmlspecialchars($text,
				ENT_COMPAT | ENT_HTML401 | ENT_NOQUOTES,
				$this->encoding);
	}


	//HTMLFormat::escapeAttribute
	protected function escapeAttribute($text)
	{
		return htmlspecialchars($text, ENT_COMPAT | ENT_HTML401,
				$this->encoding);
	}


	//HTMLFormat::escapeComment
	protected function escapeComment($text)
	{
		$from = '-->';
		$to = '--&gt;';

		return '<!--'.str_replace($from, $to, $text).'-->';
	}


	//HTMLFormat::escapeText
	protected function escapeText($text)
	{
		$from = array('&', '<', '>', "\n", "\r");
		$to = array('&amp;', '&lt;', '&gt;', "<br />\n", '');

		return str_replace($from, $to, $text);
	}


	//HTMLFormat::escapeURI
	protected function escapeURI($text)
	{
		return rawurlencode($text);
	}


	//rendering
	//HTMLFormat::render
	public function render(Engine $engine, PageElement $page,
			$filename = FALSE)
	{
		$this->ids = array();
		$this->tags = array();
		$this->titles = array();
		$this->engine = $engine;
		$meta = array('application-name', 'author', 'description',
			'generator', 'keywords');
		//FIXME also track tags are properly closed

		if($this->doctype !== FALSE)
			print($this->doctype);
		$this->tagOpen('html');
		$this->renderTabs();
		$this->tagOpen('head');
		if($this->encoding !== FALSE)
			$this->renderMetaCharset($this->encoding);
		$this->_renderTitle($page);
		$this->_renderBase($page);
		$this->_renderTheme($page);
		$this->_renderIcontheme($page);
		$this->_renderFavicon($page);
		$this->_renderJavascript($page);
		if(($location = $page->get('location')) !== FALSE)
			$this->renderMeta('Location', $location);
		if(($refresh = $page->get('refresh')) !== FALSE
				&& is_numeric($refresh))
			$this->renderMeta('Refresh', $refresh);
		//meta-information
		foreach($meta as $m)
			if(($content = $this->configGet('meta::'.$m)) !== FALSE)
			{
				$this->renderTabs();
				$this->tag('meta', FALSE, FALSE, array(
						'name' => $m,
						'content' => $content));
			}
		//viewport
		if(($vw = $this->configGet('viewport::width')) !== FALSE)
		{
			$this->renderTabs();
			$this->tag('meta', FALSE, FALSE, array(
					'name' => 'viewport',
					'content' => "width=$vw"));
		}
		$this->renderTabs(-1);
		$this->tagClose('head');
		$this->renderTabs();
		$this->tagOpen('body');
		parent::render($engine, $page, $filename);
		$this->renderTabs(-1);
		$this->tagClose('body');
		$this->renderTabs(-1);
		$this->tagClose('html');
		$this->engine = FALSE;
	}

	private function _renderBase(PageElement $page)
	{
		$url = dirname($this->engine->getURL()).'/'; //XXX
		$this->renderTabs();
		$this->tag('base', FALSE, FALSE, array('href' => $url));
	}

	private function _renderFavicon(PageElement $page)
	{
		if(($favicon = $this->configGet('favicon')) === FALSE)
			return;
		//FIXME emit a (debugging) warning if the icon is not readable?
		$this->renderTabs();
		$this->tag('link', FALSE, FALSE, array('rel' => 'shortcut icon',
					'href' => $favicon));
	}

	private function _renderIcontheme(PageElement $page)
	{
		global $config;
		//XXX this should be set by the HTTP engine
		$cdn = $this->configGet('cdn') ?: '';
		$standalone = $this->get('standalone');

		if(($icontheme = $config->get(FALSE, 'icontheme')) === FALSE)
			return;
		//FIXME emit a (debugging) warning if the theme is not readable?
		$this->renderTabs();
		$filename = "icons/$icontheme.css";
		if(!$standalone || ($css = @file_get_contents(
				'../data/'.$filename)) === FALSE)
			$css = " @import url('$cdn$filename'); ";
		$this->tagOpen('style', FALSE, FALSE, array(
				'type' => 'text/css'));
		print($this->escapeComment($css.'//'));
		$this->tagClose('style');
	}

	private function _renderJavascript(PageElement $page)
	{
		$standalone = $this->get('standalone');

		if($this->javascript === FALSE)
			return;
		$this->_renderJavascriptScript('js/jquery.js', $standalone);
		$this->_renderJavascriptScript('js/DaPortal.js', $standalone);
	}

	private function _renderJavascriptScript($src, $standalone = FALSE)
	{
		//XXX this should be set by the HTTP engine
		$cdn = $this->configGet('cdn') ?: '';
		$tag = 'script';
		$type = 'text/javascript';

		$this->renderTabs();
		//FIXME emit a (debugging) warning if the file is not readable?
		if($standalone && ($js = @file_get_contents('../data/'.$src))
				!== FALSE)
		{
			$this->tagOpen($tag, FALSE, FALSE, array(
					'type' => $type));
			print($this->escapeComment($js.'//'));
		}
		else
			$this->tagOpen($tag, FALSE, FALSE, array(
					'type' => $type, 'src' => $cdn.$src));
		$this->tagClose($tag);
	}

	private function _renderTheme(PageElement $page)
	{
		global $config;

		if(($theme = $config->get(FALSE, 'theme')) !== FALSE)
			$this->_renderThemeCSS($theme);
		if($this->configGet('alternate_themes') == 1)
			$this->_renderThemeAlternate($page, $theme);
	}

	private function _renderThemeAlternate(PageElement $page,
			$theme = FALSE)
	{
		$themes = '../data/themes';

		if(($dir = @opendir($themes)) === FALSE)
			return;
		$themes = array();
		while(($de = readdir($dir)) !== FALSE)
			if(substr($de, -4, 4) == '.css'
					&& ($theme === FALSE
						|| $de != "$theme.css"))
				$themes[] = substr($de, 0, -4);
		sort($themes, SORT_STRING);
		foreach($themes as $theme)
			$this->_renderThemeCSS($theme, TRUE);
		closedir($dir);
	}

	private function _renderThemeCSS($theme, $alternate = FALSE)
	{
		//XXX this should be set by the HTTP engine
		$cdn = $this->configGet('cdn') ?: '';
		$rel = $alternate ? 'alternate stylesheet' : 'stylesheet';
		$standalone = $this->get('standalone');

		$this->renderTabs();
		$filename = "themes/$theme.css";
		if($standalone && ($css = @file_get_contents(
				'../data/'.$filename)) !== FALSE)
		{
			$this->tagOpen('style', FALSE, FALSE, array(
					'type' => 'text/css',
					'title' => $theme));
			print($this->escapeComment($css.'//'));
			$this->tagClose('style');
		}
		else
			$this->tag('link', FALSE, FALSE, array('rel' => $rel,
					'href' => $cdn.$filename,
					'title' => $theme));
	}

	private function _renderTitle(PageElement $e)
	{
		global $config;

		if(($title = $e->get('title')) === FALSE)
			$title = $config->get(FALSE, 'title');
		if($title !== FALSE)
		{
			$this->renderTabs();
			$this->tag('title', FALSE, $e->get('id'), FALSE,
					$title);
		}
	}


	//protected
	//methods
	//rendering
	protected function renderBlock(PageElement $e, $tag = 'div')
	{
		$class = $e->getType();

		if(($c = $e->get('class')) !== FALSE)
			$class .= ' '.$c;
		$this->renderTabs();
		$this->tagOpen($tag, $class);
		$this->renderChildren($e);
		$this->renderTabs();
		$this->tagClose($tag);
	}


	protected function renderBox(PageElement $e, $type = 'vbox')
	{
		$this->renderTabs();
		if(($class = $e->get('class')) !== FALSE)
			$class = $type.' '.$class;
		else
			$class = $type;
		$this->tagOpen('div', $class, $e->get('id'));
		$children = $e->getChildren();
		foreach($children as $c)
		{
			$this->renderTabs();
			$this->tagOpen('div', 'pack');
			$this->renderElement($c);
			if(count($c->getChildren()) > 1)
				$this->renderTabs();
			$this->tagClose('div');
		}
		$this->renderTabs();
		$this->tagClose('div');
	}


	protected function renderButton(PageElement $e)
	{
		$id = $e->get('id');
		$text = $e->get('text');
		$tooltip = $e->get('tooltip');
		$cancel = 'if(history.length > 1) { history.go(-1);'
			.' return false; }';

		if(($r = $e->get('request')) !== FALSE)
			$url = $this->engine->getURL($r, FALSE);
		else
			$url = $e->get('url');
		$class = 'button';
		if(($s = $e->get('stock')) !== FALSE)
			$class .= ' stock16 '.$s;
		if(($c = $e->get('class')) !== FALSE && $c != $s)
			$class .= ' '.$c;
		$tag = 'a';
		$args = array();
		if($tooltip !== FALSE)
			$args['title'] = $tooltip;
		switch(($type = $e->get('type')))
		{
			case 'reset':
			case 'submit':
				$tag = 'input';
				$url = FALSE;
				if($s === FALSE && $c === FALSE)
					$class .= ' stock16 '.$type;
				$args['type'] = $type;
				if(($name = $e->get('value')) !== FALSE)
					$args['name'] = $name;
				if($text !== FALSE)
					$args['value'] = $text;
				$this->tag($tag, $class, $id, $args);
				break;
			case 'button':
			case FALSE:
				if($this->javascript && $e->get('target')
						== '_cancel')
					$args['onclick'] = $cancel;
				//fallthrough
			default:
				$type = 'button';
				if($s === FALSE && $c === FALSE)
					$class .= ' stock16 '.$type;
				if($url !== FALSE)
					$args['href'] = $url;
				$this->tagOpen($tag, $class, $id, $args);
				$this->renderChildren($e);
				if($text !== FALSE)
					print($this->escape($text));
				$this->tagClose($tag);
				break;
		}
	}


	protected function renderCheckbox(PageElement $e)
	{
		$this->renderTabs();
		$this->tagOpen('div', $e->getType());
		print('<input type="checkbox"');
		if(($name = $e->get('name')) !== FALSE)
		{
			//FIXME the ID may not be unique
			print(' id="'.$this->escapeAttribute($name).'"');
			print(' name="'.$this->escapeAttribute($name).'"');
		}
		if(($value = $e->get('value')) !== FALSE)
			print(' checked="checked"');
		print('/>');
		if(($text = $e->get('text')) !== FALSE)
		{
			$l = new PageElement('label', array('text' => $text));
			if($name !== FALSE)
				$l->set('for', $name);
			$this->renderElement($l);
		}
		$this->tagClose('div');
	}


	protected function renderChildren(PageElement $e)
	{
		$children = $e->getChildren();
		foreach($children as $c)
			$this->renderElement($c);
	}


	protected function renderCombobox(PageElement $e)
	{
		$tag = 'select';
		$class = $e->get('class');
		$list = FALSE;

		$class = ($class !== FALSE) ? $class.' ' : '';
		$this->tagOpen('span', $class.$e->getType(), $e->get('id'),
				FALSE, $e->get('text'));
		$this->renderTabs();
		$name = $e->get('name');
		$value = $e->get('value');
		if($e->get('editable'))
		{
			$list = uniqid();
			$this->tag('input', FALSE, FALSE, array(
					'type' => 'text', 'list' => $list,
					'name' => $name));
			$tag = 'datalist';
		}
		$this->tagOpen($tag, FALSE, $list, array('name' => $name));
		$children = $e->getChildren();
		foreach($children as $c)
		{
			$this->renderTabs();
			$text = $c->get('text');
			if(($v = $c->get('value')) === FALSE)
				$v = $text;
			$args = array('value' => $v);
			if($value !== FALSE && $value == $v)
				$args['selected'] = 'selected';
			$this->tag('option', $c->get('class'), $c->get('id'),
					$args, $text);
		}
		$this->renderTabs();
		$this->tagClose($tag);
		$this->tagClose('span');
	}


	protected function renderData(PageElement $e)
	{
		//FIXME implement
	}


	protected function renderDialog(PageElement $e)
	{
		if(($type = $e->get('type')) === FALSE)
			$type = 'message';
		if(($title = $e->get('title')) === FALSE)
			switch($type)
			{
				case 'error':
					$title = _('Error');
					break;
				case 'question':
					$title = _('Question');
					break;
				case 'warning':
					$title = _('Warning');
					break;
				case 'info':
				case 'message':
					$title = _('Message');
					break;
			}
		$this->renderTabs();
		$this->tagOpen('div', 'dialog '.$type);
		$title = new PageElement('title', array('stock' => $type,
				'text' => $title));
		$this->renderElement($title);
		$this->renderTabs();
		$this->tagOpen('div', 'message', FALSE, FALSE, $e->get('text'));
		$this->tagClose('div');
		$this->renderTabs();
		if(count($e->getChildren()) > 0)
			$this->renderChildren($e);
		else if($this->get('close') && !$this->get('standalone'))
			//close button (if there is no child)
			$this->tag('button', 'stock16 close hidden', FALSE,
					array('type' => 'button'), _('Close'));
		$this->tagClose('div');
	}


	protected function renderEntry(PageElement $e)
	{
		$class = ($e->get('class') !== FALSE)
			? 'entry '.$e->get('class') : 'entry';

		$this->renderTabs();
		$this->tagOpen('div', $e->getType());
		if(($text = $e->get('text')) === FALSE)
			//default to the placeholder text
			$text = $e->get('placeholder');
		if($text !== FALSE)
		{
			$l = new PageElement('label', array(
					'class' => $e->get('class'),
					'text' => $text));
			$this->renderElement($l);
		}
		$name = $e->get('name');
		$value = $e->get('value');
		$type = ($e->get('hidden') === TRUE) ? 'password' : 'text';
		$attributes = array('type' => $type, 'name' => $name,
			'value' => $value);
		if(($size = $e->get('size')) !== FALSE && is_numeric($size))
			$attributes['size'] = $size;
		if(($width = $e->get('width')) !== FALSE && is_numeric($width))
			$attributes['style'] = 'width: '.$width.'ex';
		$this->tag('input', $class, $e->get('id'), $attributes);
		if($this->javascript && is_string($name)
				&& substr($name, -2) == '[]')
			$this->tag('input', 'stock16 add hidden', FALSE, array(
					'type' => 'button',
					'value' => _('More')));
		$this->tagClose('div');
	}


	protected function renderExpander(PageElement $e)
	{
		$this->renderTabs();
		$this->tagOpen('div', 'expander');
		if(($title = $e->get('title')) !== FALSE)
		{
			$this->renderTabs();
			$this->tag('div', 'title', FALSE, FALSE, $title);
		}
		$this->renderChildren($e);
		$this->renderTabs();
		$this->tagClose('div');
	}


	protected function renderFileChooser(PageElement $e)
	{
		$class = ($e->get('class') !== FALSE)
			? 'filechooser '.$e->get('class') : 'filechooser';

		$this->renderTabs();
		$this->tagOpen('div', $e->getType());
		if(($text = $e->get('text')) !== FALSE)
		{
			$l = new PageElement('label', array('text' => $text));
			$this->renderElement($l);
		}
		$name = $e->get('name');
		$type = 'file';
		$this->tag('input', $class, $e->get('id'),
				array('type' => $type, 'name' => $name));
		if($this->javascript && is_string($name)
				&& substr($name, -2) == '[]')
			$this->tag('input', 'stock16 add hidden', FALSE, array(
					'type' => 'button',
					'value' => _('More')));
		$this->tagClose('div');
	}


	protected function renderForm(PageElement $e)
	{
		$request = $e->get('request');
		$secure = $e->get('secure');
		$action = 'index.php';

		$this->renderTabs();
		if($secure && !isset($_SERVER['HTTPS']))
		{
			//XXX requires insider from HTTPEngine
			$_SERVER['HTTPS'] = 1;
			$port = isset($_SERVER['SERVER_PORT'])
				? $_SERVER['SERVER_PORT'] : FALSE;
			$_SERVER['SERVER_PORT'] = 443;
			$action = $this->engine->getURL();
			//restore the previous state
			if($port !== FALSE)
				$_SERVER['SERVER_PORT'] = $port;
			else
				unset($_SERVER['SERVER_PORT']);
			unset($_SERVER['HTTPS']);
		}
		$method = $e->get('idempotent') ? 'get' : 'post';
		$args = array('action' => $action, 'method' => $method,
			'enctype' => $this->_formEnctype($e));
		$this->tagOpen('form', $e->getType(), $e->get('id'), $args);
		if($method === 'post')
		{
			$auth = $this->engine->getAuth();
			$token = sha1(uniqid(php_uname(), TRUE));
			if(($tokens = $auth->getVariable($this->engine,
						'tokens')) === FALSE)
				$tokens = array();
			$tokens[$token] = time() + 3600;
			$auth->setVariable($this->engine, 'tokens', $tokens);
			$this->_formHidden('_token', $token);
		}
		if($request !== FALSE)
		{
			$this->_formHidden('_module', $request->getModule());
			$this->_formHidden('_action', $request->getAction());
			$this->_formHidden('_id', $request->getID());
			$this->_formHidden('_title', $request->getTitle());
			if(($args = $request->getParameters()) !== FALSE)
				foreach($args as $k => $v)
					$this->_formHidden($k, $v);
		}
		$this->renderChildren($e);
		$this->renderTabs();
		$this->tagClose('form');
	}

	protected function _formEnctype(PageElement $e)
	{
		//XXX look for any file upload field
		foreach($e->getChildren() as $c)
			if($c->getType() == 'filechooser')
				return 'multipart/form-data';
			else if(($ret = $this->_formEnctype($c)) !== FALSE)
				return $ret;
		return FALSE;
	}

	private function _formHidden($name, $value = FALSE)
	{
		if($value === FALSE)
			return;
		$this->renderTabs();
		$this->tag('input', FALSE, FALSE, array('type' => 'hidden',
					'name' => $name, 'value' => $value));
	}


	protected function renderFrame(PageElement $e)
	{
		$this->renderTabs();
		$this->tagOpen('div', 'frame');
		if(($title = $e->get('title')) !== FALSE)
		{
			$this->renderTabs();
			$this->tag('div', 'title', FALSE, FALSE, $title);
		}
		$this->renderChildren($e);
		$this->renderTabs();
		$this->tagClose('div');
	}


	protected function renderHbox(PageElement $e)
	{
		$this->renderBox($e, $e->getType());
	}


	protected function renderHtmledit(PageElement $e)
	{
		$filter_class = static::$filter_class;
		$class = $e->get('class');

		$class = ($class === FALSE) ? 'editor' : 'editor '.$class;
		$this->renderTabs();
		if(($text = $e->get('text')) !== FALSE)
		{
			$l = new PageElement('label', array('text' => $text));
			$this->renderElement($l);
		}
		if(($text = $e->get('value')) === FALSE || !is_string($text))
			$text = '';
		$text = $filter_class::filter($this->engine, $text);
		$this->tagOpen('textarea', $class, $e->get('id'), array(
				'name' => $e->get('name')));
		print($this->escape($text));
		$this->tagClose('textarea');
		if($this->javascript)
			$this->_htmleditJavascript($e, $class);
	}

	private function _htmleditJavascript(PageElement $e, $class)
	{
		$actions = array('cut' => _('Cut'), 'copy' => _('Copy'),
			'paste' => _('Paste'),
			'undo' => _('Undo'), 'redo' => _('Redo'),
			'insert-hrule' => _('Insert ruler'),
			'insert-link' => _('Insert link'),
			'insert-image' => _('Insert image'),
			'insert-table' => _('Insert table'),
			'insert-text' => _('Insert text'));
		$styles = array('' => _('Style'),
			'<h1>' => _('Heading 1'), '<h2>' => _('Heading 2'),
			'<h3>' => _('Heading 3'), '<h4>' => _('Heading 4'),
			'<h5>' => _('Heading 5'), '<h6>' => _('Heading 6'),
			'<p>' => _('Normal'), '<pre>' => _('Preformatted'),
			'<blockquote>' => _('Quotation'));
		$fonts = array('' => _('Font'),
			'cursive' => _('Cursive'),
			'fantasy' => _('Fantasy'),
			'monospace' => _('Monospace'),
			'sans-serif' => _('Sans serif'),
			'serif' => _('Serif'));
		$sizes = array('' => _('Size'),
			1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7);
		$format = array('bold' => _('Bold'),
			'italic' => _('Italic'),
			'underline' => _('Underline'),
			'strikethrough' => _('Strikethrough'),
			'subscript' => _('Subscript'),
			'superscript' => _('Superscript'),
			'remove-format' => _('Remove format'),
			'justify-left' => _('Align left'),
			'justify-center' => _('Center'),
			'justify-right' => _('Align right'),
			'justify-fill' => _('Justify'),
			'numbering' => _('Numbering'),
			'bullets' => _('Bullets'),
			'unindent' => _('Unindent'),
			'indent' => _('Indent'));

		$toolbar = $this->_htmleditToolbar($actions);
		$this->renderElement($toolbar);
		//format
		$toolbar = $this->_htmleditToolbar($format);
		//size
		$toolbar->prepend($this->_htmleditSelector('fontsize', $sizes));
		//font
		$toolbar->prepend($this->_htmleditSelector('fontname', $fonts));
		//style
		$toolbar->prepend($this->_htmleditSelector('formatblock',
					$styles));
		$this->renderElement($toolbar);
		$this->renderTabs();
		$this->tagOpen('iframe', $class, FALSE, array(
				'width' => '100%', 'height' => '400px'));
		$this->tagClose('iframe');
	}

	private function _htmleditSelector($class, $values = array())
	{
		$combobox = new PageElement('combobox', array(
				'class' => $class));

		foreach($values as $v => $t)
			$combobox->append('label', array('value' => $v,
					'text' => $t));
		return $combobox;
	}

	private function _htmleditToolbar($actions = array())
	{
		$toolbar = new PageElement('toolbar', array(
				'class' => 'editor'));

		foreach($actions as $a => $t)
		{
			$button = new PageElement('button', array('class' => $a,
				'stock' => $a, 'tooltip' => $t));
			$toolbar->append($button);
		}
		return $toolbar;
	}


	protected function renderHtmlview(PageElement $e)
	{
		$filter_class = static::$filter_class;
		$class = $e->get('class');
		$class = $e->getType().(($class !== FALSE) ? ' '.$class : '');
		$text = $e->get('text');

		if(!is_string($text))
			return;
		$this->tagOpen('div', $class, $e->get('id'), FALSE);
		if($e->get('trusted') === TRUE)
			print($text);
		else
			print($filter_class::filter($this->engine, $text));
		$this->tagClose('div');
	}


	protected function renderIconview(PageElement $e)
	{
		$e->set('view', 'icons');
		if(($columns = $e->get('columns')) === FALSE)
		{
			$columns = array('icon' => 'Icon', 'label' => 'Label');
			$e->set('columns', $columns);
		}
		$this->renderTreeview($e);
	}


	protected function renderImage(PageElement $e)
	{
		$size = 48;
		$class = FALSE;
		$attributes = array('alt' => $e->get('text'));
		$standalone = $this->get('standalone');

		if(($title = $e->get('title')) !== FALSE)
			$attributes['title'] = $title;
		if(($stock = $e->get('stock')) !== FALSE)
		{
			if(($s = $e->get('size')) !== FALSE && is_numeric($s))
				$size = $s;
			$class = "stock$size $stock";
			//XXX to display the "alt" text only when relevant
			$attributes['src'] = 'icons/generic/'.$size.'x'.$size
				.'/stock.png';
			$filename = '../data/'.$attributes['src'];
			if($standalone && ($img = file_get_contents($filename))
					!== FALSE)
				//FIXME use the actual icon
				$attributes['src'] = 'data:image/png'
					.';base64,'.base64_encode($img);
			$attributes['width'] = 0;
			$attributes['height'] = 0;
		}
		else if(($r = $e->get('request')) !== FALSE)
			$attributes['src'] = $this->engine->getURL($r, FALSE);
		else
			$attributes['src'] = $e->get('source');
		$this->tag('img', $class, $e->get('id'), $attributes);
	}


	protected function renderInline(PageElement $e)
	{
		$text = $e->get('text');

		if($e->getType() !== FALSE)
		{
			$this->tagOpen('span', $e->getType(), $e->get('id'),
					FALSE, $text);
			$this->renderChildren($e);
			$this->tagClose('span');
		}
		else if($text !== FALSE)
			print($this->escapeText($text));
	}


	protected function renderLabel(PageElement $e)
	{
		$tag = 'span';
		$class = 'label';
		$attributes = array();

		if(($c = $e->get('class')) !== FALSE)
			//FIXME apply to the respective parent and children
			$class = 'label '.$c;
		if(($for = $e->get('for')) !== FALSE)
		{
			$tag = 'label';
			$attributes['for'] = $for;
		}
		$this->tagOpen($tag, $class, $e->get('id'), $attributes,
				$e->get('text'));
		$this->tagClose($tag);
		$this->renderChildren($e);
	}


	protected function renderLink(PageElement $e)
	{
		$standalone = $this->get('standalone');
		$url = FALSE;

		$attributes = array();
		if(($a = $e->get('name')) !== FALSE)
			$attributes['name'] = $a;
		if(($r = $e->get('request')) !== FALSE)
		{
			$url = $this->engine->getURL($r, $standalone);
			$attributes['href'] = $url;
		}
		else if(($url = $e->get('url')) !== FALSE)
			$attributes['href'] = $url;
		if(($title = $e->get('title')) !== FALSE)
			$attributes['title'] = $title;
		$this->tagOpen('a', FALSE, $e->get('id'), $attributes);
		if(($stock = $e->get('stock')) !== FALSE)
			$this->tag('img', 'stock16 '.$stock, FALSE,
					array('alt' => ''));
		if(($text = $e->get('text')) === FALSE)
			$text = $url;
		if($text !== FALSE)
			print($this->escapeText($text));
		$this->renderChildren($e);
		$this->tagClose('a');
	}


	protected function renderMenu(PageElement $e, $class = FALSE)
	{
		//FIXME really implement
		if(($children = $e->getChildren()) === FALSE
				|| count($children) == 0)
			return;
		$this->tagOpen('ul', $class);
		foreach($children as $c)
			if($c->getType() == 'menuitem')
				$this->renderMenuitem($c);
		$this->renderTabs(-1);
		$this->tagClose('ul');
	}


	protected function renderMenubar(PageElement $e)
	{
		$this->renderTabs();
		$this->renderMenu($e, 'menubar');
	}


	protected function renderMenuitem(PageElement $e)
	{
		$this->renderTabs();
		$this->tagOpen('li', 'menuitem');
		if(($text = $e->get('text')) !== FALSE)
		{
			$class = $e->get('class');
			$attributes = array();
			if(($a = $e->get('name')) !== FALSE)
				$attributes['name'] = $a;
			if($e->get('important') !== FALSE)
				$class = is_string($class) ? $class.' important'
					: 'important';
			if(($r = $e->get('request')) !== FALSE)
				$attributes['href'] = $this->engine->getURL($r,
						FALSE);
			else if(($u = $e->get('url')) !== FALSE)
				$attributes['href'] = $u;
			if(count($attributes))
				$this->tagOpen('a', $class, $e->get('id'),
						$attributes);
			print($this->escapeText($text));
			if(count($attributes))
				$this->tagClose('a');
		}
		$this->renderMenu($e);
		$this->tagClose('li');
	}


	protected function renderMeta($header, $value)
	{
		$this->renderTabs();
		$this->tag('meta', FALSE, FALSE, array('http-equiv' => $header,
					'content' => $value));
	}


	protected function renderMetaCharset($charset)
	{
		$this->renderMeta('Content-Type', 'text/html; charset='
				.$charset);
	}


	protected function renderPage(PageElement $e)
	{
		$this->renderChildren($e);
	}


	protected function renderProgress(PageElement $e)
	{
		//XXX render something nicer
		$this->renderInline($e);
	}


	protected function renderRadioButton(PageElement $e)
	{
		$args = array('type' => 'radio');
		$value = $e->get('value');

		if(($children = $e->getChildren()) === FALSE)
			return;
		if(($name = $e->get('name')) !== FALSE)
			$args['name'] = $name;
		foreach($children as $c)
		{
			$a = $args;
			$class = $c->get('class');
			$id = $c->get('id');
			if(($v = $c->get('value')) !== FALSE)
			{
				$a['value'] = $v;
				if($v == $value)
					$a['checked'] = 'checked';
			}
			$this->renderTabs();
			$this->tag('input', $class, $id, $a);
			$this->renderElement($c);
		}
	}


	protected function renderStatusbar(PageElement $e)
	{
		$class = 'statusbar';

		$this->renderTabs();
		if(($c = $e->get('class')) !== FALSE)
			$class .= ' '.$c;
		$this->tagOpen('div', $class, $e->get('id'), FALSE,
				$e->get('text'));
		$this->renderChildren($e);
		$this->tagClose('div');
	}


	protected function renderTabs($more = 0)
	{
		print("\n");
		for($i = 0; $i < count($this->tags) + $more; $i++)
			print("\t");
	}


	protected function renderTextview(PageElement $e)
	{
		$this->renderTabs();
		$this->tagOpen('div', $e->getType());
		if(($text = $e->get('text')) !== FALSE)
		{
			$l = new PageElement('label', array('text' => $text));
			$this->renderElement($l);
		}
		$value = $e->get('value');
		$args = array();
		if(($name = $e->get('name')) !== FALSE)
			$args['name'] = $name;
		//text has to be escaped without any tags
		$this->tagOpen('textarea', $e->get('class'), $e->get('id'),
				$args);
		if($value !== FALSE)
			print($this->escape($value));
		$this->tagClose('textarea');
		$this->tagClose('div');
	}


	protected function renderTitle(PageElement $e)
	{
		$hcnt = count($this->titles);
		$tcnt = count($this->tags);
		$level = 1;

		/* XXX this algorithm is a bit ugly but seems to work */
		if($hcnt == 0) /* no title set */
			$this->titles[$level - 1] = $tcnt;
		else if($this->titles[$hcnt - 1] < $tcnt) //deeper level
		{
			$this->titles[$hcnt] = $tcnt;
			$level = $hcnt;
		}
		else if($this->titles[$hcnt - 1] == $tcnt) //same level
			$level = $hcnt - 1;
		else
		{
			for(; $hcnt > 0; $hcnt = count($this->titles))
			{
				$h = $this->titles[$hcnt - 1];
				if($h <= $tcnt)
					break;
				unset($this->titles[$hcnt - 1]);
			}
			$level = $hcnt - 1;
		}
		$tag = "h$level";
		if(($class = $e->get('class')) === FALSE)
			$class = '';
		if(($stock = $e->get('stock')) !== FALSE)
			switch($level)
			{
				case 1: $class = "stock48 $stock $class"; break;
				case 2: $class = "stock32 $stock $class"; break;
				case 3: $class = "stock24 $stock $class"; break;
				case 4:
				default:$class = "stock16 $stock $class"; break;
			}
		$class = rtrim($class);
		$this->renderTabs();
		$this->tagOpen($tag, $class, $e->get('id'), FALSE,
				$e->get('text'));
		$this->renderChildren($e);
		$this->tagClose($tag);
	}


	protected function renderToolbar(PageElement $e)
	{
		return $this->renderBlock($e);
	}


	protected function renderTreeview(PageElement $e)
	{
		$auth = $this->engine->getAuth();

		switch(($view = $e->get('view')))
		{
			case 'details':
			case 'preview':
				break;
			case 'icons':
			case 'list':
				if(($c = $e->get('columns')) !== FALSE)
					break;
				$c = array('icon' => '', 'label' => '');
				$e->set('columns', $c);
				break;
			case 'thumbnails':
				if(($c = $e->get('columns')) !== FALSE)
					break;
				$c = array('thumbnail' => '', 'label' => '');
				$e->set('columns', $c);
				break;
			default:
				$view = 'details';
				break;
		}
		$r = $e->get('request');
		$tag = ($r !== FALSE) ? 'form' : 'div';
		$class = "treeview $view";
		if($e->get('alternate'))
			$class .= ' alternate';
		if(($c = $e->get('class')) !== FALSE && strlen($c))
			$class .= " $c";
		$args = FALSE;
		if($r !== FALSE)
		{
			$method = $e->get('idempotent') ? 'get' : 'post';
			$args = array('action' => 'index.php',
				'method' => $method);
		}
		$this->renderTabs();
		$this->tagOpen($tag, $class, $e->get('id'), $args);
		if($r !== FALSE && $method === 'post')
		{
			$token = sha1(uniqid(php_uname(), TRUE));
			if(($tokens = $auth->getVariable($this->engine,
						'tokens')) === FALSE)
				$tokens = array();
			$tokens[$token] = time() + 3600;
			$auth->setVariable($this->engine, 'tokens', $tokens);
			$this->_renderTreeviewHidden('_token', $token);
		}
		if($r !== FALSE)
		{
			//FIXME copied from renderForm()
			$this->_renderTreeviewHidden('_module', $r->getModule());
			$this->_renderTreeviewHidden('_action', $r->getAction());
			$this->_renderTreeviewHidden('_id', $r->getID());
			$this->_renderTreeviewHidden('_title', $r->getTitle());
			if(($parameters = $r->getParameters()) !== FALSE)
				foreach($r->getParameters() as $k => $v)
					$this->_renderTreeviewHidden($k, $v);
		}
		$this->_renderTreeviewToolbar($e);
		$columns = $e->get('columns');
		if(!is_array($columns) || count($columns) == 0)
			$columns = array('title' => 'Title');
		$this->renderTabs();
		$this->tagOpen('div', 'table');
		$this->_renderTreeviewHeaders($e, $columns);
		//render rows
		$this->_renderTreeviewRows($e, $columns);
		$this->renderTabs(-1);
		$this->tagClose('div');
		$this->renderTabs(-1);
		//render the (optional) controls
		$this->_renderTreeviewControls($e);
		$this->tagClose($tag);
	}

	private function _renderTreeviewControls(PageElement $e)
	{
		if(($children = $e->getChildren()) === FALSE)
			return;
		foreach($children as $c)
			switch($c->getType())
			{
				case 'row':
				case 'toolbar':
					break;
				default:
					$this->renderElement($c);
					break;
			}
	}

	private function _renderTreeviewHeaders(PageElement $e, $columns)
	{
		$this->renderTabs();
		$this->tagOpen('div', 'header');
		if($e->get('request') !== FALSE)
			$this->tag('span', 'detail', FALSE, FALSE, '');
		foreach($columns as $c => $v)
			$this->tag('span', "detail $c", FALSE, FALSE, $v);
		$this->tagClose('div');
	}

	private function _renderTreeviewHidden($name, $value = FALSE)
	{
		//FIXME copied from _formHidden()
		if($value === FALSE)
			return;
		$this->renderTabs();
		$this->tag('input', FALSE, FALSE, array('type' => 'hidden',
					'name' => $name, 'value' => $value));
	}

	private function _renderTreeviewRows(PageElement $e, $columns)
	{
		$id = 1;

		$this->_renderTreeviewRowsDo($e, $columns, $id);
	}

	private function _renderTreeviewRowsDo(PageElement $e, $columns, &$id,
			$level = 0)
	{
		$class = 'row';
		$request = $e->get('request');

		if(($children = $e->getChildren()) === FALSE)
			return;
		if($level > 0)
			$class .= ' level level'.$level;
		foreach($children as $c)
		{
			$this->renderTabs();
			if($c->getType() != 'row')
				continue;
			$cl = $class;
			if(($p = $c->get('class')) !== FALSE)
				$cl .= ' '.$p;
			$this->tagOpen('div', $cl);
			if($request !== FALSE)
			{
				$this->tagOpen('span', 'detail');
				$name = $c->get('id');
				$args = array('type' => 'checkbox',
					'name' => $name);
				if($c->get('value'))
					$args['checked'] = 'checked';
				$this->tag('input', FALSE, '_check_'.$id,
						$args);
				$this->tagClose('span');
			}
			foreach($columns as $k => $v)
			{
				if(($v = $c->get($k)) === FALSE)
					$v = '';
				$this->tagOpen('span', "detail $k");
				if($v instanceof PageElement)
					$this->renderElement($v);
				else if(is_scalar($v))
					$this->tag('label', FALSE, FALSE,
							array('for' => '_check_'
								.$id), $v);
				$this->tagClose('span');
			}
			$this->tagClose('div');
			$id++;
			$this->_renderTreeviewRowsDo($c, $columns, $id,
					$level + 1);
		}
	}

	private function _renderTreeviewToolbar(PageElement $e)
	{
		if(($children = $e->getChildren()) === FALSE)
			return;
		foreach($children as $c)
			if($c->getType() == 'toolbar')
				$this->renderToolbar($c);
	}


	protected function renderVbox(PageElement $e)
	{
		$this->renderBox($e, $e->getType());
	}


	//tagging
	protected function tag($name, $class = FALSE, $id = FALSE,
			$attributes = FALSE, $content = FALSE)
	{
		$tag = '<'.$this->escapeAttribute($name);

		if($class !== FALSE)
			$tag .= ' class="'.$this->escapeAttribute($class).'"';
		if($id !== FALSE)
		{
			if(isset($ids[$id]))
				$this->engine->log(LOG_DEBUG, 'HTML ID '.$id
						.' is already defined');
			$ids[$id] = TRUE;
			$tag .= ' id="'.$this->escapeAttribute($id).'"';
		}
		if(is_array($attributes))
			foreach($attributes as $k => $v)
				$tag .= ' '.$this->escapeAttribute($k).'="'
					.$this->escapeAttribute($v).'"';
		if($content !== FALSE)
			$tag .= '>'.$this->escapeText($content)
				.'</'.$this->escapeAttribute($name).'>';
		else
			$tag .= '/>';
		print($tag);
	}


	protected function tagClose($name)
	{
		print('</'.$this->escapeAttribute($name).'>');
		if(array_pop($this->tags) != $name)
			$this->engine->log(LOG_DEBUG, 'Invalid tag sequence');
	}


	protected function tagOpen($name, $class = FALSE, $id = FALSE,
			$attributes = FALSE, $content = FALSE)
	{
		array_push($this->tags, $name);
		//FIXME automatically output tabs
		$tag = '<'.$this->escapeAttribute($name);
		if($class !== FALSE && strlen($class) > 0)
			$tag .= ' class="'.$this->escapeAttribute($class).'"';
		if($id !== FALSE)
		{
			if(isset($ids[$id]))
				$this->engine->log(LOG_DEBUG, 'HTML ID '.$id
						.' is already defined');
			$ids[$id] = TRUE;
			$tag .= ' id="'.$this->escapeAttribute($id).'"';
		}
		if(is_array($attributes))
			foreach($attributes as $k => $v)
				$tag .= ' '.$this->escapeAttribute($k).'="'
					.$this->escapeAttribute($v).'"';
		$tag.='>';
		print($tag);
		if($content !== FALSE)
			print($this->escapeText($content));
	}


	//private
	//properties
	private $engine = FALSE;
	private $ids;
	private $javascript = FALSE;
	private $tags;
	private $titles;
}

?>
