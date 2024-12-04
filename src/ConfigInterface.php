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
     * @param TKey  $key
     */
    public function has($key): bool;

    /**
     * @param array<TKey, TValue> ...$array_to_merge
     */
    public function merge(...$array_to_merge): ConfigInterface;

    /**
     *
     * @param callable(TValue, TKey, Config, array): void $visitor
     */
    public function traverse(callable ...$visitor): void;

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;
}
