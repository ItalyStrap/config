<?php
/**
 * Config Class that handle the classes configuration
 *
 * @package ItalyStrap\Config
 */
declare(strict_types=1);

namespace ItalyStrap\Config;

interface ConfigInterface extends Config_Interface, \ArrayAccess, \IteratorAggregate, \Countable {

	/**
	 * Retrieves all of the runtime configuration parameters
	 *
	 * @return array
	 */
	public function all(): array;

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string  $parameter_key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get( $parameter_key, $default = null );

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param  string  $parameter_key
	 * @return bool
	 */
	public function has( $parameter_key ): bool;

	/**
	 * Add a configuration in via the key
	 *
	 * @param string|int $parameter_key Key to be assigned, which also becomes the property
	 * @param mixed $value Value to be assigned to the parameter key
	 * @return ConfigInterface
	 */
	public function add( $parameter_key, $value );

	/**
	 * Removes an item or multiple items.
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed ...$with_keys
	 * @return ConfigInterface
	 */
	public function remove( ...$with_keys );

	/**
	 * Merge a new array into this config
	 *
	 * @since 1.1.0
	 *
	 * @param array ...$array_to_merge
	 * @return ConfigInterface
	 */
	public function merge( ...$array_to_merge );

	/**
	 * Return an array of items
	 *
	 * @return array
	 */
	public function toArray(): array;

	/**
	 * Return a Json object of items
	 *
	 * @return string
	 */
	public function toJson(): string;
}
