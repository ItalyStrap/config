<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\Test\Unit;
use ItalyStrap\Config\ConfigInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use UnitTester;

abstract class BaseConfig extends Unit {

	use ProphecyTrait;

	/**
	 * @var UnitTester
	 */
	protected $tester;
	protected $config_file_name;
	protected $default_file_name;
	protected $empty_file_name;

	protected $config_arr;
	protected $default_arr;
	protected $empty_arr;

	// phpcs:ignore
	protected function _before() {
		$this->config_file_name = __DIR__ . '/../_data/config/config.php';
		$this->default_file_name = __DIR__ . '/../_data/config/default.php';
		$this->empty_file_name = __DIR__ . '/../_data/config/empty.php';

		$this->config_arr = require( $this->config_file_name );
		$this->default_arr = require( $this->default_file_name );
		$this->empty_arr = require( $this->empty_file_name );
	}

	// phpcs:ignore
	protected function _after() {
	}

	/**
	 * @test
	 */
	public function testFileExists() {
		$this->assertFileExists( $this->config_file_name );
		$this->assertFileExists( $this->default_file_name );
		$this->assertFileExists( $this->empty_file_name );
	}

	/**
	 * @param array $val
	 * @param array $default
	 * @return ConfigInterface
	 */
	abstract protected function getInstance( $val = [], $default = [] ): ConfigInterface;

	/**
	 * @test
	 */
	public function instanceOk() {
		$sut = $this->getInstance();
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
		$sut = $this->getInstance( (array) $value, (array) $default );
	}

	/**
	 * @test
	 * it should have key
	 */
//	public function it_should_have_key()
//	{
//
//		$config = $this->getInstance( $this->config_arr );
//
//		$this->assertTrue( $config->has( 'tizio' ) );
//		$this->assertTrue( $config->has( 'caio' ) );
//		$this->assertTrue( $config->has( 'sempronio' ) );
//
//		$this->assertFalse( $config->has( 'cesare' ) );
//		$this->assertFalse( $config->has( 'cesarergserg' ) );
//	}
}
