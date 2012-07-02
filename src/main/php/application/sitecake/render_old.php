<?php

include_once('config.php');
define('PHP_TEMPLATE', true);

ini_set('display_errors','On');

define('APP_ROOT', dirname(__FILE__));
define('ROOT', realpath(APP_ROOT . '/../../../webroot'));
define('TEMP_DIR', ROOT . '/sitecake-content/temp');
define('PUBLIC_DIR', ROOT . '/sitecake-content/public');
define('DRAFT_DIR', ROOT . '/sitecake-content/draft');
define('VERSIONS_DIR', ROOT . '/sitecake-content/versions');
define('PUBLIC_CACHE', TEMP_DIR);
define('TEMPLATES', TEMP_DIR .'/templates.json');
define('PUBLIC_PAGES', PUBLIC_DIR . '/pages.json');
define('DRAFT_PAGES', DRAFT_DIR . '/pages.json');
define('SITEMAP', ROOT . '/sitemap.xml');

set_include_path(implode(PATH_SEPARATOR, array('.', APP_ROOT .'/../../lib', get_include_path())));

if (!defined('TEST')) {
  if ( !file_exists(PUBLIC_DIR) ) mkdir(PUBLIC_DIR, 0777, true);
  if ( !file_exists(DRAFT_DIR) ) mkdir(DRAFT_DIR, 0777, true);
  if ( !file_exists(TEMP_DIR) ) mkdir(TEMP_DIR, 0777, true);
  if ( !file_exists(VERSIONS_DIR) ) mkdir(VERSIONS_DIR, 0777, true);

  process();
}

/**
 * Represents a HTTP request.
 */
class Request {
  /**
   * The request's server name (e.g. www.example.com).
   * @var string
   */
  public $server;
  
  /**
   * The request's host part (e.g. the host part in http://www.example.com:88/path/index.php
   * is www.example.com:88).
   * @var string
   */
  public $host;

  /**
   * The request's URI part (e.g. the URI part in http://www.example.com:88/path/index.php?something
   * is /path/index.php?something).
   * @var string
   */
  public $uri;

  /**
   * The URL path (e.g. the path in http://www.example.com/path/index.php?something is
   * /path/index.html).
   * @var string
   */
  public $path;

  /**
   * The relative path to the web root (e.g. the base path in http://www.example.com/some/path/index.php
   * is /some/path).
   * @var string
   */
  public $basePath;

  /**
   * The page indentifier (e.g. the page in http://www.example.com/some/path/index.php?something is
   * index). The default key value (i.e. when the path is '/') is 'index'.
   * @var string
   */
  public $page;

  /**
   * The page access postfix (e.g. in http://g.com/about.php?something it is '.php', in 
   * http://g.com/about?something is an empty string.
   * @var string
   */
  public $access;

  public static function get($serverGlobal) {
    $request = new Request;
    $request->query = $serverGlobal['QUERY_STRING'];
    $request->uri = empty($serverGlobal['REQUEST_URI']) ? '/' : $serverGlobal['REQUEST_URI'];
    $request->server = $serverGlobal['SERVER_NAME'];
    $request->host = $serverGlobal['HTTP_HOST'];
    $request->path = empty($request->query) ? $request->uri : substr($request->uri, 0, strpos($request->uri, '?'));
    $request->page = (substr($request->path, -1) == '/') ? '' : basename($request->path, '.php');
    $request->page = (empty($request->page) || $request->page == 'index') ? '__home' : $request->page;
    $request->access = strpos($serverGlobal['SCRIPT_NAME'], basename(__FILE__)) ? '' : '.php';
    $request->basePath = substr($request->path, 0, strrpos($request->path, '/') + 1);
    return $request;    
  }
}

/**
 * The HTTP response.
 */
class Response {
  public $code = 200;
  public $status = 'OK';
  public $body;

