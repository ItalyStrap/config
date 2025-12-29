<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;

class NodeManipulationTest extends TestCase
{
    private function makeInstance(array $default = []): ConfigInterface
    {
        return new Config($default);
    }

    public static function appendToDataProvider(): \Generator
    {
        yield 'Append single value to existing array' => [
            ['items' => ['apple', 'banana']],
            'items',
            'orange',
            ['apple', 'banana', 'orange'],
        ];

        yield 'Append single value to empty array' => [
            ['items' => []],
            'items',
            'apple',
            ['apple'],
        ];

        yield 'Append array value to existing array' => [
            ['items' => ['apple']],
            'items',
            ['banana', 'orange'],
            ['apple', 'banana', 'orange'],
        ];

        yield 'Append to nested path' => [
            ['key' => ['subKey' => ['value1']]],
            'key.subKey',
            'value2',
            ['value1', 'value2'],
        ];

        yield 'Append associative array' => [
            ['settings' => ['plugins' => ['plugin1' => true]]],
            'settings.plugins',
            ['plugin2' => false],
            ['plugin1' => true, 'plugin2' => false],
        ];

        yield 'Append to non-existent key creates array' => [
            [],
            'items',
            'apple',
            ['apple'],
        ];
    }

    /**
     * @dataProvider appendToDataProvider
     */
    public function testAppendToAddsValuesToEnd(array $initial, string $key, $value, array $expected): void
    {
        $config = $this->makeInstance($initial);
        $config->appendTo($key, $value);
        $this->assertSame($expected, $config->get($key));
    }

    public function testAppendToMultipleValuesAddsAllToEnd(): void
    {
        $config = $this->makeInstance(['items' => ['value1']]);
        $config->appendTo('items', 'value2');
        $config->appendTo('items', 'value3');
        $config->appendTo('items', 'value3');

        $this->assertSame(['value1', 'value2', 'value3', 'value3'], $config->get('items'));
    }

    public function testAppendToWithMixedArrayTypes(): void
    {
        $config = $this->makeInstance();
        $config->appendTo('settings.plugins', ['plugin1' => true]);
        $config->appendTo('settings.plugins', ['plugin2']);
        $config->appendTo('settings.plugins', ['plugin3']);
        $config->appendTo('settings.plugins', 'plugin4');
        $config->appendTo('settings.plugins', 5);

        $this->assertSame(
            ['plugin1' => true, 'plugin2', 'plugin3', 'plugin4', 5],
            $config->get('settings.plugins')
        );
    }

    public static function nonArrayThrowsExceptionDataProvider(): \Generator
    {
        yield 'appendTo on non-array throws exception' => [
            'appendTo',
            ['items' => 'not-an-array'],
            'items',
            'value',
            'set',
        ];

        yield 'prependTo on non-array throws exception' => [
            'prependTo',
            ['items' => 'not-an-array'],
            'items',
            'value',
            'set',
        ];

        yield 'insertAt on non-array throws exception' => [
            'insertAt',
            ['items' => 'not-an-array'],
            'items',
            'value',
            'set',
        ];

        yield 'deleteFrom on non-array throws exception' => [
            'deleteFrom',
            ['items' => 'not-an-array'],
            'items',
            'value',
            'delete',
        ];
    }

