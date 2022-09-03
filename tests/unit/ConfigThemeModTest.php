<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\Config\ConfigThemeMods;
use ItalyStrap\Event\EventDispatcherInterface;

class ConfigThemeModTest extends BaseConfig {

	protected \Prophecy\Prophecy\ObjectProphecy $config;
	protected \Prophecy\Prophecy\ObjectProphecy $dispatcher;

	public function getDispatcher(): EventDispatcherInterface {
		return $this->dispatcher->reveal();
	}

	public function getConfig( $val = [], $default = [] ): ConfigInterface {
		$this->config->willBeConstructedWith(
			[
				$val,
				$default
			]
		);

		return $this->config->reveal();
	}

	// phpcs:ignore
	protected function _before() {
		$this->config = $this->prophesize( ConfigInterface::class );
		$this->dispatcher = $this->prophesize( EventDispatcherInterface::class );
		parent::_before();
	}

	// phpcs:ignore
	protected function _after() {
		parent::_after();
	}

	protected function getInstance( $val = [], $default = [] ): ConfigInterface {
		$sut = new ConfigThemeMods( $this->getConfig(...\func_get_args()), $this->getDispatcher() );
		$this->assertInstanceOf( ConfigInterface::class, $sut, '' );
		return $sut;
	}

	/**
	 * @test
	 */
	public function getAndAddOk() {
		$this->dispatcher->filter('theme_mod_key', null )->willReturn('value');

		// phpcs:ignore
		\tad\FunctionMockerLe\define('set_theme_mod', function ( string $parameter_key, string $value ) {
			$this->assertStringContainsString('key', $parameter_key, '');
			$this->assertStringContainsString('value', $value, '');
		});

		$sut = $this->getInstance();
		$sut->add( 'key', 'value' );

		$this->assertSame('value', $sut->get('key'), '');
	}

	/**
	 * @test
	 */
	public function removeOk() {
		$collection = [
			'key'	=> 'val',
			'key2'	=> 'val2',
		];

		// phpcs:ignore
		\tad\FunctionMockerLe\define('remove_theme_mod', function ( $key ) use ( &$collection ) {
			unset( $collection[ $key ] );
		});

		$sut = $this->getInstance();
		$sut->remove( 'key', 'key2' );

		$this->assertEmpty( $collection, '' );
	}

	/**
	 * @test
	 */
	public function mergeOk() {

		$collection = [
			'key'	=> 'val',
			'key2'	=> 'val2',
		];

		$this->config->merge($collection)->shouldbeCalled(1);
		$this->config->all()->willReturn($collection);

		// phpcs:ignore
		\tad\FunctionMockerLe\define('get_option', function ( string $key ) : string {
			return 'theme_name';
		});

		// phpcs:ignore
		\tad\FunctionMockerLe\define('update_option', function ( string $key, array $value ) use ( $collection ) {
			$this->assertStringContainsString('theme_mods_theme_name', $key, '');
			$this->assertSame($collection, $value, '');
		});

		$sut = $this->getInstance();
		$sut->merge( $collection );
	}
}
