<?php

class PHPUnit_Framework_TestCaseExt extends PHPUnit_Framework_TestCase {
	public function getMockStaticClass($className, $methods = array())
    {
    	$mockObject = PHPUnit_Framework_MockObject_GeneratorExt::getStaticMock(
    		$className);
    	
    	if ( $mockObject != null )
    		$this->mockObjects[] = $mockObject;
   
    	return $className;    	
    }
    
    public static function mockStaticClass($className) {
    	PHPUnit_Framework_MockObject_GeneratorExt::getStaticMock($className);
    }
	
}