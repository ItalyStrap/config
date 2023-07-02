<?php

declare(strict_types=1);

namespace ItalyStrap\Config\Tests\Unit;

use ItalyStrap\Config\AccessValueInArrayWithNotationTrait;
use ItalyStrap\Tests\TestCase;

class AccessValueInArrayWithNotationTest extends TestCase
{

    private array $values = [];

    protected function makeInstance($val = [], $default = [])
    {
        return new class($this->values) {
            use AccessValueInArrayWithNotationTrait;

            private array $values;

            public function __construct($values = [])
            {
                $this->values = $values;
            }

            public function get(string $key, $default = null)
            {
                $levels = \explode('.', $key);
                //        $shifted = \array_shift($levels);

                return $this->findValue($this->values, $levels, $default);
            }

            public function set(string $key, $value): bool
            {
                $levels = \explode('.', $key);
                //        $shifted = \array_shift($levels);

                return $this->appendValue($this->values, $levels, $value);
            }

            public function delete(string $key): bool
            {
                $levels = \explode('.', $key);
                //        $shifted = \array_shift($levels);

                return $this->deleteValue($this->values, $levels);
            }
        };
    }

    public static function defaultDataProvider(): iterable
    {
        yield 'empty' => [
            [],
            'key',
            null,
        ];

        yield 'empty array' => [
            [],
            'key',
            [],
        ];

        yield 'empty string' => [
            [],
            'key',
            '',
        ];

        yield 'empty int' => [
            [],
            'key',
            0,
        ];

        yield 'key not exists' => [
            [
                'key1' => 'value',
            ],
            'key2',
            null,
        ];

        yield 'sub key not exists' => [
            [
                'key1' => [
                    'key2' => 'value',
                ],
            ],
            'key1.key3',
            null,
        ];

        yield 'sub key not exists and return default value' => [
            [
                'key1' => [
                    'key2' => 'value',
                ],
            ],
            'key1.key3',
            'default value',
        ];
    }

    /**
     * @dataProvider defaultDataProvider
     */
    public function testReturnDefaultIfValueDoesNotExistsInEmptyArray(array $values, string $key, $expected)
    {
        $this->values = $values;

        $sut = $this->makeInstance();

        $value = $sut->get($key, $expected);
        $this->assertSame($expected, $value);
    }

    public function testGetValueFromChainKeys(): void
    {
        $this->values = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
            ],
        ];

        $sut = $this->makeInstance();

        $value = $sut->get('key1.key2.key3', 'default');
        $this->assertSame('value', $value);
    }

    public function testSetValueFromChainKeys()
    {
        $this->values = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ];

        $sut = $this->makeInstance();

        $sut->set('key1.key2.key3', 'new value');
        $actual = $sut->get('key1.key2.key3', 'default');

        $this->assertSame('new value', $actual);
        $this->assertSame('value4', $sut->get('key1.key4', 'default'));

        $sut->set('key1.key4', 'new value4');
        $this->assertSame('new value4', $sut->get('key1.key4', 'default'));
    }

    public function testDeleteValueFromChainKeys()
    {
        $this->values = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ];

        $sut = $this->makeInstance();

        $actual = $sut->get('key1.key2.key3', 'default');
        $this->assertSame('value', $actual);

        $sut->delete('key1.key2.key3');
        $actual = $sut->get('key1.key2.key3', 'default');
        $this->assertSame('default', $actual);

        $actual = $sut->get('key1.key2', 'default');
        $this->assertSame([], $actual);
    }
}