  public function __construct($content, $code = 200, $status = 'OK') {
    $this->content = $content;
    $this->code = $code;
    $this->status = $status;
  }
}

class Page {
  public $id;
  public $external;
  public $uri;
  public $hidden;
  public $navTitle;
  public $navDisplay;
  public $template;
  public $phpTemplate;
  public $title;
  public $description;
  public $keywords;
}

function process() {
  send(response(Request::get($_SERVER)));
}

function send($response) {
  header(sprintf('HTTP/1.1 %d %s', $response->code, $response->status));
  echo $response->content;
}

function notFoundResponse($uri) {
  $body = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head><title>404 Not Found</title></head>
<body><h1>Not Found</h1><p>The requested URL ' . $uri . ' was not found on this server.</p></body></html>';
  return new Response($body, 404, 'Not Found');
}

function response($request) {
  try {
    return isPublicRequest($request->query) ? publicResponse($request) : adminResponse($request);
  } catch (Exception $e) {
    return new Response("Error: " . $e->getMessage() . "<br/>" . $e->getTraceAsString());
  }
}

function isPublicRequest($query) {
  return empty($query);
}

function publicResponse($request) {
  $assemble = false;
  if (defined('PHP_TEMPLATE') && constant('PHP_TEMPLATE')) {
    $pages = publicPages($request);
    $rpage =  ($request->page == '__home') ? array_shift(array_keys($pages)) : $request->page;
    $page = $pages[$rpage];
    $assemble = $page->phpTemplate;
  }
  return $assemble ? assemblePublic($request) : cachedPublic($request);
}

function cachedPublic($request) {
  $cachePath = PUBLIC_CACHE . '/' . $request->page . '.cached';
  $page = is_readable($cachePath) ? file_get_contents($cachePath) : false;
  if (!$page) {
    $page = pageExists($request->page, publicPages($request)) ? file_get_contents($cachePath) : false;
  }
  return $page ? new Response($page) : notFoundResponse($request->uri);
}

Function isLoginPhase() {
  Return !sessionExists();
}

function sessionExists() {
  if ( !isset($_COOKIE['PHPSESSID']) ) 
    return false;
  session_start();
  return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
}

function pageExists($page, $pages) {
  return ($page == '__home' && !empty($pages)) || array_key_exists($page, $pages);
}

function assemblePublic($request) {
  require_once('Zend/Loader/Autoloader.php');
  Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

  $pages = publicPages($request);
  if (!pageExists($request->page, $pages))
    return notFoundResponse($request->uri);

  $rpage =  ($request->page == '__home') ? array_shift(array_keys($pages)) : $request->page;

  return new Response(publicPage($pages[$rpage], renderNavMenu($pages, $rpage, $request)));
}

function adminResponse($request) {
  require_once('Zend/Loader/Autoloader.php');
  Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

  $pages = draftPages($request);
  if ( !pageExists($request->page, $pages) )
    return notFoundResponse($request->uri);
  $rpage =  ($request->page == '__home') ? array_shift(array_keys($pages)) : $request->page;
  return new Response(isLoginPhase() ? adminLoginPage($rpage, $pages) : adminEditPage($rpage, $pages));
}

function adminLoginPage($path, $pages) {
  return injectLogin(page($path, $pages));
}

function adminEditPage($path, $pages) {
  return injectEdit(page($path, $pages));
}


function publicPages($request) {
  return pages(PUBLIC_PAGES, $request);
}

function draftPages($request) {
  return pages(DRAFT_PAGES, $request);
}

function pages($path, $request) {
  require_once('Zend/Loader/Autoloader.php');
  Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

  if (!is_readable($path))
    initSite($request);
  $arrays = Zend_Json::decode(file_get_contents($path), Zend_Json::TYPE_ARRAY);
  $pages = array();
  foreach ($arrays as $key => $page)
    $pages[$key] = (object)$page;
  return $pages;
}

