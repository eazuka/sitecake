<?php
namespace sitecake;

use \phpQuery\phpQuery as phpQuery;
use \WideImage\img as img;
use \Zend\Json\Json as json;

class content {
	/**
	* Saves the given page content into the respective content containers.
	*
	* The response is an array with the following elements:
	* <code>status</code> - int, possible outcomes:
	* -1 - call failed because of an (execution) error
	*  0 - the page content saved
	*
	* <code>errorMessage</code> - string, present if <code>status</code> 
	* 	is -1 or 1
	*
	* @param array $params the page content in the following format:
	* 	scpageid - the sc page id
	* 	sc-content-<name>(sc-repeater-<name>) - the content of the container 
	* 		(or repeater) <name>
	*
	* @return array the service response
	*/
	static function save($params) {
		$id = $params['scpageid'];
		$draft = draft::get($id);
		foreach ($params as $container => $data) {
			if ($container == 'scpageid') continue;
			// remove slashes
			if (get_magic_quotes_gpc())
				$data = stripcslashes($data);	
			$data = base64_decode($data);
			if (!empty($data)) {
				$data = content::process_save(
					array_key_exists($container, $draft) ? 
					$draft[$container] : '', $data);
			}
			$draft[$container] = $data;		
		}
		draft::update($id, $draft);
		return array('status' => 0);
	}
	
	/**
	 * Publish the site content.
	 *
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the site published
	 *
	 * <code>errorMessage</code> - string, present if <code>status</code> 
	 * 	is -1 or 1
	 *
	 * @param array $params the page content in the following format:
	 * scpageid - the page name
	 *
	 * @return array the service response
	 */
	static function publish($params) {
		$id = $params['scpageid'];
		$pageFiles = renderer::pageFiles();
		$draft = draft::get($id);
		foreach ($pageFiles as $pageFile) {
			$html = io::file_get_contents($pageFile);
			if (preg_match('/\\s+scpageid="'.$id.'";/', $html)) {
				$tpl = phpQuery::newDocument($html);
				renderer::normalizeContainerNames($tpl);
				renderer::injectDraftContent($tpl, $draft);
				renderer::cleanupContainerNames($tpl);
				renderer::savePageFile($pageFile, (string)$tpl);
				$repeaters = content::repeaters($draft);
				if (!empty($repeaters)) {
					content::pass_repeaters($pageFiles, $pageFile, $repeaters);
				}				
				draft::delete($id);
				break;
			}
		}
		draft::delete($id);
		return array('status' => 0);
	}
	
	static function process_save($old, $new) {
		$oldDoc = phpQuery::newDocumentXHTML($old);
		$newDoc = phpQuery::newDocumentXHTML($new);
		foreach (phpQuery::pq('img', $newDoc) as $imgNode) {
			$img = phpQuery::pq($imgNode, $newDoc);
			$url = $img->attr('src');
			$data = $img->attr('data');
			$oldImg = phpQuery::pq("img[src='$url']", $oldDoc);
			if (!$oldImg || $oldImg->attr('data') != $data) {
				$img->attr('src', content::process_image($url, $data));
			}
		}
		return (string)$newDoc;
	}
	
	static function process_image($url, $data) {
		$info = content::image_info($url);
		
		$path = $info['path'];
		if (!io::file_exists($path))
			return $url;
		
		$id = $info['id'];
		if (meta::exists($id)) {
			// if the image already a draft image
			// just replace it
			$spath = util::apath(meta::get($id, 'orig'));
			content::transform_image($spath, $path, $data);
			return $url;
		} else {
			// otherwise, if the image is a template image, transform the image
			// and save it as a new draft
			$id = util::id();
			$name = $id . '.' . $info['ext'];
			$dpath = $GLOBALS['DRAFT_CONTENT_DIR'] . DS . $name;
			content::transform_image($path, $dpath, $data);
			meta::put($id, array('orig' => util::rpath($path)));
			return $GLOBALS['DRAFT_CONTENT_URL'] . '/' . $name;
		}
	}
	
	static function image_info($url) {
		return array(
			'id' => reset(explode('.', end(explode('/', $url)))),
			'ext' => end(explode('.', end(explode('/', $url)))),
			'path' => $GLOBALS['SC_ROOT'] . DS . $url,
			'name' => basename($GLOBALS['SC_ROOT'] . DS . $url)
		);
	}
	
	static function transform_image($spath, $dpath, $data) {
		$datas = explode(':', $data);
		$srcWidth = $datas[0];
		$srcHeight = $datas[1];
		$srcX = $datas[2];
		$srcY = $datas[3];
		$dstWidth = $datas[4];
		$dstHeight = $datas[5];
		
		img::load($spath);
			
		$origWidth = img::getWidth();
		$origHeight = img::getHeight();
		
		$xRatio = $origWidth / $srcWidth;
		$yRatio = $origHeight / $srcHeight;
		
		$srcWidth = $dstWidth * $xRatio;
		$srcHeight= $dstHeight * $yRatio;
		$srcX = $srcX * $xRatio;
		$srcY = $srcY * $yRatio;
		
		img::transform($srcX, $srcY, $srcWidth, $srcHeight, 
			$dstWidth, $dstHeight);
		img::save($dpath);
		img::unload();
	}
	
	static function repeaters($containers) {
		$repeaters = array();
		foreach ($containers as $key => $val) {
			if (preg_match('/^sc\-content\-_rep_.+$/', $key))
				$repeaters[$key] = $val;	
		}
		return $repeaters;
	}
	
	static function pass_repeaters($pageFiles, $currPageFile, $repeaters) {
		foreach ($pageFiles as $pageFile) {
			if ($pageFile == $currPageFile) continue;
			$html = io::file_get_contents($pageFile);
			if (preg_match('/sc\-repeater\-/', $html)) {
				$tpl = phpQuery::newDocument($html);
				renderer::normalizeContainerNames($tpl);
				renderer::injectDraftContent($tpl, $repeaters);
				renderer::cleanupContainerNames($tpl);
				renderer::savePageFile($pageFile, (string)$tpl);
			}			
		}
	}
	
}