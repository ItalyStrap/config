<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

use ItalyStrap\Storage\StoreInterface;

/**
 * @template TKey as array-key
 * @template TValue
 * @template-extends \ArrayAccess<TKey, TValue>
 * @template-extends \IteratorAggregate<TKey, TValue>
 */
interface ConfigInterface extends Config_Interface, StoreInterface, \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Get the specified configuration value.
     *
     * @param  TKey|string  $key
     * @param  TValue $default
     * @return TValue
     */
    public function get($key, $default = null);

    /**
     * Determine if the given configuration value exists.
     *
     * @param TKey|string  $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * Add a configuration in via the key
     *
     * @param TKey $key Key to be assigned, which also becomes the property
     * @param TValue $value Value to be assigned to the parameter key
     * @return ConfigInterface
     */
//    public function set($key, $value): bool;

    /**
     * @param  TKey ...$with_keys
     * @return ConfigInterface
     */
//    public function remove(...$with_keys);

    /**
     * @param array<TKey, TValue> ...$array_to_merge
     * @return ConfigInterface
     */
    public function merge(...$array_to_merge): ConfigInterface;

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
