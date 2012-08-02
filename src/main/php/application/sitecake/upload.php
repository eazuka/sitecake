<?php
namespace sitecake;

use Zend\Http\Request as Request;
use WideImage\img as img;

class upload {
	static $forbidden = array('php', 'php5', 'php4', 'php3', 'phtml', 'phpt');
	
	static function upload(Request $req) {
		if(!isset($_SERVER['HTTP_X_UPLOADOBJECT_FILENAME'])) {
			return array('status' => -1,
							'errorMessage' => 'Invalid file upload request');
		}
		
		$fileName = $_SERVER['HTTP_X_UPLOADOBJECT_FILENAME'];
		$comps = explode(".", $fileName);
		$fileExt = $comps[count($comps)-1];

		if (in_array(strtolower($fileExt), self::$forbidden) ) {
			return array('status' => -1, 
				'errorMessage' => 'Not allowed file type');
		}
		
		if ($fileExt) {
			$imageUpload = isset($_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH']) || 
				isset($_SERVER['HTTP_X_IMAGEUPLOAD_THUMBDIM']);
		}
		
		$id = util::id();
		$file = $GLOBALS['DRAFT_CONTENT_DIR'] . DS . $id . 
			($fileExt ? '.' . $fileExt : '');
			
		io::file_put_contents($file, io::file_get_contents("php://input"));
		
		$result = array('status' => 0);
		$result['id'] = $id;
		
		if ($imageUpload) {
			$result['url'] = $GLOBALS['DRAFT_CONTENT_URL'] . '/' . $id . 
				($fileExt ? '.' . $fileExt : '');
				
			if (isset($_SERVER['HTTP_X_IMAGEUPLOAD_THUMBDIM'])) {
				$thumbnailDimension = $_SERVER['HTTP_X_IMAGEUPLOAD_THUMBDIM'];
				$thumbId = util::id();
				$thumbFile = $GLOBALS['DRAFT_CONTENT_DIR'] . DS . 
					$thumbId .	($fileExt ? '.' . $fileExt : '');
				img::load($file);
				img::resizeToDimension($thumbnailDimension);
				img::save($thumbFile);
				$result['thumbnailUrl'] = $GLOBALS['DRAFT_CONTENT_URL'] . '/' . 
					$thumbId .	($fileExt ? '.' . $fileExt : '');
				$result['thumbnailWidth'] = img::getWidth();
				$result['thumbnailHeight'] = img::getHeight();
				img::unload();
				meta::put($thumbId, array(
					'orig' => util::rpath($file),
					'path' => util::rpath($thumbFile),
					'name' => basename($thumbFile)
				));
			}
		
			if (isset($_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH']) && 
					$_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH'] != 0 ) {
				$resizedWidth = $_SERVER['HTTP_X_IMAGEUPLOAD_RESIZEWIDTH'];
				$resizedId = util::id();
				$resizedFile = $GLOBALS['DRAFT_CONTENT_DIR'] . DS .
					$resizedId .	($fileExt ? '.' . $fileExt : '');
				img::load($file);
				if (img::getWidth() <= $resizedWidth) {
					io::copy($file, $resizedFile);
				} else {
					img::resizeToWidth($resizedWidth);
					img::save($resizedFile);
				}
				$result['resizedUrl'] = $GLOBALS['DRAFT_CONTENT_URL'] . '/' . 
					$resizedId .	($fileExt ? '.' . $fileExt : '');
				$result['resizedWidth'] = img::getWidth();
				$result['resizedHeight'] = img::getHeight();
				img::unload();
				meta::put($resizedId, array(
					'orig' => util::rpath($file),
					'path' => util::rpath($resizedFile),
					'name' => basename($resizedFile)
				));				
			}
		} else {
			$result['url'] = $GLOBALS['DRAFT_CONTENT_URL'] . '/' . 
				$id .	($fileExt ? '.' . $fileExt : '');
		}
		return $result;
	}
}