<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ArrayIterator;
use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigFactory;
use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\StorageTests\CommonStoreMultipleTestsTrait;
use stdClass;

final class ConfigTest extends TestCase
{
    use CommonStoreMultipleTestsTrait;

    private function makeInstance($val = [], array $default = []): ConfigInterface
    {
        return new Config($val, $default);
    }

    /**
     * @test
     */
    public function factory(): void
    {
        $sut = (new ConfigFactory())->make([]);
        $this->assertInstanceOf(Config::class, $sut);
        $this->assertInstanceOf(ConfigInterface::class, $sut);
    }

    public function valueProvider(): iterable
    {

        yield 'empty values'    => [
            false,
            false
        ];

        yield 'one value'   => [
            [],
            false
        ];

        yield 'two values'  => [
            [],
            []
        ];

        yield 'one array'   => [
            $this->config_arr,
            false
        ];

        yield 'two array the second is the default' => [
            $this->config_arr,
            $this->default_arr
        ];

        yield 'with stdClass'   => [
            new \stdClass(),
            new \stdClass(),
        ];

        yield 'with Iterator'   => [
            new \ArrayIterator(),
            new \ArrayIterator(),
        ];

        yield 'with ArrayObject'    => [
            new \ArrayObject(),
            new \ArrayObject(),
        ];

        yield 'with IteratorIterator'   => [
            new \IteratorIterator(new \ArrayObject()),
            new \IteratorIterator(new \ArrayObject()),
        ];
    }

    /**
     * @test
     * @dataProvider valueProvider()
     */
    public function itShouldBeInstantiatableWith($value, $default): void
    {
        $this->makeInstance((array) $value, (array) $default);
    }

    /**
     * @test
     */
    public function deprecatedPush(): void
    {
        $sut = $this->makeInstance();
        $sut->push('key', 42);
        $this->assertSame($sut->toArray(), ['key' => 42]);
    }

    /**
     * @test
     * it should have key
     */
    public function itShouldHaveKey(): void
    {
        $config = $this->makeInstance($this->config_arr);

        $this->assertTrue($config->has('tizio'));
        $this->assertTrue($config->has('caio'));
        $this->assertTrue($config->has('sempronio'));

        $this->assertTrue($config->has('object'));
        $this->assertTrue($config->has('object.key'));
        $this->assertTrue($config->has('object.sub-object.sub-key'));
        $this->assertTrue($config->has(['object','sub-object','sub-key']));

        $this->assertFalse($config->has('cesare'));
        $this->assertFalse($config->has('cheeseburger'));
    }

    public function keyTypeProvider(): array
    {
        return [
            'int'   => [
                1,
                "a",
            ],
            'string int'    => [
                "1",
                "b",
            ],
        ];
    }

    /**
     * @test
     * @dataProvider keyTypeProvider()
     */
    public function itShouldHaveKeyWith($key, string $value): void
    {
        $config = $this->makeInstance([
            $key    => $value
        ]);

        $this->assertTrue($config->has($key));

        $expected = $value;
        /** @var string $actual */
        $actual = $config->get($key);

        $this->assertStringMatchesFormat($expected, $actual, '');
    }

    /**
     * @test
     */
    public function itShouldHaveAndGetKey(): void
    {
        $config = $this->makeInstance($this->config_arr);
        $this->assertTrue($config->has('sempronio'));
        $this->assertIsArray($config->get('recursive'));
        $this->assertArrayHasKey('subKey', $config->get('recursive'));
    }

