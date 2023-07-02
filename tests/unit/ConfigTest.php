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

class ConfigTest extends TestCase {

	protected function makeInstance($val = [], $default = [] ): Config {
		$sut = new Config($val, $default);
		$this->assertInstanceOf( Config_Interface::class, $sut );
		$this->assertInstanceOf( ConfigInterface::class, $sut );
		return $sut;
	}

	/**
	 * @test
	 */
	public function factory(): void {
		$sut = ConfigFactory::make([]);
		$this->assertInstanceOf( Config::class, $sut );
		$this->assertInstanceOf( Config_Interface::class, $sut );
		$this->assertInstanceOf( ConfigInterface::class, $sut );
	}

	public function valueProvider(): iterable {

		yield 'empty values'	=> [
			false,
			false
		];

		yield 'one value'	=> [
			[],
			false
		];

		yield 'two values'	=> [
			[],
			[]
		];

		yield 'one array'	=> [
			$this->config_arr,
			false
		];

		yield 'two array the second is the default'	=> [
			$this->config_arr,
			$this->default_arr
		];

		yield 'with stdClass'	=> [
			new \stdClass(),
			new \stdClass(),
		];

		yield 'with Iterator'	=> [
			new \ArrayIterator(),
			new \ArrayIterator(),
		];

		yield 'with ArrayObject'	=> [
			new \ArrayObject(),
			new \ArrayObject(),
		];

		yield 'with IteratorIterator'	=> [
			new \IteratorIterator(new \ArrayObject()),
			new \IteratorIterator(new \ArrayObject()),
		];
	}

	/**
	 * @test
	 * @dataProvider valueProvider()
	 */
	public function itShouldBeInstantiatableWith( $value, $default ) {
		$sut = $this->makeInstance( (array) $value, (array) $default );
	}

	/**
	 * @test
	 */
	public function offSetMethods(): void {
		$sut = $this->makeInstance();
		$sut->offsetSet('key', 42);
		$this->assertTrue($sut->offsetExists('key'), '');
		$this->assertSame( $sut->offsetGet('key'), 42, '' );
		$sut->offsetUnset('key');
		$this->assertFalse($sut->offsetExists('key'), '');
	}

