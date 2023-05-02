<?php
/**
 * Test the Filtered_Class_Loader class.
 *
 * @package Jdamner\WpFilteredClassReflection
 */

declare ( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WpFilteredClassReflection\Filtered_Class_Loader;

/**
 * Test the Filtered_Class_Loader class.
 */
class Test_Filtered_Class_Loader extends TestCase {

	/**
	 * Test the correct classes are returned by each method.
	 */
	public function test_get_class() {

		add_filter(
			'test_filter',
			static function (): string {
				return Test_Filtered_Class_Loader::class; // has to return an actual class name for the alias to work.
			}
		);

		$this->assertEquals( self::class, apply_filters( 'test_filter', 'Default_Class' ) );

		Filtered_Class_Loader::create( 'test_filter', 'Default_Class', 'My_Class', 'DynamicExtendableClass' );

		// Check that the filter does return our new class.
		$this->assertEquals( 'My_Class', apply_filters( 'test_filter', 'DefaultClass' ) );

		// Confirm the class has been created.
		$this->assertTrue( class_exists( 'DynamicExtendableClass' ) );

		// Confirm the class created has the methods from the default class and the new class.
		$this->assertTrue( method_exists( 'My_Class', 'method_one' ) );
		$this->assertTrue( method_exists( 'My_Class', 'method_two' ) );
	}
}
