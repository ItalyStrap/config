# Manipulating Node Values with `appendTo()`, `prependTo()`, `insertAt()` and `deleteFrom()`

The `Config` class is designed to work well with multidimensional arrays addressed via *dot notation* paths (e.g. `"key.subKey"`) and array notation (e.g. `['key', 'subKey']`).

Besides the usual `get()`, `set()`, `has()` and `delete()` methods, `Config` also provides a small set of **node manipulation methods** that make it easy to work with values that are expected to be *lists* (arrays) at a specific path.

These methods are useful when you want to:

- Avoid the classic workflow of `get()` → mutate array → `set()`.
- Append/prepend/insert items to a list at a nested path.
- Remove items from a list at a nested path.

---

## Table of Contents

- [Overview](#overview)
- [Supported key formats](#supported-key-formats)
- [Method summary](#method-summary)
- [Usage Examples](#usage-examples)
    - [Example 1: Appending values to the end of a list](#example-1-appending-values-to-the-end-of-a-list)
    - [Example 2: Prepending values to the beginning of a list](#example-2-prepending-values-to-the-beginning-of-a-list)
    - [Example 3: Inserting values at a specific position](#example-3-inserting-values-at-a-specific-position)
    - [Example 4: Removing values from a list](#example-4-removing-values-from-a-list)
    - [Example 5: Using `traverse()` for bulk cleanup](#example-5-using-traverse-for-bulk-cleanup)
- [Important Notes](#important-notes)
    - [1) These methods work on lists (arrays)](#1-these-methods-work-on-lists-arrays)
    - [2) Duplicates are allowed](#2-duplicates-are-allowed)
    - [3) `deleteFrom()` removes only the first occurrence](#3-deletefrom-removes-only-the-first-occurrence)
    - [4) `deleteFrom()` searches by VALUE, not by KEY](#4-deletefrom-searches-by-value-not-by-key)
    - [5) Integers, strings, and strict comparisons](#5-integers-strings-and-strict-comparisons)
- [Conclusion](#conclusion)

---

## Overview

The following methods operate on a *single node* identified by a path:

- `Config::appendTo($key, $value)`
- `Config::prependTo($key, $value)`
- `Config::insertAt($key, $value, int $position)`
- `Config::deleteFrom($key, $value)`

All these methods:

- Work on **nested paths**, not only on the root.
- Expect the value stored at `$key` (if present) to be an **array/list**.
- If the node does not exist, `appendTo()`, `prependTo()` and `insertAt()` treat it as an empty list and create it.
- Throw a `RuntimeException` if the node exists, but it is not an array.

---

## Supported key formats

All methods accept any key format already supported by `Config::get()` / `Config::set()`:

- Dot notation string: `"settings.plugins"`
- Array notation: `['settings', 'plugins']`
- Single segment string/int: `"plugins"` or `0`

---

## Method summary

### `appendTo()`

Appends one or more values to the end of the list stored at `$key`.

- If `$value` is a scalar or an object, it will be appended as a single element.
- If `$value` is an array, it will be appended element-by-element (i.e. merged).
- Duplicates are allowed.

```php
// Initial state: ['items' => ['apple']]

$config->appendTo('items', 'orange'); // The same as $config->appendTo('items', ['orange']);
// ['items' => ['apple', 'orange']]

$config->appendTo('items', ['banana', 'apple']);
// ['items' => ['apple', 'orange', 'banana', 'apple']]
```

### `prependTo()`

Behaves like `appendTo()`, but it prepends one or more values to the beginning of the list stored at `$key`.

```php
// Initial state: ['items' => ['apple']]

$config->prependTo('items', 'orange'); // The same as $config->prependTo('items', ['orange']);
// ['items' => ['orange', 'apple']]

$config->prependTo('items', ['banana', 'apple']);
// ['items' => ['banana', 'apple', 'orange', 'apple']]
```

### `insertAt()`

Inserts one or more values at a given position in the list stored at `$key`.

```php
// Initial state: ['items' => ['apple', 'orange']]

$config->insertAt('items', 'banana', 1);
// ['items' => ['apple', 'banana', 'orange']]
```

### `deleteFrom()`

Removes values from the list stored at `$key`.

Current behavior:

- The value is removed by **searching the first occurrence** of each requested value.
- If the last element is removed, the key is deleted and `get($key)` will return `null`.
- If the key does not exist, the method returns `true`.

```php
// Initial state: ['items' => ['apple', 'banana', 'orange', 'banana']]

$config->deleteFrom('items', 'banana');
// ['items' => ['apple', 'orange', 'banana']] // only the first 'banana' is removed

$config->deleteFrom('items', ['banana', 'orange']);
// ['items' => ['apple']]
```

---

## Usage Examples

### Example 1: Appending values to the end of a list

```php
use ItalyStrap\Config\Config;

$config = new Config([
    'key' => [
        'subKey' => ['value1'],
    ],
]);

$config->appendTo('key.subKey', 'value2');

var_dump($config->get('key.subKey'));
// ['value1', 'value2']
```

### Example 2: Prepending values to the beginning of a list

```php
use ItalyStrap\Config\Config;

$config = new Config([
    'items' => ['apple', 'banana'],
]);

$config->prependTo('items', 'orange');

var_dump($config->get('items'));
// ['orange', 'apple', 'banana']
```

### Example 3: Inserting values at a specific position

```php
use ItalyStrap\Config\Config;

$config = new Config([
    'items' => ['apple', 'orange'],
]);

$config->insertAt('items', 'banana', 1);

var_dump($config->get('items'));
// ['apple', 'banana', 'orange']
```

### Example 4: Removing values from a list

By default, `deleteFrom()` removes the first occurrence.

```php
use ItalyStrap\Config\Config;

$config = new Config([
    'items' => ['apple', 'banana', 'banana', 'orange'],
]);

$config->deleteFrom('items', 'banana');

var_dump($config->get('items'));
// ['apple', 'banana', 'orange']
```

If you need to remove all duplicates or apply complex rules, prefer using `traverse()`.

### Example 5: Using `traverse()` for bulk cleanup

The node manipulation methods are intentionally lightweight.

When you need deep or bulk changes (for example: remove duplicate entries in many places), the recommended tool is `Config::traverse()`.

Example: remove duplicated values from all lists in the entire configuration structure:

```php
use ItalyStrap\Config\Config;
use ItalyStrap\Config\SignalCode;

$config = new Config([
    'config' => [
        'allow-plugins' => [
            'plugin-a',
            'plugin-b',
            'plugin-a', // duplicate
            'plugin-c',
        ],
    ],
    'tags' => ['php', 'javascript', 'php', 'python'], // duplicates
    'settings' => [ // associative array, will NOT be modified
        'key1' => 'value1',
        'key2' => 'value2',
    ],
]);

$config->traverse(static function (&$current): ?int {
    // Only process sequential arrays (lists), not associative arrays
    if (
        \is_array($current)
        && $current !== []
        // Check if it's a list (sequential numeric keys)
        && \array_keys($current) !== range(0, count($current) - 1)
    ) {
        // Remove duplicates and reindex
        $current = \array_values(\array_unique($current, \SORT_REGULAR));
    }
    return SignalCode::NONE;
});

var_dump($config->toArray());
// [
//     'config' => [
//         'allow-plugins' => ['plugin-a', 'plugin-b', 'plugin-c'],
//     ],
//     'tags' => ['php', 'javascript', 'python'],
//     'settings' => [
//         'key1' => 'value1',
//         'key2' => 'value2',
//     ],
// ]
```

You can also target a specific path if you only want to clean up one list:

```php
$config->traverse(static function (mixed &$current, string|int $key, Config $config, array $path): ?int {
    // Only operate on the specific allow-plugins list
    if ($path === ['config', 'allow-plugins'] && is_array($current)) {
        $current = array_values(array_unique($current, SORT_REGULAR));
    }
    return SignalCode::NONE;
});
```

---

## Important Notes

### 1) These methods work on lists (arrays)

If the value at the given path exists, and it is not an array, a `RuntimeException` is thrown.

This is by design: `appendTo()`, `prependTo()`, `insertAt()` and `deleteFrom()` are meant to manipulate lists at specific nodes.

### 2) Duplicates are allowed

`appendTo()`, `prependTo()` and `insertAt()` **do not attempt to deduplicate** values.

If you need set-like behavior, use `traverse()` (see [Example 5](#example-5-using-traverse-for-bulk-cleanup)) or normalize the value before appending.

### 3) `deleteFrom()` removes only the first occurrence

This behavior is intentionally similar to list semantics in other languages.

If you need to remove all occurrences, use `traverse()` or perform repeated `deleteFrom()` calls.

### 4) `deleteFrom()` searches by VALUE, not by KEY

`deleteFrom()` is designed for **list manipulation**, not associative array key removal.

It uses `array_search()` internally, which means:

- It searches for the **value** you want to remove, not the key.
- Passing a key name will **not** remove that key; it will search for an element whose value equals that key name.

```php
$config = new Config([
    'plugins' => [
        'plugin1' => 'value1',
        'plugin2' => 'value2',
    ],
]);

// This does NOT remove the 'plugin1' key!
// It searches for an element with value 'plugin1' (which doesn't exist)
$config->deleteFrom('plugins', 'plugin1');
// Result: ['plugin1' => 'value1', 'plugin2' => 'value2'] (unchanged)

// To remove by value, pass the actual value:
$config->deleteFrom('plugins', 'value1');
// Result: ['plugin2' => 'value2']

// To remove by key, use the delete() method instead:
$config->delete('plugins.plugin1');
// or
$config->delete(['plugins', 'plugin1']);
```

**Rule of thumb:**
- Use `deleteFrom()` for **sequential lists** where you want to remove items by their value.
- Use `delete()` for **associative arrays** where you want to remove items by their key.

### 5) Integers, strings, and strict comparisons

`deleteFrom()` uses strict comparisons (`array_search(..., true)`), so:

- `1` and `'1'` are treated as different values.
- `true` and `1` are treated as different values.

---

## Conclusion

Node manipulation methods let you treat a nested path as a list and operate on it directly:

- Use `appendTo()` to push values to the end.
- Use `prependTo()` to push values to the start.
- Use `insertAt()` when you need positional insertion.
- Use `deleteFrom()` to remove values (first match).

When you need advanced or cross-tree manipulations, use `Config::traverse()`.