    /**
     * @test
     */
    public function itShouldResetDefaultMemberOnEveryCallAndOnlyReturnValueIfExist(): void
    {
        $config = $this->makeInstance([ 'key' => 'value' ]);
        $this->assertFalse($config->has('some-key'));
        $this->assertFalse($config->has('some-key'));
        $this->assertStringContainsString(
            $config->get('some-key', 'default value'),
            'default value',
            ''
        );
        $this->assertStringContainsString(
            $config->get('some-key.with-subkey', 'default value'),
            'default value',
            ''
        );
        $this->assertFalse($config->has('some-key'));
        $this->assertStringContainsString(
            strval($config->get('some-key', 'other default value')),
            'other default value',
            ''
        );

        $this->assertFalse($config->has('some-key'));
        $this->assertEmpty($config->get('some-key'), '');

        $this->assertStringContainsString(
            strval($config->get('some-key', 'other default value')),
            'other default value',
            ''
        );
        $this->assertEmpty($config->get('some-key'), '');
        $this->assertFalse($config->has('some-key'));
    }

    /**
     * @test
     */
    public function itShouldGetKey(): void
    {
        $config = $this->makeInstance($this->config_arr);

        $this->assertEquals([], $config->get('tizio'));
        $this->assertEquals([], $config->tizio);

        $this->assertEquals(
            $this->config_arr['object'],
            $config->get('object')
        );

        $this->assertEquals(
            $this->config_arr['object']['key'],
            $config->get('object.key')
        );

        $this->assertEquals(
            $this->config_arr['object']['sub-object']['sub-key'],
            $config->get('object.sub-object.sub-key')
        );

        $this->assertEquals(
            $this->config_arr['object']['sub-object']['sub-key'],
            $config->get(['object','sub-object','sub-key'])
        );
    }

    public function testWrongKeyProvided(): void
    {
        $sut = $this->makeInstance();

        $sut->set('key.key2.key3', 'value');
        try {
            $sut->get(['key','key2',['key3']]);
        } catch (\Exception $exception) {
            $this->assertStringContainsString('Array to string conversion', $exception->getMessage());
        }
    }

    /**
     * @test
     */
    public function itShouldSearchSubKeys(): void
    {
        $arr = [
            'key'   => [
                'subKey'    => 'subvalue',
                'subSubKey' => [
                    'subSubKeyKey'  => 'subSubValue'
                ],
            ],
        ];

        $config = $this->makeInstance($arr);

        $this->assertTrue($config->has('key.subKey'));
        $this->assertNotTrue($config->has('key.subKeyfgsfg'));
        $this->assertTrue($config->has('key.subSubKey.subSubKeyKey'));

        $this->assertEquals($arr['key']['subKey'], $config->get('key.subKey'), '');
        $this->assertEquals($arr['key']['subSubKey'], $config->get('key.subSubKey'), '');
        $this->assertEquals(
            $arr['key']['subSubKey']['subSubKeyKey'],
            $config->get('key.subSubKey.subSubKeyKey'),
            ''
        );
        $this->assertEquals('subSubValue', $config->get('key.subSubKey.subSubKeyKey'), '');
    }

    public static function defaultValueProvider(): iterable
    {
        yield 'Key is empty' => [
            [], // Data
            '', // Key
            'default', // Expected
        ];

        yield 'Key does not exist' => [
            [], // Data
            'noKey', // Key
            'default', // Expected
        ];

        yield 'Key does not exist and default is array' => [
            [], // Data
            'noKey', // Key
            ['default'], // Expected
        ];

        yield 'Key does not exist and default is object' => [
            [], // Data
            'noKey', // Key
            (object) ['default'], // Expected
        ];

        yield 'Key does not exist and default is integer' => [
            [], // Data
            'noKey', // Key
            42, // Expected
        ];

        yield 'Key does not exist and default is float' => [
            [], // Data
            'noKey', // Key
            42.42, // Expected
        ];

        yield 'Key does not exist and default is boolean' => [
            [], // Data
            'noKey', // Key
            true, // Expected
        ];

        yield 'Key does not exist and default is null' => [
            [], // Data
            'noKey', // Key
            null, // Expected
        ];
    }

    /**
     * @test
     * @dataProvider defaultValueProvider()
     */
    public function itShouldReturnDefaultValueIf(array $data, string $key, $expected): void
    {
        $config = $this->makeInstance($data);
        $this->assertEquals($expected, $config->get($key, $expected));
    }

