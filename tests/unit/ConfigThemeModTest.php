<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\Config\ConfigThemeMods;
use ItalyStrap\Event\EventDispatcherInterface;

class ConfigThemeModTest extends TestCase
{
    protected function makeInstance($val = [], $default = []): ConfigThemeMods
    {
        return new ConfigThemeMods($this->makeConfig(), $this->makeDispatcher());
    }

    public function getAndAddOk()
    {
        $this->config->set('key')->willReturn(true);
        $this->dispatcher->filter('theme_mod_key', null)->willReturn('value');

		// phpcs:ignore
		\tad\FunctionMockerLe\define('set_theme_mod', function ( string $parameter_key, string $value ) {
            $this->assertStringContainsString('key', $parameter_key, '');
            $this->assertStringContainsString('value', $value, '');
            return true;
        });

        $sut = $this->makeInstance();
        $sut->set('key', 'value');

        $this->assertSame('value', $sut->get('key'), '');
    }

    public function removeOk()
    {
        $collection = [
            'key'   => 'val',
            'key2'  => 'val2',
        ];

		// phpcs:ignore
		\tad\FunctionMockerLe\define('remove_theme_mod', function ( $key ) use ( &$collection ) {
            unset($collection[ $key ]);
        });

        $sut = $this->makeInstance();
        $sut->remove('key', 'key2');

        $this->assertEmpty($collection, '');
    }

    /**
     * @test
     */
    public function mergeOk()
    {

        $collection = [
            'key'   => 'val',
            'key2'  => 'val2',
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

        $sut = $this->makeInstance();
        $sut->merge($collection);
    }
}
