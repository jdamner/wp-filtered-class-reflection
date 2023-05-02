<?php
/**
 * Filtered_Class_Loader
 *
 * @package jdamner\Filtered_Class_Loader
 */

declare ( strict_types = 1 );

namespace WpFilteredClassReflection;

/**
 * Filtered Class Loader.
 *
 * Handles the dynamic loading of a class that's loaded using a filter. Many plugins in WP allow you to
 * filter what class is loaded for a function within that plugin. It's common in WooCommerce to do this to
 * replace things like the data-store or logger classes. This collection of methods allows you to replace the class
 * set to load on the filter, and still provide you with a way of extending whatever class was due to be loaded.
 *
 * Usage:
 * ```php
 * Filtered_Class_Loader::create(
 *     'wp_filter_that_modifies_class', // the filter name.
 *     'DefaultClassToReturn', // the default value of the filter.
 *     'MyNewClass', // the name you intend to use to define your new class
 *     'ExtendableClass' // the name of the class that will be extended by the new class.
 * );
 *
 * // Now we can define our class that extends the original class.
 * class MyNewClass extends ExtendableClass {
 *    public function some_method() {
 *         // Do something before the parent method is called.
 *         parent::some_method();
 *         // Do something after the parent method is called.
 *     }
 * }
 * ```
 */
final class Filtered_Class_Loader {

	/**
	 * Filter name.
	 *
	 * @var string
	 */
	private string $filter_name = '';

	/**
	 * Default value.
	 *
	 * @var string
	 */
	private string $default_class_name = '';

	/**
	 * New Class Name.
	 *
	 * The class name that's going to extend this dynamic class,
	 * and should be avoided when the extendable class is being loaded.
	 *
	 * @var string
	 */
	private string $new_class_name = '';

	/**
	 * Original Class.
	 *
	 * @var string
	 */
	private string $original_class = '';

	/**
	 * Create a new Dynamic Class alias.
	 *
	 * @param string $filter_name The filter name.
	 * @param string $default_class_name The default value.
	 * @param string $new_class_name The new class name.
	 * @param string $extendable_class_name The class name that will be used as the extension point.
	 *
	 * @return bool True if the class was created, false if not.
	 */
	public static function create( string $filter_name, string $default_class_name, string $new_class_name, string $extendable_class_name ): bool {
		$instance = new static( $filter_name, $default_class_name, $new_class_name );
		add_filter( $instance->filter_name, array( $instance, 'get_new_class_name' ), PHP_INT_MAX, 1 );
		return class_alias( $instance->get_original_class_name(), $extendable_class_name );
	}

	/**
	 * Private constructor.
	 *
	 * Prevents being setup incorrectly
	 *
	 * @param string $filter_name The filter name.
	 * @param string $default_class_name The default value.
	 * @param string $dynamic_class_name The dynamic class name.
	 */
	private function __construct( string $filter_name = '', string $default_class_name = '', string $dynamic_class_name = '' ) {
		$this->filter_name        = $filter_name;
		$this->default_class_name = $default_class_name;
		$this->new_class_name     = $dynamic_class_name;
	}

	/**
	 * Get the correct class class.
	 *
	 * @param string $incoming_class The incoming class from the filter.
	 *                               We store this for later use.
	 *
	 * @return string
	 */
	public function get_new_class_name( string $incoming_class ): string {

		if ( $this->new_class_name !== $incoming_class ) {
			$this->original_class = $incoming_class;
		}

		return $this->new_class_name;
	}

	/**
	 * Get the original class name.
	 *
	 * This will get the original class name so we can define an alias to it.
	 *
	 * @return string
	 */
	private function get_original_class_name(): string {
		$class_name = apply_filters( $this->filter_name, $this->default_class_name );
		if ( $this->new_class_name === $class_name ) {
			return $this->original_class ?? $this->default_class_name;
		}

		return $class_name;
	}
}
