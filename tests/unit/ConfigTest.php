<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use ArrayIterator;
use \ItalyStrap\Config\Config;
use ItalyStrap\Config\Config_Interface;
use ItalyStrap\Config\ConfigFactory;
use ItalyStrap\Config\ConfigInterface;
use stdClass;
use function array_replace_recursive;
use function is_array;
use function json_encode;
// phpcs:ignoreFile
require_once 'BaseConfig.php';

class ConfigTest extends BaseConfig {

	protected function _before() {
		parent::_before();
	}

	protected function _after() {
		parent::_after();
	}

	/**
	 * @inheritDoc
	 */
	protected function getInstance( $val = [], $default = [] ): ConfigInterface {
		$sut = new Config($val, $default);
		$this->assertInstanceOf( Config::class, $sut );
		$this->assertInstanceOf( Config_Interface::class, $sut );
		$this->assertInstanceOf( ConfigInterface::class, $sut );
		return $sut;
	}

	/**
	 * @test
	 */
	public function factory() {
		$sut = ConfigFactory::make([]);
		$this->assertInstanceOf( Config::class, $sut );
		$this->assertInstanceOf( Config_Interface::class, $sut );
		$this->assertInstanceOf( ConfigInterface::class, $sut );
	}

	/**
	 * @test
	 */
	public function offSetMethods() {
		$sut = $this->getInstance();
		$sut->offsetSet('key', 42);
		$this->assertTrue($sut->offsetExists('key'), '');
		$this->assertSame( $sut->offsetGet('key'), 42, '' );
		$sut->offsetUnset('key');
		$this->assertFalse($sut->offsetExists('key'), '');
	}

	/**
	 * @test
	 */
	public function deprecatedPush() {
		$sut = $this->getInstance();
		$sut->push('key', 42);
		$this->assertTrue($sut->has('key'), '');
		$this->assertSame( $sut->get('key'), 42, '' );
		$sut->remove('key');
		$this->assertFalse($sut->has('key'), '');
	}

	/**
	 * @test
	 * it should have key
	 */
	public function it_should_have_key() {

		$config = $this->getInstance( $this->config_arr );

		$this->assertTrue( $config->has( 'tizio' ) );
		$this->assertTrue( $config->has( 'caio' ) );
		$this->assertTrue( $config->has( 'sempronio' ) );

		$this->assertFalse( $config->has( 'cesare' ) );
		$this->assertFalse( $config->has( 'cesarergserg' ) );
	}

	public function keyTypeProvider() {
		return [
			'int'	=> [
				1, "a"
			],
			'string int'	=> [
				"1", "b"
			],
//			'float'	=> [
//				1.5,"c"
//			],
//			'bool'	=> [
//				true,"d"
//			],
		];
	}

	/**
	 * @test
	 * @dataProvider keyTypeProvider()
	 */
	public function it_should_have_key_with( $key, $value ) {

		$config = $this->getInstance([
			$key	=> $value
		]);

		$this->assertTrue( $config->has( $key ) );

		$expected = $value;
		/** @var string $actual */
		$actual = $config->get($key);

		$this->assertStringMatchesFormat($expected, $actual, '');
	}

	/**
	 * @test
	 * it should have key
	 */
	public function it_should_have_and_get_key() {
		$config = $this->getInstance( $this->config_arr );
		$this->assertTrue( $config->has( 'sempronio' ) );
		$this->assertIsArray( $config->get( 'recursive' ) );
		$this->assertArrayHasKey( 'subKey', $config->get( 'recursive' ) );
	}

	/**
	 * @test
	 * it should have key
	 */
	public function it_should_reset_default_member_on_every_call_and_only_return_value_if_exist() {
		$config = $this->getInstance( [ 'key' => 'value' ] );
		$this->assertFalse( $config->has( 'some-key' ) );
		$this->assertFalse( $config->has( 'some-key' ) );
		$this->assertStringContainsString(
			strval( $config->get( 'some-key', 'default value' ) ),
			'default value',
			''
		);
		$this->assertFalse( $config->has( 'some-key' ) );
		$this->assertStringContainsString(
			strval( $config->get( 'some-key', 'other default value' ) ),
			'other default value',
			''
		);

		$this->assertFalse( $config->has( 'some-key' ) );
		$this->assertEmpty( $config->get( 'some-key' ), '' );

		$this->assertStringContainsString(
			strval( $config->get( 'some-key', 'other default value' ) ),
			'other default value',
			''
		);
		$this->assertEmpty( $config->get( 'some-key' ), '' );
		$this->assertFalse( $config->has( 'some-key' ) );
	}

