<?php
namespace sitecake;

use Zend\Http\PhpEnvironment\Request,
	Zend\Http\PhpEnvironment\Response,
	Zend\Json\Json,
	phpQuery\phpQuery;

use \Exception as Exception;

class renderer {
	static function process() {
		try {
			http::send(renderer::response(http::request()));
		} catch (Exception $e) {
			http::send(http::errorResponse('<h2>Exception: </h2><b>' . 
				$e->getMessage() . "</b><br/>" .
				$e->getFile() . '(' . $e->getLine() . '): <br/>' . 
				implode("<br/>", explode("\n", $e->getTraceAsString()))));
		}
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param Request $req
	 */
	static function response($req) {
		$pageFiles = renderer::pageFiles();		
		$pageUri = renderer::pageUri($req->query());
		if (array_key_exists($pageUri, $pageFiles)) {
			return renderer::assemble(
				$pageFiles[$pageUri], 
				!renderer::isLoggedin());
		} else {
			return http::notFoundResponse($req->getBasePath() . '/' . $pageUri);
		}
	}
		
	static function isLoggedin() {
		if ( isset($_COOKIE['PHPSESSID']) ) { 
			session_start();
			return (isset($_SESSION['loggedin']) && 
				$_SESSION['loggedin'] === true);
		}
		else {
			return false;
		}
	}
	
	static function pageUri($params) {
		return isset($params['page']) ? $params['page'] : 'index.html';
	}
	
	static function pages() {
		$pages = array();
		$pageFiles = renderer::pageFiles();
		
		$homeDoc = phpQuery::newDocument($pageFiles['index.html']);
		
		$homePage = Page::create();
		$homePage->navMenuIndex = -1;
		$homePage->external = false;
		$homePage->uri = 'index.html';
		$page->title = phpQuery::pq('title', $homeDoc)->text();
		$page->description = phpQuery::pq(
			'meta[name="description"]', $homeDoc)->attr('content');
		$page->keywords = phpQuery::pq(
			'meta[name="keywords"]', $homeDoc)->attr('content');
		array_push($pages, $homePage);
		
		$navMenuIndex = 0;
		foreach (phpQuery::pq('ul.sc-nav:first li a', $homeDoc) as $navNode) {
			$node = phpQuery::pq($navNode, $homeDoc);
			$href = $node->attr('href');
			if ($href == 'index.html') {
				$page = $homePage;
			} else {
				$page = Page::create();
				array_push($pages, $page);
			} 
			$page->external = renderer::isExternalLink($href);
			$page->uri = $page->external ? $href : basename($href);
			$page->navMenuIndex = $navMenuIndex++;
			$page->navDisplay = $node->text();
			$page->navTitle = $node->attr('title') ?: $page->navDisplay;
		
			if (!$page->external && ($page != $homePage)) {
				$pageHtml = renderer::loadPageFile($page->uri);
				$pageDoc = phpQuery::newDocument($pageHtml);
				$page->title = phpQuery::pq('title', $pageDoc)->text();
				$page->description = phpQuery::pq(
					'meta[name="description"]', $pageDoc)->attr('content');
				$page->keywords = phpQuery::pq(
					'meta[name="keywords"]', $pageDoc)->attr('content');
			}
			$page->title = $page->title ?: $page->navDisplay;
		}

		return $pages;	
	}
	
	static function isExternalLink($url) {
		return strpos($url, '/') || strpos($url, 'http://') || 
			(substr($url, -5) != '.html');
	}
	
	static function pageFiles() {
		$path = $GLOBALS['SC_ROOT'];
		
		if (!is_readable($path)) {
			throw new Exception(
				resources::message('PAGE_DIR_NOT_READABLE', $path));
		}
		
		$htmlFiles = glob($path . DS . '*.html');
	
		if ($htmlFiles === false || empty($htmlFiles)) {
			throw new Exception(
				resources::message('NO_PAGE_EXISTS', $path));
		}
		
		$pageFiles = array();
		foreach ( $htmlFiles as $htmlFile ) {
			$pageFiles[basename($htmlFile)] = $htmlFile;
		}
		
		if (!array_key_exists('index.html', $pageFiles)) {
			throw new Exception(
				resources::message('INDEX_PAGE_NOT_EXISTS', $path));
		}
				
		return $pageFiles;
	}
	
	static function loadPageFile($path) {
		if (!is_readable($path))
			throw new Exception('PAGE_NOT_EXISTS', $path);
		return file_get_contents($path);
	}
	
	static function savePageFile($path, $content) {
		file_put_contents($path, $content);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $pageFile
	 * @param boolean $isLogin
	 * @return Response
	 */
	static function assemble($pageFile, $isLogin) {
		$tpl = phpQuery::newDocument(renderer::loadPageFile($pageFile));
		renderer::adjustNavMenu($tpl);
		renderer::injectClientCode($tpl, $isLogin);
		renderer::normalizeContainerNames($tpl);
		//if (!$isLogin) {
			renderer::injectDraftContent($tpl, $pageFile);
		//}
		return http::response($tpl);
	}
	
	static function sitemap($pages, $reqest) {
		$result = '<?xml version="1.0" encoding="UTF-8"?>' .
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		foreach ($pages as $page) {
			if ($page == $pages[array_shift(array_keys($pages))])
			$result .= '<url><loc>' . 'http://' . $request->host . 
				$request->basePath . '</loc>' .
				'<changefreq>monthly</changefreq></url>';
			elseif (!$page->hidden && !$page->external)
				$result .= '<url><loc>' . 'http://' . $request->host . 
					$request->basePath . $page->uri . '</loc>' .
					'<changefreq>monthly</changefreq></url>';
		}
		return $result . '</urlset>';		
	}
	
	static function normalizeContainerNames($tpl) {
		$cnt = 0;
		foreach ( phpQuery::pq('[class*="sc-content"], [class*="sc-repeater-"]', 
				$tpl) as $node) {
			$container = phpQuery::pq($node, $tpl);
			$class = $container->attr('class');
			if (preg_match('/(^|\s)sc\-content($|\s)/', $class, $matches))
			$container->addClass('sc-content-' . $cnt++);
		}
		return $tpl;		
	}
	
	static function containers($tpl) {
		$containers = array();
		foreach (phpQuery::pq('[class*="sc-content-"], [class*="sc-repeater-"]',
				$tpl) as $node) {
			$cNode = phpQuery::pq($node, $tpl);
			if (preg_match('/(^|\s)(sc-content-[^\s]+)/', $cNode->attr('class'),
					$matches)) {
				$containers[$matches[2]] = false;
			}
			else {
				preg_match('/(^|\s)sc-repeater-([^\s]+)/', 
					$cNode->attr('class'), $matches);
				$containers[$matches[2]] = true;
			}
		}
		return $containers;			
	}
	
	static function adjustNavMenu($tpl) {
		foreach (phpQuery::pq('ul.sc-nav li a', $tpl) as $navNode) {
			$node = phpQuery::pq($navNode, $tpl);
			$href = $node->attr('href');
			if (!renderer::isExternalLink($href)) {
				$node->attr('href', 'sc-admin.php?page=' . $href);
			}
		}
		return $tpl;
	}
	
	static function injectClientCode($tpl, $isLogin) {
		phpQuery::pq('head', $tpl)->append(renderer::clientCode($isLogin));	
		return $tpl;
	}
	
	static function clientCode($isLogin) {
		return $isLogin ? 
			renderer::clientCodeLogin() : renderer::clientCodeEdit();
	}
	
	static function clientCodeLogin() {
		return '<!-- sitecake login -->';
	}
	
	static function clientCodeEdit() {
		return '<!-- sitecake edit -->';
	}
	
	static function injectDraftContent($tpl, $pageFile) {
		$containers = renderer::containers($tpl);
		$content = renderer::draftContent($tpl, $pageFile, $containers);
		foreach ($containers as $container) {
			if (isset($content[$container])) {
				renderer::setContent($tpl, $container, $content[$container]);
			}
		}
		return $tpl;
	}
	
	static function draftContent($tpl, $pageFile, $containers) {
		$id = renderer::pageId($tpl, $pageFile);
		return renderer::loadDraftContent($id);
	}
	
	static function id() {
		return sha1(uniqid('', true));
	}
	
	static function pageId($tpl, $pageFile) {
		if (preg_match('/\\s+scpageid=([^;]+);/', 
				(string)(phpQuery::pq('head', $tpl)->html()), $matches)) {
			return $matches[1];
		} else {
			$id = renderer::id();
			$origTpl = phpQuery::newDocument(renderer::loadPageFile($pageFile));
			phpQuery::pq('head', $origTpl)->append(
				'<!-- scpageid=' . $id . '; -->');
			phpQuery::pq('head', $tpl)->append(
				'<!-- scpageid=' . $id . '; -->');
			renderer::savePageFile($pageFile, (string)$origTpl);
			return $id;
		}
	}
	
	static function loadDraftContent($pageId) {
		$draft = array();
		$path = $GLOBALS['DRAFT_CONTENT_DIR'] . DS . $pageId . '.json';
		if (is_readable($path)) {
			$draft = Json::decode($path, Json::TYPE_ARRAY);
		}
		return $draft;
	}
	
	static function setContent($tpl, $container, $content) {
		phpQuery::pq('.' . $container, $tpl)->html($content);
	}
}