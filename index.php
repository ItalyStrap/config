<?php
/*
Plugin Name: Config
Description: Description
Plugin URI: http://#
Author: Author
Author URI: http://#
Version: 1.0
License: GPL2
Text Domain: Text Domain
Domain Path: Domain Path
*/

/*

	Copyright (C) Year  Author  Email

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

declare(strict_types=1);

require( __DIR__ . '/vendor/autoload.php' );

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigFactory;

if (!function_exists('add_action')) {
	function add_action($vent_name, $callable, $priority = 10, $num_args = 3) {
		$callable();
	}
}

add_action( 'wp_footer', static function () {
	$config = ConfigFactory::make( [ 'test' => 'value', 'test2' => 'value2' ], [ 'test' => null ] );
	var_dump('VAR_DUMP');
	var_dump($config->all());

	var_dump( $config->get( 'key-does-not-exists' ) );
	var_dump( $config->get( 'test' ) );

	var_dump( $config->has( 'key-does-not-exists' ) );
	var_dump( $config->has( 'test' ) );


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

	var_dump( $config->get( 'key1.subKey' ) );
	var_dump( $config->get( 'key1' ) );
	var_dump( $config->get( 'key2' ) );
	var_dump( $config->get( 'key1.subKey' ) );
	var_dump( $config->get( 'key1.nested.subSubKey' ) );
	var_dump( $config->get( 'key1.nested.subSubKeyfdgd' ) );

	var_dump( $config->merge( [ 'key1' => [ 1 ] ] ) );

	var_dump( $config['key1']['subKe'] ?? '' );
	var_dump( $config->key1['subKey'] ?? '' );
	var_dump( $config->key1->subKey ?? '' );
	var_dump( $config->getArrayCopy() );

	var_dump( $config->get('test_null') );
	var_dump( $config->get('test') );
	var_dump( $config->get('undefined') );

	var_dump( PHP_EOL );
	var_dump( PHP_EOL );
	$config = ConfigFactory::make();
	$generator = function (): \Traversable {
		yield 'key' => 'val';
	};
	$config->merge($generator());
	var_dump($config->get('key'));
} );
