<?php
namespace sitecake;

class session {

	/**
	* Starts the admin session if the given credential is valid.
	* This is the only service that does not requre an authorization check.
	*
	* The response is an array with the following elements:
	* <code>status</code> - int, possible outcomes:
	* -1 - call failed because of an (execution) error
	*  0 - login granted, the session is started
	*  1 - login failed because of invalid credential
	*  2 - login failed because the admin session has already begun (locked)
	*  3 - login failed because of some other reason (the reason decription will
	*  		be present in the errorMessage)
	*
	* <code>errorMessage</code> - string, present if <code>status</code> 
	* 	is -1 or 3
	*
	* @param string $credential the authentication credential, SHA1 hex hash of
	* 	the admin password
	* @return array the service response
	*/
	static function login($credential) {
		return array('status' => -1, 'errorMessage' => 'not implemented');
	}
	
	/**
	 * Requests the authorization credential to be changed/replaced by the new
	 * one.
	 *
	 * The response is an array with the following elements:
	 * <code>status</code> - int, possible outcomes:
	 * -1 - call failed because of an (execution) error
	 *  0 - the new credential accepted
	 *  1 - the request failed because of invalid credential
	 *  2 - the new credential is not acceptable
	 *
	 * <code>errorMessage</code> - string, present if <code>status</code> is -1
	 *
	 * @param string $credential the currently valid credential
	 * @param string $newCredential the new credential
	 * @return array the service response
	 */
	static function change($credential, $newCredential) {
		return array('status' => -1, 'errorMessage' => 'not implemented');
	}
	
	/**
	 * Ends the admin session.
	 *
	 * The response is an array with the following elements:
	 * <code>status</code> - int - 0 if OK, -1 the service call failed
	 * <code>errorMessage</code> - string, present if <code>status</code> 
	 * 	is not 0
	 *
	 * @return array the service response
	 */
	static function logout() {
		return array('status' => -1, 'errorMessage' => 'not implemented');
	}
	
	/**
	 * Refreshes the admin session by resetting the session timeout timer.
	 *
	 * The response is an array with the following elements:
	 * <code>status</code> - int - 0 if OK, -1 the service call failed
	 * <code>errorMessage</code> - string, present if <code>status</code> 
	 * 	is not 0
	 *
	 * @return array the service response
	 */
	static function alive() {
		return array('status' => -1, 'errorMessage' => 'not implemented');
	}
	
}