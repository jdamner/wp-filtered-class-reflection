<?php

/**
 * Load Deps
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/class-default-class.php';
require_once __DIR__ . '/includes/class-wp-hook.php';
require_once __DIR__ . '/../src/class-filtered-class-loader.php';

/**
 * Load WP_Hook stub implementation.
 */
global $wp_hook;
$wp_hook = new WP_Hook();

/**
 * Define functions that are used in the tests.
 */
function add_filter( $filter_name, $callback, $priority = 10, $accepted_args = 1 ) {
	global $wp_hook;
	$wp_hook->add_filter( $filter_name, $callback, $priority, $accepted_args );
}

function apply_filters( $filter_name, $default_class_name ) {
	global $wp_hook;
	return $wp_hook->apply_filters( $filter_name, $default_class_name );
}