function saveDraftPages($pages) {
  file_put_contents(DRAFT_PAGES, Zend_Json::encode($pages));
}

function publicPage($page, $navMenu) {
  $template = pageTemplate($page->template);
  $content = pagePublicContent($page->uri);
  return renderPage($page, $template, $content, $navMenu);
}

function pageTemplate($template) {
  $path = ROOT . '/' . $template . '.html';
  if ( PHP_TEMPLATE ) {
    ob_start();
    include($path);
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
  } else {
    return file_get_contents($path);
  }
}

function getMetaTemplate($page, $templates) {
  
}

function pagePublicContent($page) {
  return array('content');
}

function renderPage($page, $template, $content, $navMenu) {
  require_once('phpQuery/phpQuery.php');
  
  phpQuery::newDocument($template);
  pq('.sc-nav')->html($navMenu);
  pq('.sc-content')->html($content[0]);
  pq('title')->remove();
  pq('meta[name="description"]')->remove();
  pq('meta[name="keywords"]')->remove();
  pq('head')->append('<title>' . $page->title . '</title>');
  pq('title')->after('<meta name="description" content="' . $page->description . '"/>');
  pq('title')->after('<meta name="keywords" content="' . $page->keywords . '"/>');

  return phpQuery::getDocument()->__toString();
}

function renderNavMenu($pages, $activePage, $request) {
  $result = '';
  $idx = 0;
  foreach ($pages as $key => $page) {
    $idx++;
    if ( !$page->hidden )
      $result .= (($key == $activePage) ? '<li class="active">' : '<li>') .
	'<a href="' . (($idx == 1) ? $request->basePath : ($page->uri . $request->access)) . '" title="' . $page->navTitle . '">' . $page->navDisplay . '</a></li>';
  }
  return $result;
}

function updateSitemap($request) {
  $sitemap = renderSitemap(publicPages($request), $request);
  if (!file_put_contents(SITEMAP, $sitemap))
    throw new Exception('Unable to save Sitemap to ' . SITEMAP);
  triggerSearchEngines();
}

function triggerSearchEngines() {
  // TODO: trigger search engines' sitemap protocol
  // http://www.google.com/webmasters/tools/ping?sitemap=[your sitemap url]
  // http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=[your sitemap url]
  // http://submissions.ask.com/ping?sitemap=[your sitemap url]
  // http://www.bing.com/webmaster/ping.aspx?siteMap=[your sitemap url]
}

function renderSitemap($pages, $request) {
  $result = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  foreach ($pages as $page) {
    if ($page == $pages[array_shift(array_keys($pages))])
      $result .= '<url><loc>' . 'http://' . $request->host . $request->basePath . '</loc>' .
	'<changefreq>monthly</changefreq></url>';
    elseif (!$page->hidden && !$page->external)
      $result .= '<url><loc>' . 'http://' . $request->host . $request->basePath . $page->uri . $request->access . '</loc>' .
	'<changefreq>monthly</changefreq></url>';
  }
  return $result . '</urlset>';
}

/**
 * Returns an map of existing theme page templates. 
 * All files found in TEMPLATES_DIR directory with the 'html' extension that do
 * not contain the HTML metatag 'template' are considered page templates.
 *
 * @return array
 */
$_templatesRet = null;
function templates() {
  global $_templatesRet;

  if (!empty($_templateRet))
    return $_templatesRet;

  if (!is_readable(TEMPLATES)) {
    $templates = array();
    $templateFiles = glob(ROOT . '/*.html');

    require_once('phpQuery/phpQuery.php');

    foreach ( $templateFiles as $templateFile ) {
      $html = file_get_contents($templateFile);
      if ( !$html ) continue;
      phpQuery::newDocument($html);
      if (pq('meta[name="template"]')->attr('name')) continue;
      $template = new stdClass;
      $template->name = basename($templateFile, '.html');
      $template->php = (strpos($html, '<?php') !== FALSE) && defined('PHP_TEMPLATE') && constant('PHP_TEMPLATE');
      $template->file = $templateFile;
      $templates[$template->name] = $template;
    }
    if (!empty($templates))
      file_put_contents(TEMPLATES, Zend_Json::encode($templates));
  } else {
    $templates = Zend_Json::decode(file_get_contents(TEMPLATES), Zend_Json::TYPE_OBJECT);
  }

  $_templatesRet = empty($templates) ? null : $templates;
  return $templates;
}


