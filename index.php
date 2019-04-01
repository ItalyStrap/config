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

require( __DIR__ . '/vendor/autoload.php' );

 use \ItalyStrap\Config\Config;
 use \ItalyStrap\Config\Config_Factory;

// $config = Config_Factory::make( [ 'test' => 'value' ], [ 'test' => null ] );
//
// d( $config->test_null );
// ddd( $config->test );

add_action( 'wp_footer', function () {
	$config = Config_Factory::make( [ 'test' => 'value', 'test2' => 'value2' ], [ 'test' => null ] );

//	d( $config->all() );
//
//	d( $config->get( 'prova' ) );
//	d( $config->get( 'test' ) );
//
//	d( $config->has( 'prova' ) );
//	d( $config->has( 'test' ) );
//
//	$default = [
//		'key'	=> 'value',
//		'key1'	=> [
//			'someKey'	=> 'someValue',
//		],
//	];

//	d( $config->merge( $default )->merge( [ 'key1' => [ 'someKey'	=> 'someValuedfgasedga' ] ] ) );
//	d( $config->merge( [ 'key1' => [ 1 ] ] ) );

//	d( $config );
//	d( $config->getArrayCopy() );

//	d( $config->test_null );
// ddd( $config->test );

	function remove ( ...$args ) {
		d($args);
	}


	$arr = [3,4];

	echo (...$arr);

	d( ...$arr );

} );
