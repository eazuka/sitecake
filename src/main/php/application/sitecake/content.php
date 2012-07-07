<?php
namespace sitecake;

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
	* __sc_page - the page name
	* __sc_content_<container name> - the content of the container 
	* 	(<container name>)
	*
	* @return array the service response
	*/
	static function save($params) {
		return array('status' => -1, 'errorMessage' => 'not implemented');
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
	 * __sc_page - the page name
	 *
	 * @return array the service response
	 */
	static function publish($params) {
		return array('status' => -1, 'errorMessage' => 'not implemented');
	}
}