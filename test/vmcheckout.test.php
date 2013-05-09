<?php
/**
*  vm_checkout_test.php
*/

require_once dirname(__FILE__) . '/../vmcheckout.php';

class VmcTest extends PHPUnit_Framework_TestCase { 

	/**
	 * Get Protected Method From Class
	 *
	 * Description: A Classess protected method
	 * is return and can be invoked for testing
	 * @param Method
	 */
	protected static function getMethod($name) {

		$class = new ReflectionClass('VMC');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	// Not a good test becuase I don't push the name.txt file to the repo
    function file_has_contents() {

    	$foo = self::getMethod('is_name_set');
    	$obj = new VMC();
    	$hasName = $foo->invoke($obj);

        $this->assertEquals('mat', $hasName);
    }

    function testing() {
    	$this->assertEquals(0,0);
    }
}



?>
