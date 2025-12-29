<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

/**
 * Interface for manipulating list nodes in configuration structures.
 *
 * These methods operate on values that are expected to be arrays (lists)
 * at a specific path, supporting both dot notation and array notation keys.
 *
 * @template TKey as array-key
 * @template TValue
 */
interface NodeManipulationInterface
{
    /**
     * Appends one or more values to the end of the list stored at $key.
     *
     * If $value is a scalar or an object, it will be appended as a single element.
     * If $value is an array, it will be appended element-by-element (merged).
     * Duplicates are allowed.
     *
     * If the key does not exist, an empty array is created first.
     *
     * @param TKey|string|int|array $key The path to the list (dot notation or array notation)
     * @param TValue $value The value(s) to append
     * @return bool True on success
     * @throws \RuntimeException If the value at $key exists but is not an array
     */
    public function appendTo($key, $value): bool;

    /**
     * Prepends one or more values to the beginning of the list stored at $key.
     *
     * If $value is a scalar or an object, it will be prepended as a single element.
     * If $value is an array, it will be prepended element-by-element (merged).
     * Duplicates are allowed.
     *
     * If the key does not exist, an empty array is created first.
     *
     * @param TKey|string|int|array $key The path to the list (dot notation or array notation)
     * @param TValue $value The value(s) to prepend
     * @return bool True on success
     * @throws \RuntimeException If the value at $key exists but is not an array
     */
    public function prependTo($key, $value): bool;

    /**
     * Inserts one or more values at a given position in the list stored at $key.
     *
     * If $value is a scalar or an object, it will be inserted as a single element.
     * If $value is an array, it will be inserted element-by-element.
     *
     * If the key does not exist, an empty array is created first.
     *
     * @param TKey|string|int|array $key The path to the list (dot notation or array notation)
     * @param TValue|mixed $value The value(s) to insert
     * @param int $position The zero-based position at which to insert
     * @return bool True on success
     * @throws \RuntimeException If the value at $key exists but is not an array
     */
    public function insertAt($key, $value, int $position): bool;

    /**
     * Removes values from the list stored at $key.
     *
     * Only the first occurrence of each value is removed (strict comparison).
     * If the last element is removed, the key is deleted entirely.
     * If the key does not exist, returns true.
     *
     * @param TKey|string|int|array $key The path to the list (dot notation or array notation)
     * @param TValue|mixed $value The value(s) to remove
     * @return bool True on success
     * @throws \RuntimeException If the value at $key exists but is not an array
     */
    public function deleteFrom($key, $value): bool;
}
