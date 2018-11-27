<?php
/**
 * Config Class that handle the classes configuration
 *
 * @package ItalyStrap\Config
 */

namespace ItalyStrap\Config;

use \ArrayObject;

/**
 * Config Class
 */
class Config extends ArrayObject implements Config_Interface {

	/**
	 * @var array
	 */
	protected $items = array();

	/**
	 * Init object
	 *
	 * @param array $config
	 * @param array $default
	 */
	function __construct( array $config = array(), array $default = array() ) {
		$this->items = array_replace_recursive( $default, $config );
		parent::__construct( $this->items, ArrayObject::ARRAY_AS_PROPS );
	}

	/**
	 * Retrieves all of the runtime configuration parameters
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function all() {
		return (array) $this->items;
	}

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public function get( $key, $default = null ) {

		if ( ! $this->has( $key ) ) {
			$this->push( $key, $default );
		}

		return $this->items[ $key ];
	}

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	public function has( $key ) {

		return (bool) array_key_exists( $key, $this->items );
	}

	/**
	 * Push a configuration in via the key
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Key to be assigned, which also becomes the property
	 * @param mixed $value Value to be assigned to the parameter key
	 * @return null
	 */
	public function push( $key, $value ) {
		$this->items[ $key ] = $value;
		$this->offsetSet( $key, $value );
	}

	/**
	 * Merge a new array into this config
	 *
	 * @since 1.1.0
	 *
	 * @param array $array_to_merge
	 * @return null
	 */
	public function merge( array $array_to_merge ) {
		$this->items = array_replace_recursive( $this->items, $array_to_merge );

		array_walk( $this->items, function ( $value, $key )  {
			$this->offsetSet( $key, $value );
		} );
	}
}