    /**
     * @test
     */
    public function itShouldSetKey(): void
    {
        $config = $this->makeInstance();
        $config->var = 'Value';

        $this->assertEquals('Value', $config->get('var'));
        $this->assertEquals('Value', $config->var);

        $config->set('var2.subVar', 'Value2');
        $this->assertEquals('Value2', $config->get('var2.subVar'));

        $config->set(['var2','subVar'], 'Value2');
        $this->assertEquals('Value2', $config->get('var2.subVar'));
    }

    /**
     * @test
     */
    public function itShouldReturnAnArray(): void
    {
        $config = $this->makeInstance($this->config_arr);

        $this->assertIsArray($config->toArray());
        $this->assertEquals($this->config_arr, $config->toArray());
    }

    /**
     * @test
     */
    public function itShouldAddNewItem(): void
    {
        $config = $this->makeInstance($this->config_arr, $this->default_arr);
        $config->set('new_item', true);

        $this->assertTrue($config->get('new_item'));
    }

    /**
     * @test
     */
    public function itShouldReplaceRecursively(): void
    {
        $config = $this->makeInstance($this->default_arr);
        $this->assertEquals($this->default_arr['recursive'], $config->get('recursive'));

        $config = $this->makeInstance($this->config_arr, $this->default_arr);
        $this->assertEquals($this->config_arr['recursive'], $config->get('recursive'));
        $this->assertEquals($this->config_arr['recursive'], $config->recursive);
        $this->assertEquals($this->config_arr['recursive']['subKey'], $config->recursive['subKey']);

        $this->assertNotEquals($this->default_arr['recursive'], $config->get('recursive'));
        $this->assertNotEquals($this->default_arr['recursive'], $config->recursive);
        $this->assertNotEquals($this->default_arr['recursive']['subKey'], $config->recursive['subKey']);
    }

    /**
     * @test
     */
    public function itShouldMergeGivenArray(): void
    {
        $config = $this->makeInstance($this->config_arr, $this->default_arr);

        $new_array = [
            'new_key'   => 'New Value',
            'recursive' => [
                'subKey'    => 'otherSubValue',
            ],
        ];

        $config->merge($new_array);

        $this->assertEquals('New Value', $config->get('new_key'));

        $this->assertEquals($new_array['recursive'], $config->get('recursive'));
        $this->assertEquals($new_array['recursive'], $config['recursive']);
        $this->assertEquals($new_array['recursive'], $config->recursive);
        $this->assertEquals($new_array['recursive']['subKey'], $config->recursive['subKey']);

        $config->merge($new_array, [ 'new_key'   => 'Value changed' ], [ 'new_key'   => 'Value changed2' ]);
        $this->assertEquals('Value changed2', $config->get('new_key'));

        $config->merge('Ciao');
        $this->assertEquals('Ciao', $config->get('0'));
    }

