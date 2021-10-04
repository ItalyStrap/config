<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

use ArrayObject;

/**
 * Config Class
 *
 * @todo Immutable: https://github.com/jkoudys/immutable.php
 * @todo Maybe some ideas iterator: https://github.com/clean/data/blob/master/src/Collection.php
 * @todo Maybe some ideas json to array: https://github.com/Radiergummi/libconfig/blob/master/src/Libconfig/Config.php
 * @todo Maybe some ideas: https://www.simonholywell.com/post/2017/04/php-and-immutability-part-two/
 */
class Config extends ArrayObject implements ConfigInterface {

	use ArrayObjectTrait;

	/**
	 * @var array[]
	 */
	private $storage = [];

	/**
	 * @var array
	 */
	private $temp = [];

	/**
	 * @var mixed
	 */
	private $default;

	/**
	 * Array key level delimiter
	 * @var string
	 */
	private static $delimiter = ".";

	/**
	 * Config constructor
	 *
	 * @param array $config
	 * @param array $default
	 */
	public function __construct( $config = [], $default = [] ) {
		$this->merge( $default, $config );
		parent::__construct( $this->storage, ArrayObject::ARRAY_AS_PROPS );
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array {
		return $this->getArrayCopy();
	}

	/**
	 * @inheritDoc
	 */
	public function get( $key, $default = null ) {
		$this->default = $default;

		if ( ! $this->has( $key ) ) {
			return $default;
		}

		// The class::temp variable is always setted by the class::has() method
		return $this->temp;
	}

	/**
	 * @inheritDoc
	 */
	public function has( $key ): bool {
		$this->temp = $this->search( $this->storage, $key, $this->default );
		$this->default = null;
		return isset( $this->temp );
	}

	/**
	 * @inheritDoc
	 */
	public function add( $key, $value ) {
		$this->storage[ $key ] = $value;
		parent::exchangeArray( $this->storage );
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function push( $key, $value ) {
		return $this->add( $key, $value );
	}

	/**
	 * @inheritDoc
	 */
	public function remove( ...$with_keys ): ConfigInterface {

		foreach ( $with_keys as $keys ) {
			foreach ( (array) $keys as $k ) {
				unset( $this->storage[ $k ] );
			}
		}

		parent::exchangeArray( $this->storage );
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function merge( ...$array_to_merge ): ConfigInterface {

		foreach ( $array_to_merge as $key => $arr ) {
			if ( $arr instanceof \Traversable ) {
				$arr = \iterator_to_array( $arr );
			}
			// Make sure any value given is casting to array
			$array_to_merge[ $key ] = (array) $arr;
		}

		// We don't need to foreach here, \array_replace_recursive() do the job for us.
		$this->storage = (array) \array_replace_recursive( $this->storage, ...$array_to_merge );
		parent::exchangeArray( $this->storage );
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array {
		return \iterator_to_array( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function toJson(): string {
		return \strval( \json_encode( $this->toArray() ) );
	}

	/**
	 *
	 */
	public function __clone() {
		$this->storage = [];
		parent::exchangeArray( $this->storage );
	}

	/**
	 * @link https://www.php.net/manual/en/class.arrayobject.php#107079
	 *
	 * @param $func
	 * @param $argv
	 * @return mixed
	 */
//	public function __call( $func, $argv ) {
//
//		if ( \array_key_exists( $func, $this->storage ) && \is_callable( $this->storage[ $func ] ) ) {
//			codecept_debug( $argv );
//			$new_func = function ( ...$argv ) use ( $func ) {
//				return $func( $argv );
//			};
//			return \call_user_func_array( $new_func, $argv );
//		}
//
//		if ( ! \is_callable( $func ) || \substr( $func, 0, 6 ) !== 'array_' ) {
//			throw new BadMethodCallException(__CLASS__ . '->' . $func );
//		}
//
//		return \call_user_func_array( $func, \array_merge( [ $this->getArrayCopy() ], $argv ) );
//	}

	/**
	 * @todo In future move this method to its own class
	 *
	 * @link https://github.com/balambasik/input/blob/master/src/Input.php
	 *
	 * @param array $array
	 * @param string|int $key
	 * @param mixed $default
	 * @return mixed
	 */
	private static function search( array $array, $key, $default = null ) {

		if ( \is_int($key) || \strripos( $key, self::$delimiter ) === false ) {
			return \array_key_exists( $key, (array) $array ) ? $array[ $key ] : $default;
		}

		$levels = (array) \explode( self::$delimiter, $key );
		foreach ( $levels as $level ) {
			if ( $array instanceof \Traversable ) {
				$array = \iterator_to_array( $array );
			}

			if ( ! \array_key_exists( \strval( $level ), (array) $array ) ) {
				return $default;
			}

			if ( $array instanceof \stdClass ) {
				$array = (array) $array;
			}

			$array = $array[ $level ];
		}

		return $array ?? $default;
	}

	/**
	 * @inheritDoc
	 */
	public function count() {
		return parent::count();
	}
}
