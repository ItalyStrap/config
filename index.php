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

use ItalyStrap\Config\{Config, ConfigFactory};

// $config = Config_Factory::make( [ 'test' => 'value' ], [ 'test' => null ] );
//
// d( $config->test_null );
// ddd( $config->test );

add_action( 'wp_footer', function () {
	$config = ConfigFactory::make( [ 'test' => 'value', 'test2' => 'value2' ], [ 'test' => null ] );

//	d( $config->all() );
//
//	d( $config->get( 'prova' ) );
//	d( $config->get( 'test' ) );
//
//	d( $config->has( 'prova' ) );
//	d( $config->has( 'test' ) );
//
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
		] );

//	d( $config->get( 'key1.subKey' ) );
//	d( $config->search( 'key1' ) );
//	d( $config->search( 'key2' ) );
//	d( $config->search( 'key1.subKey' ) );
//	d( $config->search( 'key1.nested.subSubKey' ) );
//	d( $config->search( 'key1.nested.subSubKeyfdgd' ) );

//	d( $config->merge( [ 'key1' => [ 1 ] ] ) );

//	d( $config['key1']['subKe'] ?? '' );
//	d( $config->key1['subKey'] ?? '' );
//	d( $config->key1->subKey ?? '' );
//	d( $config->getArrayCopy() );

//	d( $config->test_null );
// ddd( $config->test );
//	d( $config->undefined );

//	function bridge( &$check ){
//		return $check ?? null;
//	}
//
//	d( bridge( $config->undefined ) );
} );
