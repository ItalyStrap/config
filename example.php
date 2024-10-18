<?php

declare(strict_types=1);

require( __DIR__ . '/vendor/autoload.php' );

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigFactory;
use ItalyStrap\Config\ConfigInterface;

$config = (new ConfigFactory)->make([ 'test' => 'value', 'test2' => 'value2' ], [ 'test' => null ]);
\print_r($config);

\print_r($config->get( 'key-does-not-exists' ));
\print_r($config->get( 'test' ));

\print_r($config->has( 'key-does-not-exists' ));
\print_r($config->has( 'test' ));


$default = [
	'key'	=> 'value',
	'key1'	=> [
		'subKey'	=> 'someValue',
	],
];

$config->merge( $default )->merge(
	[
		'key1' => [
			'subKey'	=> 'someValuedfgasedga',
			'nested'	=> [
				'subSubKey'	=> 'nestedValue'
			]
		]
	]
);

\print_r($config->get('key1.subKey'));
\print_r($config->get(['key1', 'subKey']));
\print_r($config->get('key1'));
\print_r($config->get('key2'));
\print_r($config->get('key1.subKey'));
\print_r($config->get('key1.nested.subSubKey'));
\print_r($config->get(['key1', 'nested', 'subSubKey']));
\print_r($config->get('key1.nested.subSubKeyfdgd'));

\print_r($config->merge(['key1' => [1]]));

\print_r($config['key1']['subKe'] ?? '');
\print_r($config->key1['subKey'] ?? '');
\print_r($config->key1->subKey ?? '');
\print_r($config->getArrayCopy());

\print_r($config->get('test_null'));
\print_r($config->get('test'));
\print_r($config->get('undefined'));

\print_r( PHP_EOL );
\print_r( PHP_EOL );
$config = ConfigFactory::make();
$generator = function (): \Traversable {
	yield 'key' => 'val';
};
$config->merge($generator());
\print_r($config->get('key'));

/**
 * Traverse the config
 */

// Modifying Numeric Values
$config = new Config([
	'numbers' => [1, 2, 3, 4, 5],
]);

$config->traverse(static function (&$current): void {
	if (\is_numeric($current) && $current % 2 === 0) {
		$current *= 10;
	}
});

// Resulting configuration:
// 'numbers' => [1, 20, 3, 40, 5]
\print_r($config->toArray());

// Modifying Elements Based on a Condition

$config = new Config([
	'items' => [
		['name' => 'Item 1', 'price' => 100],
		['name' => 'Item 2', 'price' => 200],
		['name' => 'Item 3', 'price' => 300],
	],
]);

$config->traverse(static function (&$current, $key, ConfigInterface $config, array $keyPath): void {
	if (\is_array($current) && \array_key_exists('price', $current) && $current['price'] > 200) {
		$current['price'] = 250;
	}

	// Or
	if (\is_array($current) && \array_key_exists('price', $current) && $current['price'] > 200) {
		$config->set($keyPath, ['name' => $current['name'], 'price' => 250]);
	}
});

// Resulting configuration:
// 'items' => [
//     ['name' => 'Item 1', 'price' => 100],
//     ['name' => 'Item 2', 'price' => 200],
//     ['name' => 'Item 3', 'price' => 250],
// ]
\print_r($config->toArray());

// Removing Elements Based on a Condition
$config = new Config([
	'items' => [
		['name' => 'Item 1', 'remove' => false],
		['name' => 'Item 2', 'remove' => true],
		['name' => 'Item 3', 'remove' => false],
	],
]);

$config->traverse(static function (&$current, $key, ConfigInterface $config, array $keyPath): void {
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
\print_r($config->toArray());
