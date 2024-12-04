# Traversing the Configuration Data with `Config::traverse()`

The `traverse` method allows you to recursively traverse and manipulate your configuration data. It applies one or many callbacks (Visitors) function to each node in the configuration array, providing a powerful way to modify values, remove nodes, or perform actions based on the data.

The `traverse` behavior follow the Visitor pattern, and it is inspired by the [PHP-Parser](https://github.com/nikic/PHP-Parser) library.

---

## Table of Contents

- [Overview](#overview)
- [Callback Function Signature](#callback-function-signature)
- [Traversal Order](#traversal-order)
- [Usage Examples](#usage-examples)
    - [Example 1: Modifying Numeric Values](#example-1-modifying-numeric-values)
    - [Example 2: Removing Nodes Based on a Condition](#example-2-removing-nodes-based-on-a-condition)
    - [Example 3: Using the Full Key Path](#example-3-using-the-full-key-path)
    - [Example 4: Adding New Keys During Traversal](#example-4-adding-new-keys-during-traversal)
    - [Example 5: Using SignalCode::STOP_TRAVERSAL or SignalCode::SKIP_CHILDREN](#example-5-using-signalcode::stop_traversal-or-signalcode::skip_children)
- [Important Notes](#important-notes)
- [Conclusion](#conclusion)

---

## Overview

The `traverse` method provides a way to:

- **Recursively iterate** over all nodes in the configuration data.
- Apply **user-defined callbacks function** to each node.
- **Add**, **Modify or Remove** nodes during traversal.
- Access **contextual information** such as the current value (`mixed`), the current key (`string|int`), a self instance (`ConfigInterface`) and the full key path (`array<string>`).

---

## Callback Function Signature and return type

The callback function you provide to `traverse` can accept up to four parameters:

```php
static function callback(mixed &$current, string|int $key, ConfigInterface $config, array $path): void|null|int
```

- `mixed &$current`: The current value being processed. Passed by reference, allowing you to modify it directly.
- `string|int $key`: The current key associated with the value.
- `ConfigInterface $config`: The Config instance. Provides access to the entire configuration data and its methods.
- `array<string> $path`: An array representing the full key path from the root to the current node.

**Note:** The callback can accept fewer parameters if not all are needed.

The callback function can return one of the following values:

- `null`: By default, the callback returns `null`. This indicates no specific behavior and allows the traversal to continue.
- `int`: An integer value that provides specific instructions for the traversal behavior, you can use the `SignalCode` class to return one of the values provided by the class.
- `void`: If you don't need to return anything from the callback, you can define the callback function as `void`, the callback will return `null` by default.

You can use the `SignalCode` helper class to return specific values for the traversal behavior:

- `SignalCode::NONE`: Indicates no specific behavior and allows the traversal to continue, this is the default value.
- `SignalCode::STOP_TRAVERSAL`: Indicates that the traversal will stop immediately.
- `SignalCode::REMOVE_NODE`: Indicates that the current node should be removed from the configuration, after the node is removed the traversal will continue with the next sibling node.
- `SignalCode::CONTINUE`: Indicates that the traversal should continue with the next sibling node, this must be used in conjunction with the `delete` method if you want to remove the current node and avoid unexpected behavior.
- `SignalCode::SKIP_CHILDREN`: Indicates that the traversal should skip the children of the current node and continue the traversal.

**Note:** Other return types not listed here will be ignored.

**Warning:** Always use the constants provided by the `SignalCode` class to return a specific value other than `null`. Do not return any number directly, as the numeric values may change in the future, leading to unexpected behavior in the traversal.

---

## Traversal Order and Visitors execution order

- Parent-First Traversal: The traverse method performs a parent-first traversal. This means that parent nodes are processed before their children.
- Implications:
- - Modifications to child nodes occur after the parent is processed.
- - Removing a parent node will also remove all its children.

Said so, if you have an array like this:

```php
$data = [
    'items' => [
        'item1' => 'value1',
        'item2' => [
            'subitem1' => 'sub value1',
        ],
        'item3' => 'value3',
    ],
];
```

The `traverse` method will process the nodes in the following order:

1. `'items'` (parent)
2. `'item1'` (child)
3. `'item2'` (child)
4. `'subitem1'` (grandchild)
5. `'item3'` (child)

**Note:** Recursion in PHP is expensive in terms of memory usage. Be cautious when traversing large or deeply nested data structures, but when I'm saying large, I'm talking about a really large amount of data, for most cases, you won't have any problem. (Premature optimization is the root of all evil. cit. Donald Knuth)

Each Visitor function is executed in the order they are provided to the `traverse` method:

```php
$config->traverse(
    static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
        // First Visitor
    },
    static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
        // Second Visitor
    },
    static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
        // Third Visitor
    }
);
```

In this example, the first visitor will be executed first, followed by the second visitor, and finally the third visitor.

For each node, all visitors are executed in sequence:

1. `'items'` (parent)
    - First Visitor
    - Second Visitor
    - Third Visitor
2. `'item1'` (child)
    - First Visitor
    - Second Visitor
    - Third Visitor
3. `'item2'` (child)
    - First Visitor
    - Second Visitor
    - Third Visitor
4. ...

**Note:** The order of execution is important to consider also when you need to return a `Signal` to change the behavior of the traversal.

---

## Usage Examples

### Example 1: Modifying Values

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
- The `$current` is passed by reference, so changes are reflected in the configuration.
- No return value is needed from the callback (you can also return `null` if you want), the traversal continues.

**Objective:** Modify values using more than one callback, in this example the second callback will be used as a logger.

**Code:**

```php
$config = new Config([
    'root' => [
        'items' => [
            'item1' => 'value1',
            'item2' => 'value2',
            'item3' => 'value3',
        ],
    ],
]);

$config->traverse(
  static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
        // We search for keys ending with a number and we uppercase the value
        if (\preg_match('/\d+$/', $key)) {
            $current = \strtoupper($current);
        }
        
        // Or

        if (\preg_match('/\d+$/', $key)) {
            $config->set($path, \strtoupper($current));
        }
  },
  static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
      // Assert the value is uppercase and log it
        if (\ctype_upper($current)) {
            \error_log(\sprintf('The value of the key "%s" is "%s"', \implode('.', $path), $current));
        }
  }
);

// Resulting configuration:
// 'root' => [
//     'items' => [
//         'item1' => 'VALUE1',
//         'item2' => 'VALUE2',
//         'item3' => 'VALUE3',
//     ],
// ]
```

**Explanation:**
- The first callback checks if the key ends with a number and uppercases the value.
- The second callback checks if the value is uppercase and logs it.
- The `set` method can also be used to modify nodes, just pass the `$path` array as the first argument to the method.
- The `$path` array is also used to construct the full key path for logging purposes.
- This demonstrates how to use multiple callbacks to perform different operations.

**Note:** The same as above in this case there is no need to return anything from the callback, the value is passed by reference and the second callback can directly access the changed value.

---

### Example 2: Removing Nodes Based on a Condition

**Objective:** Remove all nodes with the key `'remove'` set to `true`.

**Code:**

```php
$config = new Config([
    'items' => [
        ['name' => 'Item 1', 'remove' => false],
        ['name' => 'Item 2', 'remove' => true],
        ['name' => 'Item 3', 'remove' => false],
    ],
]);

$config->traverse(static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
    if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
        return \ItalyStrap\Config\SignalCode::REMOVE_NODE; // This will remove the node, but it keeps the parent node even if it is empty
    }
    
    // Or
    if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
        $config->delete($path); // This will remove the node too, but it keeps the parent node even if it is empty
        return \ItalyStrap\Config\SignalCode::CONTINUE; // Using the `::delete` method you need to return `::CONTINUE` value
    }
    
    return \ItalyStrap\Config\SignalCode::NONE;
});

// Resulting configuration:
// 'items' => [
//     ['name' => 'Item 1', 'remove' => false],
//     ['name' => 'Item 3', 'remove' => false],
// ]
```

**Explanation:**
- The callback checks if the current value is an array with a `'remove'` key set to `true`.
- If so, it returns `SignalCode::REMOVE_NODE` to remove the node.
- The `delete` method can also be used to remove nodes, just pass the `$path` array as the argument to the method, and return `SignalCode::CONTINUE` to continue the traversal.

You will ask what is the difference between `SignalCode::REMOVE_NODE` and `$config->delete($path); return SignalCode::CONTINUE;`?

Actually, they do the same thing, the former is a shorthand for the latter in a scenario where you need to perform simple operations like this, but let say you want to perform a deletion up to the tree for removing all the parent nodes that are empty:

```php
$config = new Config([
    'items' => [
        'item1' => [
            'properties' => [
                'subitem1' => [
                    'remove' => true,
                ],
            ],
        ],
        'item2' => [
            'properties' => [
                'subitem2' => [
                    'remove' => false,
                ],
            ],
        ],
    ],
]);

$config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
    if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
        do {
            $config->delete($path);
            \array_pop($path);
        } while ($config->get($path) === []);

        return SignalCode::CONTINUE;
    }

    return SignalCode::NONE;
});

// Resulting configuration:
// 'items' => [
//     'item2' => [
//         'properties' => [
//             'subitem2' => [
//                 'remove' => false,
//             ],
//         ],
//     ],
// ]
```

**Explanation:**
- The callback removes the node with `'remove' => true`.
- The `do-while` loop continues until the parent node is not empty.
- This demonstrates how to remove nodes and their parents based on a condition.

**Note:** Array indexes (numerical) are preserved, so the keys will not be reindexed.

If you remove a node with a numeric key, let say 0, and you have also a node with a key 1, the key 1 will not be reindexed to 0, so the resulting configuration will be:

```php
// Resulting configuration:
// 'items' => [
//     1 => [
//         'properties' => [
//             'subitem2' => [
//                 'remove' => false,
//             ],
//         ],
//     ],
// ]
```

But what if you need to use more than one callback?

```php
$config = new Config([
    'items' => [
        'item1' => [
            'properties' => [
                'subitem1' => [
                    'remove' => true,
                ],
            ],
        ],
        'item2' => [
            'properties' => [
                'subitem2' => [
                    'remove' => false,
                ],
            ],
        ],
    ],
]);

$config->traverse(
    static function (&$current, $key, ConfigInterface $config, array $path) {
        if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
            return SignalCode::REMOVE_NODE;
        }

        // Or

        if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
            $config->delete($path);
            return SignalCode::CONTINUE;
        }

        return SignalCode::NONE;
    },
    static function (&$current, $key, ConfigInterface $config, array $path) {
        // Do something else here
        // The node will be the next sibling node
    }
);
```

**Explanation:**
- The first callback removes the node with `'remove' => true`.
- Because the first callback returns `SignalCode::REMOVE_NODE` or `SignalCode::CONTINUE`, the second callback will have access to the next sibling node.

**Important:** Because internally the `traverse` method uses a `foreach` loop, the `$current` value and the `$key` value keep the reference to the old value from the `$array` that is under processing (the value is removed in the `Config` state not inside the loop), to avoid this behavior and have the `$current` value and the `$key` value updated to the next sibling node, it is a best practice to return `SignalCode::CONTINUE` in the first callback if you use `$config->delete($path);`.

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

$config->traverse(static function (mixed &$current, string|int $key, ConfigInterface $config, array $path): void {
    if ($path === ['settings','feature','enabled'] && $current === true) { // A demonstration of how to use array notation
        $current = false; // Disable the feature
    }
    // Or
    $path = \implode('.', $path); // A demonstration of how to use dot notation
    if ($path === 'settings.feature.enabled' && $current === true) {
        $current = false; // Disable the feature
    }
    // Or if `$key` is unique
    if ($key === 'enabled' && $current === true) {
        // 'settings.feature.enabled'
        $config->set($path, false); // Disable the feature
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
- The callback receives the full key path from `$path`.
- If the path matches `'settings.feature.enabled'` and the value is `true`, it sets the value to `false`.
- This demonstrates how to target specific nodes based on their location.
- Because the `$current` is passed by reference, you can modify it directly without the need to doing `$config->set($path, $value)` and/or return something.
- The `set` method can also be used to modify nodes, just pass the full key path as the first argument (passing an array of keys or passing a string with dot notation is the same, just pass a path with the position you want to modify).

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
- You can also assign a new value to `$current` directly as shown in the first example.

---

### Example 5: Using SignalCode::STOP_TRAVERSAL or SignalCode::SKIP_CHILDREN

**Objective:** Stop traversal of a node

**Code:**

```php
$config = new Config([
    'root' => [
        'items' => [
            'item1' => [
                'subitem1' => 'value1',
            ],
            'item2' => [
                'subitem2' => 'value2',
            ],
        ],
    ],
]);

$visitedPath = [];
$sut->traverse(
    static function (&$current, $key, ConfigInterface $config, array $path) use (&$visitedPath) {
        $visitedPath[] = $path;
        if ($path === ['root']) {
            return SignalCode::STOP_TRAVERSAL;
        }

        return SignalCode::NONE;
    },
    function (&$current, $key, ConfigInterface $config, array $path) {
        $this->fail('This callback should not be called');
    }
);

$this->assertSame([['root']], $visitedPath);
```

**Explanation:**
- The first callback stops the traversal when the path is `['root']`.
- The second callback is not called because the traversal is stopped.

**Objective:** Skip the children of a node

**Code:**

```php
$config = new Config([
    'root' => [
        'items' => [
            'item1' => [
                'subitem1' => 'value1',
            ],
            'item2' => [
                'subitem2' => 'value2',
            ],
        ],
    ],
]);

$visitedPath = [];
$sut->traverse(
    static function (&$current, $key, ConfigInterface $config, array $path) use (&$firstVisitedPath) {
        $firstVisitedPath[] = $path;
        if ($path === ['root']) {
            return SignalCode::SKIP_CHILDREN;
        }

        return SignalCode::NONE;
    },
    function (&$current, $key, ConfigInterface $config, array $path) use (&$secondVisitedPath) {
        $secondVisitedPath[] = $path;
    },
    function (&$current, $key, ConfigInterface $config, array $path) use (&$thirdVisitedPath) {
        $thirdVisitedPath[] = $path;
    }
);

$this->assertSame([['root']], $firstVisitedPath);
$this->assertSame([['root']], $secondVisitedPath);
$this->assertSame([['root']], $thirdVisitedPath);
```

**Explanation:**
- The first callback skips the children of the `'root'` node.
- The second and third callbacks are called but the children of the `'root'` node are not visited.

---

## Important Notes

- **Parameter Flexibility:** The callback function can accept any number of parameters up to the four provided. If you don't need all parameters, you can define the callback with the ones you need:
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
- **Traversal Direction:** Since parents levels are traversed first (parent-first), modifications to child nodes occur after the parent is processed.
- **Removing Nodes:** To remove a node, return `SignalCode::REMOVE_NODE` or use the `delete` method and return `SignalCode::CONTINUE`.
- **Accessing the ConfigInterface Instance:** The `$config` parameter provides access to the entire configuration and all its available methods. Just remember that the state of the `ConfigInterface` instance is not a mirror of the `$array` under processing.
- **Full Key Path:** The `$path` parameter is an array representing the path from the root to the current node. Use `\implode('.', $path)` to get a string representation, `['root', 'items', 'item1']` will be `'root.items.item1'`.

---

## Conclusion

The `traverse` method is a powerful tool for recursively processing and manipulating your configuration data. By providing a flexible callback mechanism and access to contextual information, it enables complex transformations and data handling with ease.

Remember to consider the traversal order and the implications of modifying the configuration during traversal. With careful use, `traverse` can greatly simplify tasks that involve complex data structures.

