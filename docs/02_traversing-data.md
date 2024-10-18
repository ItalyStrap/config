# Traversing the Configuration Data with `traverse`

The `traverse` method allows you to recursively traverse and manipulate your configuration data. It applies a callback function to each element in the configuration array, providing a powerful way to modify values, remove elements, or perform actions based on the data.

---

## Table of Contents

- [Overview](#overview)
- [Callback Function Signature](#callback-function-signature)
- [Traversal Order](#traversal-order)
- [Usage Examples](#usage-examples)
    - [Example 1: Modifying Numeric Values](#example-1-modifying-numeric-values)
    - [Example 2: Removing Elements Based on a Condition](#example-2-removing-elements-based-on-a-condition)
    - [Example 3: Using the Full Key Path](#example-3-using-the-full-key-path)
    - [Example 4: Adding New Keys During Traversal](#example-4-adding-new-keys-during-traversal)
- [Important Notes](#important-notes)
- [Conclusion](#conclusion)

---

## Overview

The `traverse` method provides a way to:

- **Recursively iterate** over all elements in the configuration data.
- Apply a **user-defined callback function** to each element.
- **Add**, **Modify or Remove** elements during traversal.
- Access **contextual information** such as the current key (string|int), the full key path (array<string>), and the `ConfigInterface` instance.

---

## Callback Function Signature

The callback function you provide to `traverse` can accept up to four parameters:

```php
function callback(mixed &$current, string|int $key, ConfigInterface $config, array $keyPath): void
```

- `mixed &$current`: The current value being processed. Passed by reference, allowing you to modify it directly.
- `string|int $key`: The current key associated with the value.
- `ConfigInterface $config`: The Config instance. Provides access to the entire configuration data and its methods.
- `array<string> $keyPath`: An array representing the full key path from the root to the current element.

**Note:** The callback can accept fewer parameters if not all are needed.

---

## Traversal Order

- Depth-First Traversal: The traverse method performs a depth-first traversal. This means that deeper levels are processed before their parent levels.
- Implications:
- - Modifications to child elements occur before the parent is processed.
- - When processing a parent, you have access to any changes made to its children.
- - This order is important if you need to make decisions in the parent based on the state of its children.

Said so, if you have an array like this:

```php
$data = [
    'items' => [
        'item1' => 'value1',
        'item2' => [
            'subitem1' => 'sub value1',
        ],
    ],
];
```

The `traverse` method will process the elements in the following order:

1. `'subitem1' => 'sub value1'`
2. `'item2' => ['subitem1' => 'sub value1']`
3. `'item1' => 'value1'`

**Note:** Recursion in PHP is expensive in terms of memory usage. Be cautious when traversing large or deeply nested data structures.

---

## Usage Examples

### Example 1: Modifying Numeric Values

**Objective:** Multiply all even numbers in the configuration by 10.

**Code:**

```php
$config = new Config([
    'numbers' => [1, 2, 3, 4, 5],
]);

$config->traverse(static function (mixed &$current): void {
    if (\is_numeric($current) && $current % 2 === 0) {
        $current *= 10;
    }
});

// Resulting configuration:
// 'numbers' => [1, 20, 3, 40, 5]
```

**Explanation:**
- The callback checks if the current value is a numeric even number.
- If so, it multiplies the value by 10.
- Only `$current` is used in the callback; other parameters are not needed.

---

### Example 2: Removing Elements Based on a Condition

**Objective:** Remove all elements with the key `'remove'` set to `true`.

**Code:**

```php
$config = new Config([
    'items' => [
        ['name' => 'Item 1', 'remove' => false],
        ['name' => 'Item 2', 'remove' => true],
        ['name' => 'Item 3', 'remove' => false],
    ],
]);

$config->traverse(static function (mixed &$current, string|int $key, ConfigInterface $config, array $keyPath): void {
    if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
        $current = null; // This will remove the element
    }
    
    // Or
    if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
        $config->delete($keyPath);
    }
});

// Resulting configuration:
// 'items' => [
//     ['name' => 'Item 1', 'remove' => false],
//     ['name' => 'Item 3', 'remove' => false],
// ]
```

**Explanation:**
- The callback checks if the current value is an array with a `'remove'` key set to `true`.
- If so, it sets the value to `null`, effectively removing the element.
- Only `$current` is used in the callback; other parameters are not needed.
- The `delete` method can also be used to remove elements, just pass the `$keyPath` array as the argument.

---

### Example 3: Using the Full Key Path

**Objective:** Modify a value based on its location in the configuration.

**Code:**

```php
$config = new Config([
    'settings' => [
        'feature' => [
            'enabled' => true,
        ],
    ],
]);

$config->traverse(static function (mixed &$current, string|int $key, ConfigInterface $config, array $keyPath): void {
    $path = \implode('.', $keyPath);
    if ($path === 'settings.feature.enabled' && $current === true) {
        $current = false; // Disable the feature
    }
    // Or
    if ($key === 'enabled' && $current === true) {
        // 'settings.feature.enabled'
        $config->set($keyPath, false);
    }
});

// Resulting configuration:
// 'settings' => [
//     'feature' => [
//         'enabled' => false,
//     ],
// ]
```

**Explanation:**
- The callback constructs the full key path using `$keyPath`.
- If the path matches `'settings.feature.enabled'` and the value is `true`, it sets the value to `false`.
- This demonstrates how to target specific elements based on their location.
- The `set` method can also be used to modify elements, just pass the full key path as the first argument (passing an array of keys or passing a string with dot notation is the same, just pass a path with the position you want to modify).

---

### Example 4: Adding New Keys During Traversal

**Objective:** Add a new item to the configuration when a condition is met.

**Code:**

```php
$config = new Config([
    'users' => [
        'user1' => ['role' => 'admin'],
    ],
]);

$config->traverse(static function (mixed &$current, string|int $key, ConfigInterface $config): void {
     if ($key === 'user1' && \array_key_exists('role', $current) && $current['role'] === 'admin') {
        // Add a new user to the 'users' array to the $current
        $current['user2'] = ['role' => 'editor'];
     }

    // Or
    if ($key === 'user1' && \array_key_exists('role', $current) && $current['role'] === 'admin') {
        // Add a new user to the 'users' array
        $config->set('users.user2', ['role' => 'editor']);
    }
});

// Resulting configuration:
// 'users' => [
//     'user1' => ['role' => 'admin'],
//     'user2' => ['role' => 'editor'],
// ]
```

**Explanation:**
- When the callback finds `'user1'` with the role `'admin'`, it adds a new user `'user2'`.
- The `ConfigInterface` instance (`$config`) is used to modify the configuration outside the current traversal path.
- This shows how to leverage the `ConfigInterface` instance within the callback.
- You can also assign a new value to `$current` directly.

---

## Important Notes

- **Parameter Flexibility:** The callback function can accept any number of parameters up to the four provided. If you don't need all parameters, you can define the callback with fewer parameters.
    ```php
    // Only use $current
    $config->traverse(static function (mixed &$current): void {
        // ...
    });
    
    // Use $current and $key
    $config->traverse(static function (mixed &$current, string|int $key): void {
        // ...
    });
    ```
- **Modification by Reference:** The `$current` parameter is passed by reference. Changes made to `$current` directly affect the configuration data.
- **Traversal Direction:** Since deeper levels are traversed first (depth-first), changes to child elements occur before their parents are processed.
- **Removing Elements:** Setting `$current` to `null` or an empty array will remove the element from the configuration. If a parent array becomes empty after removing its children, it will also be removed.
- **Accessing the ConfigInterface Instance:** The `$config` parameter provides access to the entire configuration and all its available methods.
- **Full Key Path:** The `$keyPath` parameter is an array representing the path from the root to the current element. Use `\implode('.', $keyPath)` to get a string representation.

---

## Conclusion

The `traverse` method is a powerful tool for recursively processing and manipulating your configuration data. By providing a flexible callback mechanism and access to contextual information, it enables complex transformations and data handling with ease.

Remember to consider the traversal order and the implications of modifying the configuration during traversal. With careful use, `traverse` can greatly simplify tasks that involve complex data structures.