	/**
	 * @test
	 * it should get_key
	 */
	public function it_should_get_key() {

		$config = $this->getInstance( $this->config_arr );

		$this->assertEquals( [], $config->get( 'tizio' ) );
		$this->assertEquals( [], $config->tizio );
	}

	/**
	 * @test
	 * it should get_key
	 */
	public function it_should_set_key() {
		$config = $this->getInstance();
		$config->var = 'Value';

		$this->assertEquals( 'Value', $config->get( 'var' ) );
		$this->assertEquals( 'Value', $config->var );
	}

	/**
	 * @test
	 * it should return_null_if_key_does_not_exists
	 */
	public function it_should_return_null_if_key_does_not_exists() {

		$config = $this->getInstance( $this->config_arr );

		$this->assertEquals( null, $config->get( 'noKey' ) );
	}

	/**
	 * @test
	 * it should return_the_given_value_if_key_does_not_exists
	 */
	public function it_should_return_the_given_value_if_key_does_not_exists() {

		$config = $this->getInstance( $this->config_arr );

		$this->assertEquals( true, $config->get( 'noKey', true ) );
	}

	/**
	 * @test
	 * it should return_an_array
	 */
	public function it_should_return_an_array() {

		$config = $this->getInstance( $this->config_arr );

		$this->assertTrue( is_array( $config->all() ) );
		$this->assertEquals( $this->config_arr, $config->all() );
		$this->assertEquals( $config->all(), $config->getArrayCopy() );
	}

	/**
	 * @test
	 * it should add_new_item
	 */
	public function it_should_add_new_item() {

		$config = $this->getInstance( $this->config_arr, $this->default_arr );
		$config->add( 'new_item', true );

		$this->assertTrue( $config->get( 'new_item' ) );
	}

	/**
	 * @test
	 * it should replace_recursively
	 */
	public function it_should_replace_recursively() {

		$config = $this->getInstance( $this->default_arr );
		$this->assertEquals( $this->default_arr['recursive'], $config->get( 'recursive' ) );

		$config = $this->getInstance( $this->config_arr, $this->default_arr );
		$this->assertEquals( $this->config_arr['recursive'], $config->get( 'recursive' ) );
		$this->assertEquals( $this->config_arr['recursive'], $config->recursive );
		$this->assertEquals( $this->config_arr['recursive']['subKey'], $config->recursive['subKey'] );

		$this->assertNotEquals( $this->default_arr['recursive'], $config->get( 'recursive' ) );
		$this->assertNotEquals( $this->default_arr['recursive'], $config->recursive );
		$this->assertNotEquals( $this->default_arr['recursive']['subKey'], $config->recursive['subKey'] );
	}

	/**
	 * @test
	 * it should merge_given_array
	 */
	public function it_should_merge_given_array() {

		$config = $this->getInstance( $this->config_arr, $this->default_arr );

		$new_array = [
			'new_key'   => 'New Value',
			'recursive' => [
				'subKey'	=> 'otherSubValue',
			],
		];

		$config->merge( $new_array );

		$this->assertEquals( 'New Value', $config->get( 'new_key' ) );

		$this->assertEquals( $new_array['recursive'], $config->get( 'recursive' ) );
		$this->assertEquals( $new_array['recursive'], $config['recursive'] );
		$this->assertEquals( $new_array['recursive'], $config->recursive );
		$this->assertEquals( $new_array['recursive']['subKey'], $config->recursive['subKey'] );

		$config->merge( $new_array, [ 'new_key'   => 'Value changed' ], [ 'new_key'   => 'Value changed2' ] );
		$this->assertEquals( 'Value changed2', $config->get( 'new_key' ) );

		$config->merge( 'Ciao' );
		$this->assertEquals( 'Ciao', $config->get( '0' ) );
	}