/**
 * Checks if the given URL is external from a SC theme standpoint.
 * Only relative URL that points to an HTML document are considered internal.
 */
function isExternalLink($url) {
  return strpos($url, '/') || strpos($url, 'http://') || (substr($url, -5) != '.html');
}

/**
 * Generates unique ID.
 */
function id() {
  return sha1(uniqid('', true));
}

function initPages($home, $templates) {
  require_once('phpQuery/phpQuery.php');

  $pages = array();
  $homeDoc = phpQuery::newDocumentFile($home);
  
  $page = new Page();
  $page->hidden = true;
  $page->external = false;
  $page->uri = 'home';
  $page->id = id();
  $tpl = pq('meta[name="template"]')->attr('content');
  $page->template = $tpl ? $tpl : $page->uri;
  if (!isset($templates[$page->template]))
    throw new Exception('Unable to find template ' . $page->template . ' specified in the demo page ' . $pate->uri);
  $template = $templates[$page->template];
  $page->phpTemplate = $template->php;
  $pages['home'] = $page;
  
  foreach ($homeDoc['ul.sc-nav:first li a'] as $navNode) {
    $node = pq($navNode);
    $page = new Page();
    $page->navDisplay = $node->text();
    $page->navTitle = $node->attr('title');
    $page->navTitle = !$page->navTitle ? $page->navDisplay : $page->navTitle;
    $href = $node->attr('href');
    $page->external = isExternalLink($href);
    $page->uri = $page->external ? $href : basename($href, '.html');
    $page->hidden = false;
    $page->id = id();

    if ( !$page->external ) {
      $pageFile = ROOT . '/' . $page->uri . '.html';
      if ( !$page->external && !is_readable($pageFile) )
	throw new Exception('Unable to open the demo page ' . $page->uri . ' (' . $pageFile . ') specified in the home nav menu');

      $pageHtml = file_get_contents($pageFile);
      phpQuery::newDocument($pageHtml);
      $tpl = pq('meta[name="template"]')->attr('content');
      $page->template = $tpl ? $tpl : $page->uri;
      if (!array_key_exists($page->template, $templates))
	throw new Exception('The template ' . $page->template . ' required by the demo page ' . $page->uri . ' does not exist');
      $template = $templates[$page->template];
      $page->phpTemplate = $template->php;
      
      $page->title = pq('title')->text();
      $page->title = !$page->title ? $page->navDisplay : $page->title;
      $page->description = pq('meta[name="description"]')->attr('content');
      $page->keywords = pq('meta[name="keywords"]')->attr('content');

      initDemoContent($page, $pageHtml);
    }

    $pages[$page->uri] = $page;
  }

  return $pages;
}

function initDemoContent($page, $html) {
  $containers = demoContent($html);
  foreach ($containers as $container => $content) {
    $contentFile = (strpos($container, 'repeater-') === 0) ? $container . '.cnt' : $page->id . '-' . $container . '.cnt';
    if (!file_exists(PUBLIC_DIR . '/' . $contentFile))
      file_put_contents(PUBLIC_DIR . '/' . $contentFile, $content);
    if (!file_exists(DRAFT_DIR . '/' . $contentFile))
      file_put_contents(DRAFT_DIR . '/' . $contentFile, $content);
  }
}

