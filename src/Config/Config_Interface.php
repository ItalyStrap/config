<?php
/**
 * Config Class that handle the classes configuration
 *
 * @version 0.0.1-alpha
 *
 * @package ItalyStrap\Config
 */

namespace ItalyStrap\Config;

interface Config_Interface {

	/**
	 * Retrieves all of the runtime configuration parameters
	 *
	 * @return array
	 */
	public function all() : array ;

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string  $parameter_key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get( string $parameter_key, $default = null );

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param  string  $parameter_key
	 * @return bool
	 */
	public function has( string $parameter_key ) : bool ;

	/**
	 * Push a configuration in via the key
	 *
	 * @param string $parameter_key Key to be assigned, which also becomes the property
	 * @param mixed $value Value to be assigned to the parameter key
	 * @return null
	 */
	public function push( string $parameter_key, $value );

	/**
	 * Removes an item or multiple items.
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed ...$with_keys
	 * @return self
	 */
	public function remove( ...$with_keys );

	/**
	 * Merge a new array into this config
	 *
	 * @since 1.1.0
	 *
	 * @param array ...$array_to_merge
	 * @return null
	 */
	public function merge( array ...$array_to_merge );
}
