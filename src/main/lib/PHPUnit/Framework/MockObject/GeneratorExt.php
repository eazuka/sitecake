<?php

class PHPUnit_Framework_MockObject_GeneratorExt extends PHPUnit_Framework_MockObject_Generator {
	
	/**
	 * 
	 * @param string $className
	 * @param array $methods
	 * @throws InvalidArgumentException
	 * @return object
	 * @throws PHPUnit_Framework_Exception
	 */
	public static function getStaticMock($className) {
		if (!is_string($className)) {
			throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
		}
		
		if ($className != '' && !class_exists($class_name, FALSE)) {
			$mock = self::generate($className);
			return self::getObject($mock['code'], $mock['mockClassName']);		
		}
		return null;
	}

	/**
	* @param  string  $originalClassName
	* @param  array   $methods
	* @return array
	*/
	public static function generate($originalClassName)
	{
		if (isset(self::$cache[$originalClassName])) {
			return self::$cache[$originalClassName];
		}
	
		$mock = self::generateStaticMock(
			$originalClassName
		);

		self::$cache[$originalClassName] = $mock;
	
		return $mock;
	}
		
	/**
	* @param  string     $originalClassName
	* @param  array|null $methods
	* @return array
	*/
	private static function generateStaticMock($originalClassName) {
        $templateDir   = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Generator' .
                         DIRECTORY_SEPARATOR;
        
        $classAddition = new Text_Template(
                           $templateDir . 'mocked_class.tpl'
                         );
        $classAddition = $classAddition->render();
        
		$cloneTemplate = new Text_Template($templateDir . 'mocked_clone.tpl');
		$cloneTemplate = $cloneTemplate->render();
 		$classAddition .= $cloneTemplate;
 		
        $mockClassName = self::generateClassName($originalClassName, '', '');

        $classCode = self::getClassCode($mockClassName['fullClassName']);
        $classMethods = self::getClassMethods($classCode);
		foreach ($classMethods as $classMethod) {
			$classCode = self::mockMethod($mockClassName['originalClassName'], $classMethod, $classCode);
		}
		$classCode = self::augmentClass($classCode, $classAddition);

        return array(
          'code'          => $classCode,
          'mockClassName' => $mockClassName['fullClassName']
        );
	}
	
	/**
	 * @param string $fullClassName
	 * @param string $mockClassName
	 * @return string
	 * @throws PHPUnit_Framework_Exception
	 */
	private static function getClassCode($fullClassName) {
		$origClassPath = str_replace('\\', '/', ltrim($fullClassName, '\\')) . '.php';
		
		$classCode = file_get_contents($origClassPath, true);
		if ( $classCode === FALSE )
			throw new PHPUnit_Framework_Exception('Unable to find definition of class '.
				$fullClassName.' in '.$origClassPath);
		
		$classCode = self::removeComments($classCode);
		$classCode = preg_replace('/^<\?(php)?/', '', $classCode);
		
		return $classCode;
	}

	/**
	 * @param string $code
	 * @param string $addition
	 * @return string
	 */
	private static function augmentClass($code, $addition) {
		return preg_replace('/class\s+[^{]+{/', '$0' . $addition, $code, 1);
	}
	
	/**
	 * @param string $code
	 * @return string
	 */
	private static function extractClassBody($code) {
		$code = self::removeComments($code);
		$matches = array();
		preg_match('/\\s+class\\s+[^{]*({)/', $code, $matches, PREG_OFFSET_CAPTURE);
		$start = $matches[1][1];
		return self::getBalancedCurlyBracketsContent($code, $start);
	}
	
	/**
	 * @param string $text
	 * @param int $start the offset of the starting curly bracket within the given text
	 * @return string
	 */
	private static function getBalancedCurlyBracketsContent($text, $start) {
		$characters = str_split(substr($text, $start));
		$balance = 0;
		foreach ($characters as $idx => $chr) {
			if ($chr == '{')
				$balance++;
			else if ($chr == '}')
				$balance--;
			if ($balance == 0) {
				$end = $idx;
				break;
			}
		}
		return substr($text, $start+1, $end - 2);
	}
	
	/**
	 * @param string $str
	 * @return string
	 */
	private static function removeComments($str) {
		$commentTokens = array(T_COMMENT, T_DOC_COMMENT);
		$out = '';
		$tokens = token_get_all($str);
		foreach ($tokens as $token) {
			if (is_array($token)) {
				if (in_array($token[0], $commentTokens))
					continue;
				$token = $token[1];
			}
			$out .= $token;
		}
		return $out;		
	}
	
	/**
	 * @param string $methodName
	 * @param string $methods
	 * @return string
	 * @throws PHPUnit_Framework_Exception
	 */
	private function mockMethod($className, $methodName, $methods) {
		$methodBody = '$__phpunit_result = self::__phpunit_getStaticInvocationMocker()->invoke('.
			'new \PHPUnit_Framework_MockObject_Invocation_Static('.
		    '\''.$className.'\', \''.$methodName.'\', func_get_args()));'.
			'if ($__phpunit_result[\'hasReturnValue\']) return $__phpunit_result[\'returnValue\'];';
		
		$result = preg_replace('/\s+function.*(\s|&)'.$methodName.'[^{]*{/', '$0'.$methodBody, $methods);
		//if ( $result === FALSE )
		//	throw new PHPUnit_Framework_Exception(
		//		'Mock target '.$className.'::'.$methodName.' cannot be found.');
		return $result;		
	}
	
	private static function getClassMethods($code) {
		$declarations = array();
		preg_match_all('/function\s+&?([a-zA-Z0-9_]+)[^{]*/', $code, $declarations);
		return $declarations[1];		
	}
	
	/**
	* @param  string   $className
	* @return string
	*/
	protected static function generateMockClassDeclaration($className)
	{
		return 'class ' . $className .' implements \\PHPUnit_Framework_MockObject_MockObject';
	}	
}