<?php

declare(strict_types=1);

namespace ItalyStrap\Config\Tests\Unit;

use ItalyStrap\Tests\Stubs\AccessValueInArrayWithNotation;
use ItalyStrap\Tests\TestCase;

class AccessValueInArrayWithNotationTest extends TestCase
{
    public function makeInstance(): AccessValueInArrayWithNotation
    {
        return new AccessValueInArrayWithNotation();
    }

    public function testFindValue()
    {
        $array = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
            ],
        ];

        $levels = ['key1', 'key2', 'key3'];

        $sut = $this->makeInstance();

        $result = $sut->findValue($array, $levels, 'default');

        $this->assertSame('value', $result);
    }

    public function testFindValueReturnDefault()
    {

        $array = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
            ],
        ];

        $levels = ['key1', 'key2', 'key4'];

        $sut = $this->makeInstance();

        $result = $sut->findValue($array, $levels, 'default');

        $this->assertSame('default', $result);
    }

    public function testInsertValueCoverNullKeyReturnFromArrayShift()
    {
        $array = [];
        $levels = [];
        $value = 'new value';

        $sut = $this->makeInstance();

        $result = $sut->insertValue($array, $levels, $value);

        $this->assertFalse($result);
        $this->assertNotContains('new value', $array);
        $this->assertSame([], $array);
    }

    public function testInsertValueCoverEmptyLevels()
    {
        $array = [];
        $levels = ['key1', 'key2', 'key3'];
        $value = 'new value';

        $sut = $this->makeInstance();

        $result = $sut->insertValue($array, $levels, $value);

        $this->assertTrue($result);
        $this->assertSame(['key1' => ['key2' => ['key3' => 'new value']]], $array);
    }

    public function testInsertValueCoverArrayKeyExists()
    {
        $array = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ];
        $levels = ['key1', 'key2', 'key3'];
        $value = 'new value';

        $sut = $this->makeInstance();

        $result = $sut->insertValue($array, $levels, $value);

        $this->assertTrue($result);
        $this->assertSame([
            'key1' => [
                'key2' => [
                    'key3' => 'new value',
                ],
                'key4' => 'value4',
            ],
        ], $array);
    }

    public function testDeleteValueCoverNullKeyReturnFromArrayShift()
    {
        $array = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ];
        $levels = [];

        $sut = $this->makeInstance();

        $result = $sut->deleteValue($array, $levels);

        $this->assertFalse($result);
        $this->assertSame([
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ], $array);
    }

    public function testDeleteValueCoverReturnTrueIfArrayKeyDoesNotExists()
    {

        $array = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ];
        $levels = [
            'key1',
            'key2',
            'key5',
        ];

        $sut = $this->makeInstance();

        $result = $sut->deleteValue($array, $levels);

        $this->assertTrue($result);
        $this->assertSame([
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ], $array);
    }

    public function testDeleteValueCoverReturnTrueIfValueIsCorrectlyDeleted(): void
    {
        $array = [
            'key1' => [
                'key2' => [
                    'key3' => 'value',
                ],
                'key4' => 'value4',
            ],
        ];
        $levels = ['key1', 'key2', 'key3'];

        $sut = $this->makeInstance();

        $result = $sut->deleteValue($array, $levels);

        $this->assertTrue($result);
        $this->assertSame(['key1' => ['key2' => [], 'key4' => 'value4']], $array);
    }
}