	/**
	 * @test
	 */
	public function deprecatedPush(): void {
		$sut = $this->makeInstance();
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
	public function itShouldHaveKey(): void {
		$config = $this->makeInstance( $this->config_arr );

		$this->assertTrue( $config->has( 'tizio' ) );
		$this->assertTrue( $config->has( 'caio' ) );
		$this->assertTrue( $config->has( 'sempronio' ) );

		$this->assertTrue( $config->has( 'object' ) );
		$this->assertTrue( $config->has( 'object.key' ) );
		$this->assertTrue( $config->has( 'object.sub-object.sub-key' ) );

		$this->assertFalse( $config->has( 'cesare' ) );
		$this->assertFalse( $config->has( 'cesarergserg' ) );
	}

	public function keyTypeProvider(): array {
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
	public function itShouldHaveKeyWith( $key, $value ) {
		$config = $this->makeInstance([
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
	 */
	public function itShouldHaveAndGetKey(): void {
		$config = $this->makeInstance( $this->config_arr );
		$this->assertTrue( $config->has( 'sempronio' ) );
		$this->assertIsArray( $config->get( 'recursive' ) );
		$this->assertArrayHasKey( 'subKey', $config->get( 'recursive' ) );
	}

	/**
	 * @test
	 */
	public function itShouldResetDefaultMemberOnEveryCallAndOnlyReturnValueIfExist(): void {
		$config = $this->makeInstance( [ 'key' => 'value' ] );
		$this->assertFalse( $config->has( 'some-key' ) );
		$this->assertFalse( $config->has( 'some-key' ) );
		$this->assertStringContainsString(
			$config->get( 'some-key', 'default value' ),
			'default value',
			''
		);
		$this->assertStringContainsString(
			$config->get( 'some-key.with-subkey', 'default value' ),
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
	 */
	public function itShouldGetKey(): void {
		$config = $this->makeInstance( $this->config_arr );

		$this->assertEquals( [], $config->get( 'tizio' ) );
		$this->assertEquals( [], $config->tizio );



		$this->assertEquals(
			$this->config_arr['object'],
			$config->get( 'object' )
		);

		$this->assertEquals(
			$this->config_arr['object']['key'],
			$config->get( 'object.key' )
		);

		$this->assertEquals(
			$this->config_arr['object']['sub-object']['sub-key'],
			$config->get( 'object.sub-object.sub-key' )
		);
	}

	/**
	 * @test
	 */
	public function itShouldSetKey(): void {
		$config = $this->makeInstance();
		$config->var = 'Value';

		$this->assertEquals( 'Value', $config->get( 'var' ) );
		$this->assertEquals( 'Value', $config->var );
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfKeyDoesNotExists(): void {
		$config = $this->makeInstance( $this->config_arr );

		$this->assertEquals( null, $config->get( 'noKey' ) );
	}

	/**
	 * @test
	 */
	public function itShouldReturnTheGivenValueIfKeyDoesNotExists(): void {
		$config = $this->makeInstance( $this->config_arr );

		$this->assertEquals( true, $config->get( 'noKey', true ) );
	}

	/**
	 * @test
	 */
	public function itShouldReturnAnArray(): void {
		$config = $this->makeInstance( $this->config_arr );

		$this->assertTrue( is_array( $config->all() ) );
		$this->assertEquals( $this->config_arr, $config->all() );
		$this->assertEquals( $config->all(), $config->getArrayCopy() );
	}

	/**
	 * @test
	 */
	public function itShouldAddNewItem(): void {
		$config = $this->makeInstance( $this->config_arr, $this->default_arr );
		$config->add( 'new_item', true );

		$this->assertTrue( $config->get( 'new_item' ) );
	}

	/**
	 * @test
	 */
	public function itShouldReplaceRecursively(): void {
		$config = $this->makeInstance( $this->default_arr );
		$this->assertEquals( $this->default_arr['recursive'], $config->get( 'recursive' ) );

		$config = $this->makeInstance( $this->config_arr, $this->default_arr );
		$this->assertEquals( $this->config_arr['recursive'], $config->get( 'recursive' ) );
		$this->assertEquals( $this->config_arr['recursive'], $config->recursive );
		$this->assertEquals( $this->config_arr['recursive']['subKey'], $config->recursive['subKey'] );

		$this->assertNotEquals( $this->default_arr['recursive'], $config->get( 'recursive' ) );
		$this->assertNotEquals( $this->default_arr['recursive'], $config->recursive );
		$this->assertNotEquals( $this->default_arr['recursive']['subKey'], $config->recursive['subKey'] );
	}

	/**
	 * @test
	 */
	public function itShouldMergeGivenArray(): void {
		$config = $this->makeInstance( $this->config_arr, $this->default_arr );

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
	 */
	public function itShouldMergeGivenGenerator(): void {
		$sut = $this->makeInstance();
		$generator = function (): \Traversable {
			yield 'key' => 'val';
		};

		$sut->merge($generator());
		$this->assertSame('val', $sut->get('key'), '');
	}

	/**
	 * @test
	 */
	public function itShouldBeRemoved(): void {
		$config = $this->makeInstance( $this->config_arr, $this->default_arr );
		$config->remove( 'recursive' );
		$this->assertFalse( $config->has( 'recursive' ) );

		$this->assertTrue( $config->has( 'tizio' ) );
		$this->assertTrue( $config->has( 'caio' ) );

		$config->remove( ['tizio', 'caio'] );

		$this->assertFalse( $config->has( 'tizio' ) );
		$this->assertFalse( $config->has( 'caio' ) );

		$config = $this->makeInstance( $this->config_arr, $this->default_arr );
		$config->remove( ['recursive'] );
		$this->assertFalse( $config->has( 'recursive' ) );

		$config = $this->makeInstance( $this->config_arr, $this->default_arr );
		$this->assertTrue( $config->has( 'recursive' ) );
		$this->assertTrue( $config->has( 'tizio' ) );
		$config->remove( ['recursive'], 'tizio' );
		$this->assertFalse( $config->has( 'recursive' ) );
		$this->assertFalse( $config->has( 'tizio' ) );
	}

	/**
	 * @test
	 */
	public function itShouldSePublicMembers(): void {
		$expected = 42;

		$config = $this->makeInstance();
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
	public function itShouldHasCorrectItemsOnGetArrayCopy(): void {
		$arr1 = [ 'key' => 'Ciao' ];
		$arr2 = [ 'otherKey'	=> 'Ariciao' ];

		$arrMerged = array_replace_recursive( $arr1, $arr2 );


		$config = $this->makeInstance( $arr1 );
		$config->merge( $arr2 );

		$this->assertTrue( $config->getArrayCopy() === $arrMerged );
	}

	/**
	 * @test
	 */
	public function itShouldBeIterable(): void {
		$arr = [ 'key' => 'val' ];
		$config = $this->makeInstance( $arr );

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
	public function itShouldBeCountable(): void {
		$config = $this->makeInstance( $this->config_arr );

		$this->assertCount( count( $this->config_arr ), $config );
	}

	/**
	 * @test
	 */
	public function itShouldMergeConfigObjectInArray(): void {
		$default = $this->makeInstance( [ 'er' => 'sdf' ]);
		$config = $this->makeInstance( $this->config_arr );

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
		$stdobj->obj = $stdobj;

		$anotherConfig = new Config( $stdobj );
		$this->assertArrayHasKey( 'var', $anotherConfig );
		$this->assertEquals( $stdobj->var, $anotherConfig->var );
		$this->assertEquals( $stdobj->var, $anotherConfig->get( 'var' ) );
		$this->assertTrue($anotherConfig->has( 'obj.var' ), '');
		$this->assertEquals( $stdobj->obj->var, $anotherConfig->get( 'obj.var' ) );
	}

	/**
	 * @test
	 */
	public function itShouldReturnArray(): void {
		$config = $this->makeInstance( $this->config_arr );
		$this->assertIsArray( $config->toArray() );
		foreach ( $this->config_arr as $key => $value ) {
			$this->assertArrayHasKey( $key, $config->toArray() );
		}
	}

	/**
	 * @test
	 */
	public function itShouldReturnValidJson(): void {
		$config = $this->makeInstance( $this->config_arr );
		$this->assertJson( $config->toJson() );
		foreach ( $this->config_arr as $key => $value ) {
			$this->assertStringContainsString( $key, $config->toJson() );
		}
		$this->assertEquals( json_encode( $this->config_arr ), $config->toJson() );
	}

	/**
	 * @test
	 */
	public function itShouldSearchSubkeys(): void {
		$arr = [
			'key'	=> [
				'subKey'	=> 'subvalue',
				'subSubKey'	=> [
					'subSubKeyKey'	=> 'subSubValue'
				],
			],
		];

		$config = $this->makeInstance( $arr );

		$this->assertTrue( $config->has( 'key.subKey' ) );
		$this->assertNotTrue( $config->has( 'key.subKeyfgsfg' ) );
		$this->assertTrue( $config->has( 'key.subSubKey.subSubKeyKey' ) );

		$this->assertEquals( $arr['key']['subKey'], $config->get( 'key.subKey' ), '' );
		$this->assertEquals( $arr['key']['subSubKey'], $config->get( 'key.subSubKey' ), '' );
		$this->assertEquals(
			$arr['key']['subSubKey']['subSubKeyKey'],
			$config->get( 'key.subSubKey.subSubKeyKey' ),
			''
		);
		$this->assertEquals( 'subSubValue', $config->get( 'key.subSubKey.subSubKeyKey' ), '' );
	}

	/**
	 * @test
	 */
	public function itShouldCloneHaveEmptyValue(): void {
		$arr = [ 'key'	=> 'value' ];
		$config = $this->makeInstance( $arr );

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
	public function itShouldHaveCallableInCollection(): void {
		$arr = [
			'key'	=> function (): string {
				return 'Ciao';
			},
		];

		$config = $this->makeInstance( $arr );
		$this->assertIsCallable( $config->get( 'key' ) );
		$callable = $config->get( 'key' );
		$this->assertTrue( \is_callable( $callable ));
	}

	/**
	 * @test
	 */
	public function itShouldReceiveIterableAsArgument(): void {
		$iterator = new \ArrayIterator(['test' => 'val1', 'test2' => 'val2']);
		$sut = $this->makeInstance($iterator);
		$this->assertSame('val1', $sut->get('test'), '');

		$array = [
			'test' => 'val1',
			'test2' => [
				'test3' => 'val3',
				'test4' => 'val4',
				'test5' => ['expected'],
			],
		];

		$iterator = new \ArrayIterator($array);
		$sut = $this->makeInstance(['iterator' => $iterator]);
		$this->assertSame(['expected'], $sut->get('iterator.test2.test5'), '');
	}

	/**
	 * @test
	 */
	public function itShouldExchangeArrayWorksAsExpected(): void {
		$array = ['test' => 'val1', 'test2' => 'val2'];
		$sut = $this->makeInstance($array);

		$this->assertCount(2, $sut, '');

		$sut->add('new-key', 'new-value');
		$this->assertCount(3, $sut, '');

		$sut->remove('test', 'test2');
		$this->assertCount(1, $sut, '');

		$sut->merge(['add-key' => 'add-value']);
		$this->assertCount(2, $sut, '');
	}

	/**
	 * @test
	 */
	public function itShouldReturnDefaultIfValFetchedIsNull(): void {
		$array = [
			'test' => null,
			'test2' => [
				'test' => 'val1',
				'test2' => null
			],
		];
		$sut = $this->makeInstance($array);

		$this->assertSame('default-value', $sut->get('', 'default-value'), '');
		$this->assertSame('default-value', $sut->get('test', 'default-value'), '');
		$this->assertSame('default-value', $sut->get('test2.test2', 'default-value'), '');
		$this->assertSame('default-value', $sut->get('test2.test1.sub', 'default-value'), '');
	}

	/**
	 * @test
	 */
	public function itShouldCheckForStdClass(): void {
		$stdclass = new \stdClass();
		$stdclass->test = 'value';
		$array = [
			'test' => null,
		];

		$sut = $this->makeInstance($array);
		$sut->add('test2', $stdclass);

		$this->assertSame('value', $sut->get('test2.test'), '');
	}

	/**
	 * @test
	 */
	public function itShouldBeDelimiterOk(): void {
		$array = [
			1 => 'Numeric Index',
			'test' => [
				'sub-test' => 'Sub string index',
			],
		];

		$sut = $this->makeInstance($array);
		$this->assertSame('Numeric Index', $sut->get(1, 'default-value'), '');
		$this->assertSame('Sub string index', $sut->get('test.sub-test', 'default-value'), '');
		$this->assertSame('default-value', $sut->get('testsub-test', 'default-value'), '');
	}

	/**
	 * @test
	 */
	public function itShouldExchangeStorageWhenCloned(): void {
		$array = [
			1 => 'Numeric Index',
			'test' => [
				'sub-test' => 'Sub string index',
			],
		];

		$sut = $this->makeInstance($array);

		$new_sut = clone $sut;

		$this->assertEmpty($new_sut->__serialize()[1], '');
	}

	/**
	 * @test
	 */
	public function itShouldExchangeStorageForGetIterator(): void {
		$array = [
			1 => 'Numeric Index',
			'test' => [
				'sub-test' => 'Sub string index',
			],
		];

		$sut = $this->makeInstance($array);

		foreach ($sut as $k => $v) {
			$this->assertTrue(true);
		}

		/** @var \ArrayIterator $iterator */
		$iterator = $sut->getIterator();

		while ($iterator->valid()) {
			$iterator->key();
			$iterator->current();
			$iterator->next();
		}

		$new_sut = clone $sut;

		foreach ($new_sut as $k => $v) {
			$this->fail();
		}

		/** @var \ArrayIterator $new_iterator */
		$new_iterator = $new_sut->getIterator();

		while ($new_iterator->valid()) {
			$this->fail();
		}
	}
}
