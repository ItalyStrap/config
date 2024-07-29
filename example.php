<?php

declare(strict_types=1);

require( __DIR__ . '/vendor/autoload.php' );

use ItalyStrap\Config\ConfigFactory;

$config = ConfigFactory::make([ 'test' => 'value', 'test2' => 'value2' ], [ 'test' => null ]);
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

\print_r($config->get( 'key1.subKey' ));
\print_r($config->get( 'key1' ));
\print_r($config->get( 'key2' ));
\print_r($config->get( 'key1.subKey' ));
\print_r($config->get( 'key1.nested.subSubKey' ));
\print_r($config->get( 'key1.nested.subSubKeyfdgd' ));

\print_r($config->merge( [ 'key1' => [ 1 ] ] ));

\print_r($config['key1']['subKe'] ?? '' );
\print_r($config->key1['subKey'] ?? '' );
\print_r($config->key1->subKey ?? '' );
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