	/**
	 * @test
	 * it_should_be_removed
	 */
	public function it_should_be_removed() {

		$config = $this->getInstance( $this->config_arr, $this->default_arr );
		$config->remove( 'recursive' );
		$this->assertFalse( $config->has( 'recursive' ) );

		$this->assertTrue( $config->has( 'tizio' ) );
		$this->assertTrue( $config->has( 'caio' ) );

		$config->remove( ['tizio', 'caio'] );

		$this->assertFalse( $config->has( 'tizio' ) );
		$this->assertFalse( $config->has( 'caio' ) );

		$config = $this->getInstance( $this->config_arr, $this->default_arr );
		$config->remove( ['recursive'] );
		$this->assertFalse( $config->has( 'recursive' ) );

		$config = $this->getInstance( $this->config_arr, $this->default_arr );
		$this->assertTrue( $config->has( 'recursive' ) );
		$this->assertTrue( $config->has( 'tizio' ) );
		$config->remove( ['recursive'], 'tizio' );
		$this->assertFalse( $config->has( 'recursive' ) );
		$this->assertFalse( $config->has( 'tizio' ) );
	}

	/**
	 * @test
	 * it_should_be
	 */
	public function it_should_set_public_members() {
		$expected = 42;

		$config = $this->getInstance();
		$config->test = $expected;
		$this->assertTrue( $config->has( 'test' ) );
		$this->assertNotEmpty( $config->test );

		$this->assertNotTrue( $config->has( 'some' ) );
		$this->assertEmpty( $config->some );
		$this->assertEquals( $expected, $config->get( 'test' ) );

		$config[2] = 'value';
		$this->assertTrue( $config->has('2') );

		$config->add( '0', $expected );
		$this->assertEquals( $expected, $config->get( '0' ) );
	}

	/**
	 * @test
	 */
	public function it_should_has_correct_items_on_get_Array_Copy() {
		$arr1 = [ 'key' => 'Ciao' ];
		$arr2 = [ 'otherKey'	=> 'Ariciao' ];

		$arrMerged = array_replace_recursive( $arr1, $arr2 );


		$config = $this->getInstance( $arr1 );
		$config->merge( $arr2 );

		$this->assertTrue( $config->getArrayCopy() === $arrMerged );
	}

	/**
	 * @test
	 */
	public function it_should_be_iterable() {
		$arr = [ 'key' => 'val' ];
		$config = $this->getInstance( $arr );

		foreach ( $config as $key => $value ) {
			$this->assertTrue( $arr[ $key ] === $value );
		}

		foreach ( $config as $key => $value ) {
			$this->assertTrue( $config->$key === $value );
		}
	}

	/**
	 * @test
	 */
	public function it_should_be_countable() {

		$config = $this->getInstance( $this->config_arr );

		$this->assertCount( count( $this->config_arr ), $config );
	}

	/**
	 * @test
	 */
	public function it_should_merge_config_object_in_array() {

		$default = $this->getInstance( [ 'er' => 'sdf' ]);
		$config = $this->getInstance( $this->config_arr );

		$config->merge( $default );
		$this->assertArrayHasKey( 'er', $config->all() );

		$newconfig = new Config( $default );
		$this->assertArrayHasKey( 'er', $newconfig->all() );

		$iterator = new ArrayIterator( ['recipe'=>'pancakes', 'egg', 'milk', 'flour'] );
		$newconfig = new Config( $iterator );
		$this->assertArrayHasKey( 'recipe', $newconfig->all() );
		$this->assertArrayHasKey( 'recipe', $newconfig );

		$iterator = new ArrayIterator( ['recipe2'=>'pancakes', 'egg', 'milk', 'flour'] );
		$newconfig->merge( $iterator );
		$this->assertArrayHasKey( 'recipe2', $newconfig->all() );
		$this->assertArrayHasKey( 'recipe2', $newconfig );

		$stdobj = new stdClass();
		$stdobj->var = 'Value';

		$anotherConfig = new Config( $stdobj );
		$this->assertArrayHasKey( 'var', $anotherConfig );
		$this->assertEquals( $stdobj->var, $anotherConfig->var );

		$node = new class {
			public $property;

			public function myMethod($arg = '') {
				return 'Tizio';
			}
		};

		$anotherConfig->merge( $node );

		/**
		 * @todo https://www.php.net/manual/en/class.arrayobject.php#118872
		 * Add callable method with injecting other instances
		 */
//		codecept_debug( $node->myMethod() );
////		codecept_debug( $anotherConfig->myMethod() );
//		codecept_debug( $method = $anotherConfig->myMethod );
//		codecept_debug( $method() );
//		codecept_debug( $anotherConfig->property );

		/**
		 * @todo https://www.php.net/manual/en/class.arrayobject.php#123572
		 * Maybe add recursion?
		 */
	}

