<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

/**
 * @template TKey as array-key
 * @template TValue
 * @template-implements \ArrayAccess<TKey, TValue>
 * @template-implements \IteratorAggregate<TKey, TValue>
 */
interface ConfigInterface extends Config_Interface, \ArrayAccess, \IteratorAggregate, \Countable {

	/**
	 * Get the specified configuration value.
	 *
	 * @param  TKey  $index
	 * @param  TValue $default
	 * @return TValue
	 */
	public function get( $index, $default = null );

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param TKey  $index
	 * @return bool
	 */
	public function has( $index ): bool;

	/**
	 * Add a configuration in via the key
	 *
	 * @param TKey $index Key to be assigned, which also becomes the property
	 * @param TValue $value Value to be assigned to the parameter key
	 * @return ConfigInterface
	 */
	public function add( $index, $value );

	/**
	 * @param  TKey ...$with_indexes
	 * @return ConfigInterface
	 */
	public function remove( ...$with_indexes );

	/**
	 * @param array<TKey, TValue> ...$array_to_merge
	 * @return ConfigInterface
	 */
	public function merge( ...$array_to_merge ): ConfigInterface;

	/**
	 * @return array<TKey, TValue>
	 */
	public function all(): array;

	/**
	 * @return array<TKey, TValue>
	 */
	public function toArray(): array;

	/**
	 * @return string
	 */
	public function toJson(): string;
}
