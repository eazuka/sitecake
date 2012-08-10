<?php
namespace sitecake;

use phpQuery\phpQuery as phpQuery;

class pages {
	static function all() {
		$pages = array();
		$pageFiles = renderer::pageFiles();
	
		$homeHtml = renderer::loadPageFile($pageFiles['index.html']);
		$homeDoc = phpQuery::newDocument($homeHtml);
		$nav = pages::nav_items($homeDoc);

		foreach ($pageFiles as $uri => $path) {
			$home = ($uri == 'index.html');
			$html = $home ? $homeHtml : renderer::loadPageFile($path);
			$pageDoc = $home ? $homeDoc : phpQuery::newDocument($html);
			$navidx = array_search($uri, $nav);
			array_push($pages, array(
				'id' => renderer::pageId($pageDoc, $path),
				'idx' => ($navidx === FALSE) ? -1 : $navidx,
				'title' => phpQuery::pq('title', $pageDoc)->text(),
				'home' => $home,
				'uri' => $uri
			));			
		}
		return $pages;
	}
	
	static function update($pages) {
		$navPages = pages::extract_nav_pages($pages);
		
		foreach (renderer::pageFiles() as $uri => $path) {
			$html = renderer::loadPageFile($path);
			$doc = phpQuery::newDocument($html);
			$id = renderer::pageId($doc, $path);
			$page = pages::extract_page($id, $pages);
			if ($page) {
				pages::update_page($doc, $page, pages::gen_nav($navPages, $uri),
					$path);
			}
			pages::create_pages(pages::extract_new_pages($pages, $id), 
				$doc, $navPages);
			if (!$page) {
				pages::delete_page($id, $path);
			}
		}
		
		pages::sitemap($pages);
	}
	
	static function nav_items($doc) {
		$items = array();
		foreach (phpQuery::pq('ul.sc-nav:first li a', $doc) as $navNode) {
			$node = phpQuery::pq($navNode, $doc);
			array_push($items, $node->attr('href'));
		}
		return $items;
	}
	
	static function extract_nav_pages($pages) {
		$navItems = array_filter($pages, function($page) {
			return ($page['idx'] >= 0);
		});
		usort($navItems, function($a, $b) {
			return ($a['idx'] - $b['idx']);
		});
		return $navItems;		
	}
	
	static function gen_nav($pages, $uri) {		
		$html = '';
		foreach ($pages as $page) {
			$html .= '<li' . ($page['uri'] == $uri ? ' class="active">' : '>') .
				'<a href="' . $page['uri'] . '">' . $page['title'] . 
				'</a></li>';
		}
		return $html;
	}
	
	static function extract_new_pages($pages, $tid) {
		return array_filter($pages, function($page) use ($tid) {
			return !isset($page['id']) && $page['tid'] == $tid;
		});
	}
	
	static function extract_page($id, $pages) {
		$page = null;
		foreach ($pages as $p) {
			if (isset($p['id']) && $p['id'] == $id) {
				$page = $p;
				break;
			}
		}
		return $page;
	}
	
	static function delete_page($id, $path) {
		if (meta::exists($id)) {
			meta::remove($id);
		}
		io::unlink($path);
	}
	
	static function update_page($doc, $page, $nav, $uri, $path) {
		phpQuery::pq('ul.sc-nav', $doc)->html($nav);
		phpQuery::pq('title', $doc)->html($page['title']);
		renderer::savePageFile($path, (string)$doc);
		if ($uri != $page['uri']) {
			io::rename($path, $GLOBALS['SC_ROOT'] . DS . $uri);
		}
	}
	
	static function create_pages($pages, $doc, $navItems) {
		foreach ($pages as $page) {
			pages::create_page($page, $doc, $navItems);
		}
	}
	
	static function create_page($page, $doc, $navItems) {
		$id = util::id();
		$path = $GLOBALS['SC_ROOT'] . DS . $page['uri'];
		$nav = pages::gen_nav($navItems, $page['uri']);
		phpQuery::pq('ul.sc-nav', $doc)->html($nav);
		phpQuery::pq('title', $doc)->html($page['title']);
		io::file_put_contents($path, 
			preg_replace('/scpageid="[^"]+"/', 'scpageid="' . $id . '"', 
				$doc->_toString()));
	}
	
	static function sitemap($pages) {
		io::file_put_contents($GLOBALS['SITE_MAP_FILE'], 
			pages::gen_sitemap($pages));
	}

	static function gen_sitemap($pages) {
		$base = http::$req->uri()->getScheme() . 
			'://' . http::$req->uri()->getHost() . '/' . 
			http::$req->getBasePath() . '/';
		$result = '<?xml version="1.0" encoding="UTF-8"?>' .
			'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		foreach ($pages as $page) {
			if ($page['idx'] >= 0 || $page['uri'] == 'index.html') {
				$result .= '<url><loc>' . $base . 
					($page['uri'] == 'index.html' ? '' : $page['uri']) . 
					'</loc><changefreq>monthly</changefreq></url>';
			}
		}
		return $result . '</urlset>';		
	}
}