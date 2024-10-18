# Documentation for the Config Class

This documentation provides a detailed overview of the Config class, which is designed to manage configuration settings in your application. The Config class allows you to access, set, update, delete, and check the existence of configuration values using both dot notation and array notation.

---

## **Table of Contents**

- [Overview](#overview)
- [Class Features](#class-features)
- [Creating a Config Instance](#creating-a-config-instance)
- [Accessing Values](#accessing-values)
    - [Using Dot Notation](#using-dot-notation)
    - [Using Array Notation](#using-array-notation)
- [Setting Values](#setting-values)
- [Updating Values](#updating-values)
- [Deleting Values](#deleting-values)
- [Checking for Existence](#checking-for-existence)
- [Working with Multiple Values](#working-with-multiple-values)
- [Merging Configuration Data](#merging-configuration-data)
- [Converting to Array or JSON](#converting-to-array-or-json)
- [Iterating Over Configuration Data](#iterating-over-configuration-data)
- [Countable and Array Access](#countable-and-array-access)
- [Examples](#examples)
- [Conclusion](#conclusion)

---

## **Overview**

The Config class is designed to simplify the management of configuration settings in your application. It provides a flexible way to work with configuration data, supporting:

- Accessing values using **dot notation** (e.g., `'database.host'`) or **array notation** (e.g., `['database', 'host']`).
- Modifying configuration data at runtime.
- Checking the existence of keys.
- Working with nested configuration structures.

---

## **Class Features**

- **Extends**: `ArrayObject`
- **Implements**:
    - `ArrayAccess`
    - `IteratorAggregate`
    - `Countable`
    - `JsonSerializable`

By extending `ArrayObject` and implementing these interfaces, the Config class provides seamless integration with PHP's array and object handling mechanisms.

---

## **Creating a Config Instance**

You can create a new Config instance by passing an array of configuration data:

```php
use \ItalyStrap\Config\Config;

$config = new Config([
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'credentials' => [
            'username' => 'root',
            'password' => 'secret',
        ],
    ],
    'app' => [
        'debug' => true,
    ],
]);
```

---

## **Accessing Values**

### **Using Dot Notation**

You can access nested configuration values using dot notation:

```php
$host = $config->get('database.host');

// Output: 'localhost'
```

If the key does not exist, you can provide a default value:

```php
$charset = $config->get('database.charset', 'utf8mb4');

// Output: 'utf8mb4'
```

### **Using Array Notation**

Alternatively, you can access values using array notation:

```php
$host = $config->get(['database', 'host']);

// Output: 'localhost'
```

---

## **Setting Values**

You can set configuration values using the `set` method with dot notation or array notation:

```php
$config->set('database.host', '127.0.0.1');
// or
$config->set(['database', 'host'], '127.0.0.1');

$host = $config->get('database.host');

// Output: '127.0.0.1'
```

---

## **Updating Values**

The `update` method works similarly to `set`. It sets a value at the specified key:

```php
$config->update('app.debug', false);

$debug = $config->get('app.debug');

// Output: false
```

---

## **Deleting Values**

You can delete configuration values using the `delete` method:

```php
$config->delete('database.credentials.password');
// or
$config->delete(['database', 'credentials', 'password']);

$password = $config->get('database.credentials.password');

// Output: null
```

---

## **Checking for Existence**

To check if a configuration key exists, use the `has` method:

```php
$exists = $config->has('database.credentials.username');

// Output: true
```

```php
$exists = $config->has('database.credentials.password');

// Output: false (since we deleted it earlier)
```

---

## **Working with Multiple Values**

The Config class provides methods to work with multiple keys at once.

### **Getting Multiple Values**

Use `getMultiple` to retrieve multiple values:

```php
/** @var mixed[] $values */
$values = $config->getMultiple(['database.host', 'app.debug'], 'default');

// Output:
// [
//     'database.host' => '127.0.0.1',
//     'app.debug' => false,
// ]
```

### **Setting Multiple Values**

Use `setMultiple` to set multiple values:

```php
/** @var bool $isSet */
$isSet = $config->setMultiple([
    'database.port' => 3307,
    'app.env' => 'production',
]);

$port = $config->get('database.port');
$env = $config->get('app.env');

// Output:
// $port = 3307
// $env = 'production'
```

### **Deleting Multiple Values**

Use `deleteMultiple` to delete multiple values:

```php
/** @var bool $isDeleted */
$isDeleted = $config->deleteMultiple(['database.port', 'app.env']);

$portExists = $config->has('database.port');
$envExists = $config->has('app.env');

// Output:
// $portExists = false
// $envExists = false
```

---

## **Merging Configuration Data**

You can merge additional configuration data into the existing Config instance:

```php
$config->merge([
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
]);

$cacheEnabled = $config->get('cache.enabled');

// Output: true
```

---

## **Converting to Array or JSON**

### **Converting to Array**

You can get all configuration data as an array:

```php
$allConfig = $config->toArray();

// Output:
// [
//     'database' => [...],
//     'app' => [...],
//     'cache' => [...],
// ]
```

### **Converting to JSON**

Since the Config class implements `JsonSerializable`, you can convert it to JSON:

```php
$jsonConfig = \json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Output: JSON representation of the configuration data
```

---

## **Iterating Over Configuration Data**

Because Config implements `IteratorAggregate`, you can iterate over it using a foreach loop:

```php
foreach ($config as $key => $value) {
// Process $key and $value
}
```

Note: Iterating directly over the Config instance will iterate over the top-level keys.

You can use the `traverse` method to traverse nested configuration data, see the [Traversing Data](02_traversing-data.md) documentation for more information.

---

## **Countable and Array Access**

### **Counting Configuration Entries**

Since Config implements `Countable`, you can count the number of top-level configuration entries:

```php
$count = count($config);

// Output: Number of top-level keys in the configuration
```

### **Array Access**

You can access top-level configuration entries using array access:

```php
$databaseConfig = $config['database'];

$host = $databaseConfig['host'];

// Output: '127.0.0.1'
```

You can access sublevel configuration entries as well, this is a little bit tricky, but you can also access nested using dot notation (here array notation does not work):

```php
$host = $config['database']['host'];
// or
$host = $config['database.host'];

// Output: '127.0.0.1'
```

You can see if a key exists:

```php
$exists = isset($config['database.host']);
// or
$exists = isset($config['database']['host']);

// Output: true
```

You can also set top-level configuration entries:

```php
$config['new_setting'] = 'value';

$value = $config['new_setting'];

// Output: 'value'
```

Or with dot notation:

```php
$config['new_setting.sub_setting'] = 'value';

$value = $config['new_setting.sub_setting'];

// Output: 'value'
```

But this will not work:

```php
$config['new_setting']['sub_setting'] = 'value';

// Indirect modification of overloaded element of ItalyStrap\Config\Config has no effect
```

The reason is that the `ArrayObject` class does not allow you to set nested values using array notation, this was meant to be used with flat arrays and not nested arrays.

---

## **Examples**

### **Example 1: Working with Nested Configuration**

```php
$config = new Config([
    'services' => [
        'mail' => [
            'driver' => 'smtp',
            'host' => 'smtp.example.com',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'user@example.com',
            'password' => 'password',
        ],
    ],
]);

// Accessing values
$mailDriver = $config->get('services.mail.driver');

// Setting values
$config->set('services.mail.password', 'newpassword');

// Deleting values
$config->delete('services.mail.encryption');

// Checking existence
$hasEncryption = $config->has('services.mail.encryption');

// Output:
// $mailDriver = 'smtp'
// $hasEncryption = false
```

### **Example 2: Using Default Values**

```php
$timezone = $config->get('app.timezone', 'UTC');

// Output: 'UTC' (if 'app.timezone' is not set)
```

### **Example 3: Modifying Configuration at Runtime**

```php
if ($config->get('app.debug')) {
    \error_reporting(E_ALL);
} else {
    \error_reporting(0);
}
```

---

## **Conclusion**

The Config class provides a powerful and flexible way to manage configuration settings in your application. By supporting both dot notation and array notation, and by implementing standard PHP interfaces, it integrates smoothly with your codebase and offers a rich set of features for accessing and modifying configuration data.

---