    /**
     * @test
     */
    public function itShouldMergeGivenGenerator(): void
    {
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
    public function itShouldDeleteValues(): void
    {
        $config = $this->makeInstance($this->config_arr, $this->default_arr);
        $config->delete('recursive');
        $this->assertFalse($config->has('recursive'));

        $this->assertTrue($config->has('tizio'));
        $this->assertTrue($config->has('caio'));

        $config->deleteMultiple(['tizio', 'caio']);

        $this->assertFalse($config->has('tizio'));
        $this->assertFalse($config->has('caio'));

        $config = $this->makeInstance($this->config_arr, $this->default_arr);
        $config->deleteMultiple(['recursive']);
        $this->assertFalse($config->has('recursive'));

        $config = $this->makeInstance($this->config_arr, $this->default_arr);
        $this->assertTrue($config->has('recursive'));
        $this->assertTrue($config->has('tizio'));
        $config->deleteMultiple(['recursive', 'tizio']);
        $this->assertFalse($config->has('recursive'));
        $this->assertFalse($config->has('tizio'));

        $config->delete(0);
        $this->assertFalse($config->has(0));

        $config->set(['var2','subVar'], 'Value2');
        $this->assertSame('Value2', $config->get('var2.subVar'));
        $config->delete(['var2','subVar']);
        $this->assertFalse($config->has('var2.subVar'));
    }

    public function testItShouldDeleteAndReturnValueDependingOnTheCurrentCalled(): void
    {
        $data = [
            'falseValue' => false,
            'nullValue' => null,
            'emptyString' => '',
            'zeroValue' => 0,
            'emptyArray' => [],
            'validArray' => ['key' => 'value'],
            'deeperArrayWithScalarValue' => [
                'key' => [
                    'falseValue' => false,
                    'nullValue' => null,
                    'emptyString' => '',
                    'zeroValue' => 0,
                    'emptyArray' => [],
                    'validArray' => ['key' => 'value'],
                ],
            ],
        ];

        $config = $this->makeInstance($data);

        $this->assertTrue($config->has('validArray.key'));
        $this->assertSame('value', $config->get('validArray.key'));
        $this->assertTrue($config->delete('validArray.key'));
        $this->assertFalse($config->has('validArray.key'));
        $this->assertSame([], $config->get('validArray'));

        $this->assertTrue($config->has('deeperArrayWithScalarValue.key.falseValue'));
        $this->assertSame(false, $config->get('deeperArrayWithScalarValue.key.falseValue'));
    }

    /**
     * @test
     */
    public function itShouldSeePublicMembers(): void
    {
        $expected = 42;

        $config = $this->makeInstance();
        $config->test = $expected;
        $this->assertTrue($config->has('test'));
        $this->assertNotEmpty($config->test);

        $this->assertNotTrue($config->has('some'));
        $this->assertEmpty($config->some);
        $this->assertEquals($expected, $config->get('test'));

        $config[2] = 'value';
        $this->assertTrue($config->has('2'));

        $config->set('0', $expected);
        $this->assertEquals($expected, $config->get('0'));
    }

    /**
     * @test
     */
    public function itShouldBeIterable(): void
    {
        $arr = [ 'key' => 'val' ];
        $config = $this->makeInstance($arr);

        foreach ($config as $key => $value) {
            $this->assertTrue($arr[ $key ] === $value);
        }

        foreach ($config as $key => $value) {
            $this->assertTrue($config->$key === $value);
        }
    }

    /**
     * @test
     */
    public function itShouldMergeConfigObjectInArray(): void
    {
        $default = $this->makeInstance([ 'er' => 'sdf' ]);
        $config = $this->makeInstance($this->config_arr);

        $config->merge($default);
        $this->assertArrayHasKey('er', $config->toArray());

        $newconfig = new Config($default);
        $this->assertArrayHasKey('er', $newconfig->toArray());

        $iterator = new ArrayIterator(['recipe' => 'pancakes', 'egg', 'milk', 'flour']);
        $newconfig = new Config($iterator);
        $this->assertArrayHasKey('recipe', $newconfig->toArray());
        $this->assertArrayHasKey('recipe', $newconfig);

        $iterator = new ArrayIterator(['recipe2' => 'pancakes', 'egg', 'milk', 'flour']);
        $newconfig->merge($iterator);
        $this->assertArrayHasKey('recipe2', $newconfig->toArray());
        $this->assertArrayHasKey('recipe2', $newconfig);

        $stdobj = new stdClass();
        $stdobj->var = 'Value';
        $stdobj->obj = $stdobj;

        $anotherConfig = new Config($stdobj);
        $this->assertArrayHasKey('var', $anotherConfig);
        $this->assertEquals($stdobj->var, $anotherConfig->var);
        $this->assertEquals($stdobj->var, $anotherConfig->get('var'));
        $this->assertTrue($anotherConfig->has('obj.var'), '');
        $this->assertEquals($stdobj->obj->var, $anotherConfig->get('obj.var'));
    }

    /**
     * @test
     */
    public function itShouldReturnArray(): void
    {
        $config = $this->makeInstance($this->config_arr);
        $this->assertIsArray($config->toArray());
        foreach (\array_keys($this->config_arr) as $key) {
            $this->assertArrayHasKey($key, $config->toArray());
        }
    }

    /**
     * @test
     */
    public function itShouldCloneHaveEmptyValue(): void
    {
        $arr = [ 'key'  => 'value' ];
        $config = $this->makeInstance($arr);

        $this->assertStringContainsString(strval($config->get('key')), $arr['key'], '');
        $this->assertTrue($config->has('key'), '');
        $this->assertNotEmpty($config->get('key'), '');

        $clone = clone $config;

        $this->assertFalse($clone->has('key'), '');
        $this->assertEmpty($clone->get('key'), '');

        $this->assertNotSame($config, $clone, '');
    }

    /**
     * @test
     */
    public function itShouldHaveCallableInCollection(): void
    {
        $arr = [
            'key'   => fn(): string => 'Ciao',
        ];

        $config = $this->makeInstance($arr);
        $this->assertIsCallable($config->get('key'));
        $callable = $config->get('key');
        $this->assertTrue(\is_callable($callable));
    }

    /**
     * @test
     */
    public function itShouldReceiveIterableAsArgument(): void
    {
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
    public function itShouldReturnDefaultIfValFetchedIsNull(): void
    {
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
    public function itShouldCheckForStdClass(): void
    {
        $stdclass = new \stdClass();
        $stdclass->test = 'value';
        $array = [
            'test' => null,
        ];

        $sut = $this->makeInstance($array);
        $sut->set('test2', $stdclass);

        $this->assertSame('value', $sut->get('test2.test'), '');
    }

    /**
     * @test
     */
    public function itShouldBeDelimiterOk(): void
    {
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

    public function testSubArrayAccess(): void
    {
        $sut = $this->makeInstance(
            [
                'key'   => [
                    'sub-key'   => 'value',
                ],
            ]
        );

        $this->assertTrue($sut->has('key.sub-key'));
        $this->assertSame('value', $sut->get('key.sub-key'));
        $this->assertSame('value', $sut['key.sub-key']);
        $this->assertSame('value', $sut['key']['sub-key']);
        $this->assertSame(false, isset($sut['key']['not-exists']));
        $this->assertSame(false, isset($sut['key.not-exists']));
        $this->assertSame(false, $sut['key']['not-exists'] ?? false);
        $this->assertSame(null, $sut['key.not-exists']);
        $this->assertSame(null, $sut['key.not-exists.not-exists']);
        $sut['key.not-exists.not-exists'] = 'now exists';
        $this->assertSame('now exists', $sut['key.not-exists.not-exists']);

        // This is not possible
//         $sut['key']['sub'] = 'sub value';
    }

    public function testJsonSerialize(): void
    {
        $sut = $this->makeInstance(
            [
                'key'   => [
                    'sub-key'   => 'value',
                ],
            ]
        );

        $this->assertJsonStringEqualsJsonString(
            \json_encode($sut->toArray()),
            \json_encode($sut)
        );
    }

    public function testMergeConfigIntoProjectConfig(): void
    {
        $baseConfig = new Config();
        $default = [
            'key' => 'default',
        ];

        $actual = [
            'key' => 'value',
        ];

        $config = new Config($actual);
        $baseConfig->merge($config);
        $this->assertSame('value', $baseConfig->get('key'));

        $config = new Config($default);
        $config->merge($actual);
        $this->assertSame('value', $config->get('key'));
        $baseConfig->merge($config);
        $this->assertSame('value', $baseConfig->get('key'));
        $anotherConfig = new Config();
        $anotherConfig->merge($baseConfig);
        $this->assertSame('value', $anotherConfig->get('key'));
    }
}
