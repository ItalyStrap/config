<?php
/**
 * Config Class that handle the classes configuration
 *
 * @package ItalyStrap\Config
 */

declare(strict_types=1);

namespace ItalyStrap\Config;

use \ArrayObject;

/**
 * Config Class
 *
 * @todo Maybe some ideas: https://github.com/clean/data/blob/master/src/Collection.php
 */
class Config extends ArrayObject implements Config_Interface {

	/**
	 * Config constructor
	 *
	 * @param array $config
	 * @param array $default
	 */
	function __construct( array $config = [], array $default = [] ) {
		parent::__construct( \array_replace_recursive( $default, $config ), ArrayObject::ARRAY_AS_PROPS );
	}

	/**
	 * Retrieves all of the runtime configuration parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function all() : array {
		return (array) $this->getArrayCopy();
	}

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get( string $key, $default = null ) {

		if ( ! $this->offsetExists( $key ) ) {
			return $default;
		}

		return $this->offsetGet( $key );
	}

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function has( string $key ) : bool {
		return (bool) $this->offsetExists( $key );
	}

	/**
	 * Push a configuration in via the key
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Key to be assigned, which also becomes the property
	 * @param mixed $value Value to be assigned to the parameter key
	 * @return self
	 */
	public function push( string $key, $value ) : self {
		$this->offsetSet( $key, $value );
		return $this;
	}

	/**
	 * Removes an item or multiple items.
	 *
	 * @since 2.0.0
	 *
	 * @param  mixed ...$with_keys
	 * @return self
	 */
	public function remove( ...$with_keys ) : self {

		foreach ( $with_keys as $keys ) {
			foreach ( (array) $keys as $k ) {
				$this->offsetUnset( $k );
			}
		}

		return $this;
	}

	/**
	 * Merge a new array into this config
	 *
	 * @param array ...$array_to_merge
	 * @return self
	 * @since 1.1.0
	 */
	public function merge( array ...$array_to_merge ) : self {

		foreach ( $array_to_merge as $arr ) {
			$items = \array_replace_recursive( $this->getArrayCopy(), $arr );

			foreach ( $items as $key => $value ) {
				$this->offsetSet( $key, $value );
			}
		}

		return $this;
	}

	/**
	 * Magic method when trying to get a property.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get( string $key ) {
		return $this->get( $key, null );
	}

	/**
	 * Magic method when trying to set a property. Assume the property is
	 * part of the collection and add it.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return self
	 */
	public function __set( string $key, $value ) : self {
		return $this->push( $key, $value );
	}

	/**
	 * Magic method when trying to unset a property.
	 *
	 * @param  string|array  $key
	 * @return self
	 */
	public function __unset( $key ) : self {
		$this->remove( $key );
		return $this;
	}

	/**
	 * Magic method when trying to check if a property has.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function __isset( string $key ) : bool {
		return (bool) $this->has( $key );
	}
}