function initSite($request) {
  $templates = templates();
  if (empty($templates))
    throw new Exception('No template could be found in ' . ROOT);

  $home = ROOT . '/home.html';
  if (!is_readable($home))
    throw new Exception('Unable to read the home page ' . $home);

  $pages = initPages($home, $templates);

  saveDraftPages($pages);
  copy(DRAFT_PAGES, PUBLIC_PAGES);

  $idx = 0;
  foreach ($pages as $page) {
    $idx++;
    if ($page->external || ($page->hidden && $idx != 1)) continue;
    $html = publicPage($page, renderNavMenu($pages, $page->uri, $request));
    file_put_contents(PUBLIC_CACHE . '/' . $page->uri . '.cached', $html);
    if ($idx == 1)
      file_put_contents(PUBLIC_CACHE . '/__home.cached', $html);
    copy(ROOT . '/index.php', ROOT . '/' . $page->uri . '.php');
  }

  updateSitemap($request);
}

function metaTemplate($html) {
  phpQuery::newDocument($html);
  
  // remove all SC specific tags
  pq('meta[name="template"]')->remove();
  pq('meta[name="templateName"]')->remove();

  // remove all tags that are rendered anyway
  pq('title')->remove();
  pq('meta[name="description"]')->remove();
  pq('meta[name="keywords"]')->remove();

  $cnt = 0;
  foreach (pq('[class*="sc-content"], [class*="sc-repeater-"]') as $node) {
    $container = pq($node);
    $class = $container->attr('class');
    if (preg_match('/(^|\s)sc\-content($|\s)/', $class, $matches))
      $container->addClass('sc-content-' . $cnt++);
    $container->html('');
  }

  return (string)(phpQuery::getDocument());
}

function metaTemplateInfo($html) {
  $tpl = phpQuery::newDocument($html);

  $containers = array();
  foreach ($tpl['[class*="sc-content-"], [class*="sc-repeater-"]'] as $node) {
    $container = pq($node);
    if (preg_match('/(^|\s)sc-content-([^\s]+)/', $container->attr('class'), $matches))
      $cName = $matches[2];
    else {
      preg_match('/(^|\s)sc-repeater-([^\s]+)/', $container->attr('class'), $matches);
      $cName = 'repeater-' . $matches[2];
    }
    $cnt = new stdClass;
    $cnt->name = $cName;
    $cnt->global = strpos($container->attr('class'), 'sc-repeater-') !== FALSE;
    $cnt->styles = containerStyles($container);
    $containers[] = $cnt;
  }

  $info = new stdClass;
  $info->containers = $containers;

  return $info;
}

function containerStyles($container) {
  $styles = array();

  foreach ($container['> *'] as $cNode) {
    $cnt = pq($cNode);
    $cnt->removeClass('sc-style');
    $class = $cnt->attr('class');
    if (!empty($class)) {
      $tagName = strtolower($cNode->nodeName);
      if (!isset($styles[$tagName]))
	$styles[$tagName] = array();
      $style = $styles[$tagName];
      array_push($styles[$tagName], $class);
    }
  }
  return $styles;
}

function demoContent($html) {
  phpQuery::newDocument($html);

  $containers = array();
  $cnt = 0;
  foreach (pq('[class*="sc-content"], [class*="sc-repeater-"]') as $node) {
    $container = pq($node);
    $class = $container->attr('class');
    if (preg_match('/(^|\s)sc\-content($|\s)/', $class, $matches))
      $cName = $cnt++;
    elseif (preg_match('/(^|\s)sc-content-([^\s]+)/', $class, $matches))
      $cName = $matches[2];
    else {
      preg_match('/(^|\s)sc-repeater-([^\s]+)/', $class, $matches);
      $cName = 'repeater-' . $matches[2];
    }
    
    $containers[$cName] = demoContainerContent($container);
  }

  return $containers;
}

function demoContainerContent($container) {
  $content = '';
  foreach ($container->find('> *:not(.sc-style)') as $node) {
    $content .= pq($node)->htmlOuter();
  }
  return $content;
}