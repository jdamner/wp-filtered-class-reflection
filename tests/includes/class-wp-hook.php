<?php
/**
 * A stubby WP_Hook class that can be used for testing.
 */
class WP_Hook {

	private array $callbacks = array();

	public function add_filter( $filter_name, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->callbacks[ $filter_name ] = $callback;
	}

	public function apply_filters( $filter_name, $default_class_name ) {
		$callback = $this->callbacks[ $filter_name ];
		return call_user_func_array( $callback, array( $default_class_name ) );
	}
}
