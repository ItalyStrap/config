<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use Cache\IntegrationTests\SimpleCacheTest;
use ItalyStrap\Config\Config;

class SimpleConfigTest extends SimpleCacheTest
{
    protected $skippedTests = [
//      'testSet' => 'Not passed test',
        'testSetTtl' => 'The WordPress Object Cache expiration is not used',
        'testSetExpiredTtl' => 'The WordPress Object Cache expiration is not used',
//      'testGet' => 'Not passed test',
//      'testDelete' => 'Not passed test',
//      'testClear' => 'Not passed test',
        'testSetMultiple' => 'Not passed test',
        'testSetMultipleWithIntegerArrayKey' => 'Not passed test',
        'testSetMultipleTtl' => 'The WordPress Object Cache expiration is not used',
        'testSetMultipleExpiredTtl' => 'The WordPress Object Cache expiration is not used',
        'testSetMultipleWithGenerator' => 'Not passed test',
        'testGetMultiple' => 'Not passed test',
        'testGetMultipleWithGenerator' => 'Not passed test',
        'testDeleteMultiple' => 'Not passed test',
        'testDeleteMultipleGenerator' => 'Not passed test',
//      'testHas' => 'Not passed test',
//      'testBasicUsageWithLongKey' => 'Not passed test',
        /**
         * Invalid keys
         */
        'testGetInvalidKeys' => 'Not passed test',
        'testGetMultipleInvalidKeys' => 'Not passed test',
        'testGetMultipleNoIterable' => 'Not passed test',
        'testSetInvalidKeys' => 'Not passed test',
        'testSetMultipleInvalidKeys' => 'Not passed test',
        'testSetMultipleNoIterable' => 'Not passed test',
        'testHasInvalidKeys' => 'Not passed test',
        'testDeleteInvalidKeys' => 'Not passed test',
        'testDeleteMultipleInvalidKeys' => 'Not passed test',
        'testDeleteMultipleNoIterable' => 'Not passed test',
        /**
         * Invalid TTL
         */
        'testSetInvalidTtl' => 'Not passed test',
        'testSetMultipleInvalidTtl' => 'Not passed test',

//      'testNullOverwrite' => 'Not passed test',
//      'testDataTypeString' => 'Not passed test',
//      'testDataTypeInteger' => 'Not passed test',
//      'testDataTypeFloat' => 'Not passed test',
//      'testDataTypeBoolean' => 'Not passed test',
//      'testDataTypeArray' => 'Not passed test',
//      'testDataTypeObject' => 'Not passed test',
//      'testBinaryData' => 'Not passed test',
        'testSetValidKeys' => 'Not passed test',
        'testSetMultipleValidKeys' => 'Not passed test',
//      'testSetValidData' => 'Not passed test',
        'testSetMultipleValidData' => 'Not passed test',
//      'testObjectAsDefaultValue' => 'Not passed test',
        'testObjectDoesNotChangeInCache' => 'Not passed test',
    ];

    public function createSimpleCache()
    {
        return new SimpleConfigAdapterMock(new Config());
    }

    private function makeInstance()
    {
        return $this->createSimpleCache();
    }

    public function testShouldGetZeroAsValue()
    {
        $sut = $this->makeInstance();
        $sut->set('key', 0);
        $value = $sut->get('key');
        $this->assertSame(0, $value, '');
    }


    public function testShouldGetOneAsValue()
    {
        $sut = $this->makeInstance();
        $sut->set('key', 1);
        $value = $sut->get('key');
        $this->assertSame(1, $value, '');
    }

    public function valueProviderForSet()
    {
        return [
            'custom value'  =>  [
                'custom-value'
            ],
            'zero value'    =>  [
                0
            ],
            'one value' =>  [
                1
            ],
            'negative value'    =>  [
                -1
            ],
            'float value'   =>  [
                1.1
            ],
            'bool true' =>  [
                true
            ],
            'bool false'    =>  [
                false
            ],
            'empty array'   =>  [
                []
            ],
            'array' =>  [
                ['key' => 'value']
            ],
            'empty obj' =>  [
                (new \stdClass())
            ],
            'null value'    =>  [
                null
            ],
            'serialized obj'    =>  [
                \serialize(( new \stdClass() ))
            ],
            'serialized array'  =>  [
                \serialize(['key' => 'value'])
            ],
        ];
    }

    /**
     * @dataProvider valueProviderForSet()
     */
    public function testItShouldSetValue($value)
    {
        $sut = $this->makeInstance();
        $this->assertTrue($sut->set('key', $value), '');
//      $this->assertTrue($sut->has('key'), '');
        $this->assertSame($value, $sut->get('key'), '');
    }
}
