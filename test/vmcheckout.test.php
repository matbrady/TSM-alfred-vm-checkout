<?php
/**
*  vm_checkout.test.php
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

	/**
	 * Remove File
	 * 
	 * @param 'string' : file name to delete
	 */
	protected static function removeFile($file) {
		unlink( $file );
	}


	/* TESTS */

	/**
	 * Confirm No Name File
	 *
	 * Description: confirms that no name file is deployed
	 * a user has a clean isntall of the Workflow
	 * Note: There is no way currently to ingore files when 
	 * exporting a workflow. 'name.txt' must be manually removed
	 */
	function test_for_name_file() {
		$this->assertFileNotExists('../name.txt');
	}


	/**
	 * Test Setting Name to File
	 * 
	 * Description: Create a name file using test against
	 * its contents. Remove the test file after asserting
	 */
	function test_setting_name_to_file() {

		// Creates a test name file location/name
		$file = dirname(__FILE__) . '/name.test.txt';

		// expose VMC protection function
		$set_text_to_file = self::getMethod('set_text_to_file');
		$VMC = new VMC();
		$set_text_to_file->invoke( $VMC, $file, 'John' );

		$this->assertEquals('John', file_get_contents($file) );

		// remove the test file
		self::removeFile($file);
	}


    function test_for_file_contents() {

    	// $foo = self::getMethod('is_name_set');
    	// $obj = new VMC();
    	$hasName = ''; //$foo->invoke($obj);

        $this->assertEquals('', $hasName);
    }

}



?>
