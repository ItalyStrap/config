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
     * Determine if the given configuration value exists.
     *
     * @param TKey|string  $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * @param array<TKey, TValue> ...$array_to_merge
     * @return ConfigInterface
     */
    public function merge(...$array_to_merge): ConfigInterface;

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;
}
