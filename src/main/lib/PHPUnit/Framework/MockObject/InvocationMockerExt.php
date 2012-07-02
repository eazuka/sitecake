<?php

class PHPUnit_Framework_MockObject_InvocationMockerExt extends PHPUnit_Framework_MockObject_InvocationMocker {
	/**
	* @param  PHPUnit_Framework_MockObject_Invocation $invocation
	* @return mixed
	*/
	public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
	{
		$exception      = NULL;
		$hasReturnValue = FALSE;
	
		if (strtolower($invocation->methodName) == '__tostring') {
			$returnValue = '';
		} else {
			$returnValue = NULL;
		}
	
		foreach ($this->matchers as $match) {
			try {
				if ($match->matches($invocation)) {
					$value = $match->invoked($invocation);
	
					if (!$hasReturnValue) {
						$returnValue    = $value;
						$hasReturnValue = TRUE;
					}
				}
			}
	
			catch (Exception $e) {
				$exception = $e;
			}
		}
	
		if ($exception !== NULL) {
			throw $exception;
		}
	
		return array('hasReturnValue' => $hasReturnValue, 'returnValue' => $returnValue);
	}	
}