	/**
	 * @test
	 */
	public function it_shoud_return_array() {
		$config = $this->getInstance( $this->config_arr );
		$this->assertIsArray( $config->toArray() );
		foreach ( $this->config_arr as $key => $value ) {
			$this->assertArrayHasKey( $key, $config->toArray() );
		}
	}

	/**
	 * @test
	 */
	public function it_shoud_return_valid_json() {
		$config = $this->getInstance( $this->config_arr );
		$this->assertJson( $config->toJson() );
		foreach ( $this->config_arr as $key => $value ) {
			$this->assertStringContainsString( $key, $config->toJson() );
		}
		$this->assertEquals( json_encode( $this->config_arr ), $config->toJson() );
	}

	/**
	 * @test
	 */
//	public function it_shoud_call_builtin_array_functions() {
//		$keys = \array_keys( $this->config_arr );
//
//		$config = new Config( $this->config_arr );
//		$this->assertEquals( $keys, $config->array_keys() );
//    }

	/**
	 * @test
	 */
	public function it_shoud_search_subkeys() {

		$arr = [
			'key'	=> [
				'subKey'	=> 'subvalue',
				'subSubKey'	=> [
					'subSubKeyKey'	=> 'subSubValue'
				],
			],
		];

		$config = $this->getInstance( $arr );

		$this->assertTrue( $config->has( 'key.subKey' ) );
		$this->assertNotTrue( $config->has( 'key.subKeyfgsfg' ) );
		$this->assertTrue( $config->has( 'key.subSubKey.subSubKeyKey' ) );

		$this->assertEquals( $arr['key']['subKey'], $config->get( 'key.subKey' ), '' );
		$this->assertEquals( $arr['key']['subSubKey'], $config->get( 'key.subSubKey' ), '' );
		$this->assertEquals( $arr['key']['subSubKey']['subSubKeyKey'], $config->get( 'key.subSubKey.subSubKeyKey' ), '' );
		$this->assertEquals( 'subSubValue', $config->get( 'key.subSubKey.subSubKeyKey' ), '' );
	}

	/**
	 * @test
	 */
	public function it_should_clone_have_empty_value() {
		$arr = [ 'key'	=> 'value' ];
		$config = $this->getInstance( $arr );

		$this->assertStringContainsString( strval( $config->get( 'key' ) ), $arr['key'], '' );
		$this->assertTrue( $config->has( 'key' ), '' );
		$this->assertNotEmpty( $config->get( 'key' ), '' );

		$clone = clone $config;

		$this->assertFalse( $clone->has( 'key' ), '' );
		$this->assertEmpty( $clone->get( 'key' ), '' );

		$this->assertNotSame( $config, $clone, '' );
	}

	/**
	 * @test
	 */
	public function it_shoud_have_callable_in_collection() {

		$arr = [
			'key'	=> function () {
				return 'Ciao';
			},
		];

		$config = $this->getInstance( $arr );
		$this->assertIsCallable( $config->get( 'key' ) );
		$callable = $config->get( 'key' );
		$this->assertStringContainsString( 'Ciao', $callable() );
	}

	/**
	 * @test
	 */
//	public function it_shoud_execute_callable_from_collection() {
//
//		$arr = [
//			'key'	=> function ( $arg ) {
//				return $arg;
//			},
//		];
//
//		$config = new Config( $arr );
//		$this->assertIsCallable( $config->get( 'key' ) );
//
//		codecept_debug( $config->key( 'ciao' ) );
//
//
////		$callable = $config->get( 'key' );
////		$this->assertStringContainsString( 'Ciao', $callable() );
//	}
}