    /**
     * @dataProvider nonArrayThrowsExceptionDataProvider
     * @param mixed $value
     */
    public function testNodeManipulationOnNonArrayThrowsException(
        string $method,
        array $initialData,
        string $key,
        $value,
        string $expectedMethodInMessage
    ): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'The value at "%s" is not an array, use the `%s::%s()` method instead',
                $key,
                \ItalyStrap\Config\Config::class,
                $expectedMethodInMessage
            )
        );

        $config = $this->makeInstance($initialData);

        if ($method === 'insertAt') {
            $config->insertAt($key, $value, 0);
            return;
        }

        $config->$method($key, $value);
    }

    public function testAppendToNonArrayThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);

        $config = $this->makeInstance(['items' => 'not-an-array']);
        $config->appendTo('items', 'value');
    }

    public static function prependToDataProvider(): \Generator
    {
        yield 'Prepend single value to existing array' => [
            ['items' => ['banana', 'orange']],
            'items',
            'apple',
            ['apple', 'banana', 'orange'],
        ];

        yield 'Prepend single value to empty array' => [
            ['items' => []],
            'items',
            'apple',
            ['apple'],
        ];

        yield 'Prepend array value to existing array' => [
            ['items' => ['orange']],
            'items',
            ['apple', 'banana'],
            ['apple', 'banana', 'orange'],
        ];

        yield 'Prepend to nested path' => [
            ['key' => ['subKey' => ['value2']]],
            'key.subKey',
            'value1',
            ['value1', 'value2'],
        ];

        yield 'Prepend to non-existent key creates array' => [
            [],
            'items',
            'apple',
            ['apple'],
        ];
    }

    /**
     * @dataProvider prependToDataProvider
     */
    public function testPrependToAddsValuesToBeginning(array $initial, string $key, $value, array $expected): void
    {
        $config = $this->makeInstance($initial);
        $config->prependTo($key, $value);
        $this->assertSame($expected, $config->get($key));
    }

    public function testPrependToMultipleValuesAddsAllToBeginning(): void
    {
        $config = $this->makeInstance(['items' => ['value3']]);
        $config->prependTo('items', 'value2');
        $config->prependTo('items', 'value1');

        $this->assertSame(['value1', 'value2', 'value3'], $config->get('items'));
    }

    public static function insertAtDataProvider(): \Generator
    {
        yield 'Insert at beginning (position 0)' => [
            ['items' => ['banana', 'orange']],
            'items',
            'apple',
            0,
            ['apple', 'banana', 'orange'],
        ];

        yield 'Insert in middle' => [
            ['items' => ['apple', 'orange']],
            'items',
            'banana',
            1,
            ['apple', 'banana', 'orange'],
        ];

        yield 'Insert at end' => [
            ['items' => ['apple', 'banana']],
            'items',
            'orange',
            2,
            ['apple', 'banana', 'orange'],
        ];

        yield 'Insert into empty array' => [
            ['items' => []],
            'items',
            'apple',
            0,
            ['apple'],
        ];

        yield 'Insert array at position' => [
            ['items' => ['apple', 'orange']],
            'items',
            ['banana', 'grape'],
            1,
            ['apple', 'banana', 'grape', 'orange'],
        ];

        yield 'Insert to nested path' => [
            ['key' => ['subKey' => ['value1', 'value3']]],
            'key.subKey',
            'value2',
            1,
            ['value1', 'value2', 'value3'],
        ];
    }

    /**
     * @dataProvider insertAtDataProvider
     */
    public function testInsertAtAddsValueAtSpecificPosition(
        array $initial,
        string $key,
        $value,
        int $position,
        array $expected
    ): void {
        $config = $this->makeInstance($initial);
        $config->insertAt($key, $value, $position);
        $this->assertSame($expected, $config->get($key));
    }

    public function testInsertAtNonArrayThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);

        $config = $this->makeInstance(['items' => 'not-an-array']);
        $config->insertAt('items', 'value', 0);
    }

    public static function deleteFromDataProvider(): \Generator
    {
        yield 'Delete single value from array' => [
            ['items' => ['apple', 'banana', 'orange']],
            'items',
            'banana',
            ['apple', 'orange'],
        ];

        yield 'Delete first value' => [
            ['items' => ['apple', 'banana', 'orange']],
            'items',
            'apple',
            ['banana', 'orange'],
        ];

        yield 'Delete last value' => [
            ['items' => ['apple', 'banana', 'orange']],
            'items',
            'orange',
            ['apple', 'banana'],
        ];

        yield 'Delete array of values' => [
            ['items' => ['apple', 'banana', 'orange', 'grape']],
            'items',
            ['banana', 'grape'],
            ['apple', 'orange'],
        ];

        yield 'Delete from nested path' => [
            ['key' => ['subKey' => ['value1', 'value2', 'value3']]],
            'key.subKey',
            'value2',
            ['value1', 'value3'],
        ];

        yield 'Delete associative array value' => [
            ['settings' => ['plugins' => ['plugin1' => true, 'plugin2', 'plugin3']]],
            'settings.plugins',
            'plugin2',
            ['plugin1' => true, 'plugin3'],
        ];

        yield 'Delete integer value' => [
            ['items' => ['apple', 5, 'banana']],
            'items',
            5,
            ['apple', 'banana'],
        ];
    }

    /**
     * @dataProvider deleteFromDataProvider
     */
    public function testDeleteFromRemovesValues(array $initial, string $key, $value, array $expected): void
    {
        $config = $this->makeInstance($initial);
        $config->deleteFrom($key, $value);
        $this->assertSame($expected, $config->get($key));
    }

    public function testDeleteFromLastValueRemovesKey(): void
    {
        $config = $this->makeInstance(['items' => ['apple']]);
        $result = $config->deleteFrom('items', 'apple');
        $this->assertTrue($result);
        $this->assertNull($config->get('items'));
    }

    public function testDeleteFromAllValuesRemovesKey(): void
    {
        $config = $this->makeInstance(['settings' => ['plugins' => ['plugin1' => true]]]);
        $result = $config->deleteFrom('settings.plugins', ['plugin1' => true]);
        $this->assertTrue($result);
        $this->assertNull($config->get('settings.plugins'));
    }

    public function testDeleteFromRemovesTheFirstOccurrence(): void
    {
        $config = $this->makeInstance(['items' => ['apple', 'banana', 'banana', 'orange']]);
        $config->deleteFrom('items', 'banana');
        $this->assertSame(['apple', 'banana', 'orange'], $config->get('items'));
    }

    public function testDeleteFromNonExistentValue(): void
    {
        $config = $this->makeInstance(['items' => ['apple', 'banana']]);
        $config->deleteFrom('items', 'orange');
        $this->assertSame(['apple', 'banana'], $config->get('items'));
    }

    public function testDeleteFromNonExistentKeyReturnsTrue(): void
    {
        $config = $this->makeInstance([]);
        $result = $config->deleteFrom('items', 'apple');
        $this->assertTrue($result);
    }

    public function testDeleteFromNonArrayThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);

        $config = $this->makeInstance(['items' => 'not-an-array']);
        $config->deleteFrom('items', 'value');
    }

    public function testDeleteFromSequentialOperations(): void
    {
        $config = $this->makeInstance([
            'settings' => ['plugins' => ['plugin1' => true, 'plugin2', 'plugin3', 'plugin4', 5]],
        ]);

        $config->deleteFrom('settings.plugins', 'plugin3');
        $this->assertSame(
            ['plugin1' => true, 'plugin2', 'plugin4', 5],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', 'plugin4');
        $this->assertSame(
            ['plugin1' => true, 'plugin2', 5],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', 5);
        $this->assertSame(
            ['plugin1' => true, 'plugin2'],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', 'plugin2');
        $this->assertSame(
            ['plugin1' => true],
            $config->get('settings.plugins')
        );
    }

    public function testComplexArrayBehaviourWithMixedTypes(): void
    {
        $config = $this->makeInstance();

        $config->set('key.subKey', ['value']);
        $config->appendTo('key.subKey', 'value5');
        $this->assertSame(['value', 'value5'], $config->get('key.subKey'));

        $config->appendTo('key.subKey', 'value6');
        $config->appendTo('key.subKey', 'value6');
        $this->assertSame(['value', 'value5', 'value6', 'value6'], $config->get('key.subKey'));

        $config->appendTo('config.allow-plugins', ['vendor/name' => true]);
        $this->assertSame(
            [
                'vendor/name' => true,
            ],
            $config->get('config.allow-plugins')
        );

        $config->appendTo('config.allow-plugins', ['vendor/name-ttt']);
        $this->assertSame(
            [
                'vendor/name' => true,
                'vendor/name-ttt',
            ],
            $config->get('config.allow-plugins')
        );

        $config->appendTo('config.allow-plugins', ['vendor/name-ppp']);
        $config->appendTo('config.allow-plugins', ['vendor/name-ppp']);
        $config->appendTo('config.allow-plugins', 'vendor/name-ccc');
        $config->appendTo('config.allow-plugins', 5);
        $this->assertSame(
            [
                'vendor/name' => true,
                'vendor/name-ttt',
                'vendor/name-ppp',
                'vendor/name-ppp',
                'vendor/name-ccc',
                5,
            ],
            $config->get('config.allow-plugins')
        );

        $config->deleteFrom('key.subKey', 'value5');
        $this->assertSame(['value', 'value6', 'value6'], $config->get('key.subKey'));

        $config->deleteFrom('key.subKey', 'value6');
        $this->assertSame(['value', 'value6'], $config->get('key.subKey'));

        $config->deleteFrom('config.allow-plugins', 'vendor/name-ppp');
        $this->assertSame(
            [
                'vendor/name' => true,
                'vendor/name-ttt',
                'vendor/name-ppp',
                'vendor/name-ccc',
                5,
            ],
            $config->get('config.allow-plugins')
        );

        $config->deleteFrom('config.allow-plugins', 'vendor/name-ccc');
        $this->assertSame(
            [
                'vendor/name' => true,
                'vendor/name-ttt',
                'vendor/name-ppp',
                5,
            ],
            $config->get('config.allow-plugins')
        );

        $config->deleteFrom('config.allow-plugins', 5);
        $this->assertSame(
            [
                'vendor/name' => true,
                'vendor/name-ttt',
                'vendor/name-ppp',
            ],
            $config->get('config.allow-plugins')
        );

        $config->deleteFrom('config.allow-plugins', 'vendor/name-ttt');
        $this->assertSame(
            [
                'vendor/name' => true,
                'vendor/name-ppp',
            ],
            $config->get('config.allow-plugins'),
            'The value should be an array with one element'
        );

        $config->deleteFrom('config.allow-plugins', ['vendor/name' => true, 'vendor/name-ppp']);
        $this->assertNull($config->get('config.allow-plugins'), 'The value should be null');
    }

    public function testAppendToWithAssociativeArrayAndDuplicates(): void
    {
        $config = $this->makeInstance();

        $config->appendTo('settings.plugins', ['plugin1' => true]);
        $this->assertSame(
            ['plugin1' => true],
            $config->get('settings.plugins')
        );

        $config->appendTo('settings.plugins', ['plugin2']);
        $this->assertSame(
            ['plugin1' => true, 'plugin2'],
            $config->get('settings.plugins')
        );

        $config->appendTo('settings.plugins', ['plugin3']);
        $config->appendTo('settings.plugins', ['plugin3']);
        $config->appendTo('settings.plugins', 'plugin4');
        $config->appendTo('settings.plugins', 5);
        $this->assertSame(
            [
                'plugin1' => true,
                'plugin2',
                'plugin3',
                'plugin3',
                'plugin4',
                5,
            ],
            $config->get('settings.plugins')
        );
    }

    public function testDeleteFromCompleteWorkflow(): void
    {
        $config = $this->makeInstance();

        $config->set('key.subKey', ['value1', 'value2', 'value3']);
        $config->set('settings.plugins', ['plugin1' => true, 'plugin2', 'plugin3', 'plugin4', 5]);

        $config->deleteFrom('key.subKey', 'value2');
        $this->assertSame(['value1', 'value3'], $config->get('key.subKey'));

        $config->deleteFrom('key.subKey', 'value3');
        $this->assertSame(['value1'], $config->get('key.subKey'));

        $config->deleteFrom('settings.plugins', 'plugin3');
        $this->assertSame(
            ['plugin1' => true, 'plugin2', 'plugin4', 5],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', 'plugin4');
        $this->assertSame(
            ['plugin1' => true, 'plugin2', 5],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', 5);
        $this->assertSame(
            ['plugin1' => true, 'plugin2'],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', 'plugin2');
        $this->assertSame(
            ['plugin1' => true],
            $config->get('settings.plugins')
        );

        $config->deleteFrom('settings.plugins', ['plugin1' => true]);
        $this->assertNull($config->get('settings.plugins'));
    }

    public function testPrependToWithInitialArrayAndMultipleValues(): void
    {
        $config = $this->makeInstance();

        $config->set('key.subKey', ['value2']);
        $config->prependTo('key.subKey', 'value1');
        $this->assertSame(['value1', 'value2'], $config->get('key.subKey'));

        $config->prependTo('key.subKey', 'value0');
        $this->assertSame(['value0', 'value1', 'value2'], $config->get('key.subKey'));
    }

    public function testAppendToWithMultidimensionalArrayFragment(): void
    {
        $config = $this->makeInstance();
        $config->set('root', []);

        $fragment = [
            'stubs' => [
                [
                    'file' => [
                        [
                            '@attributes' => [
                                'name' => 'vendor/inpsyde/wp-stubs-versions/latest.php',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $config->appendTo('root', $fragment);
        $this->assertSame($fragment, $config->get('root'));
    }
}
