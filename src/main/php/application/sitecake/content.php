<?php
namespace sitecake;

use \phpQuery\phpQuery as phpQuery;

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
				content::process_save($draft[$container], $data);
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
				break;
			}
		}
		draft::delete($id);
		return array('status' => 0);
	}
	
	static function process_save($old, $new) {
		// find all img tages and their data attributes
		// if the data is changed, transform the image
	}
}