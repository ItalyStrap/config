<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\Config\SignalCode;

final class TraverseMethodTest extends TestCase
{
    private function makeInstance(array $default = []): ConfigInterface
    {
        return new Config($default);
    }

    /**
     * ==========================
     * Category 1: Basic Traversal
     * ==========================
     */

    /**
     * Verifies that traversal visits each scalar value in a simple array.
     * Collects the values and asserts that they match the expected values
     */
    public function testBasicTraversalWithScalarValues(): void
    {
        $config = new Config([
            'colors' => ['red', 'green', 'blue'],
        ]);

        $visitedValues = [];

        $config->traverse(static function ($current) use (&$visitedValues): void {
            if (!is_array($current)) {
                $visitedValues[] = $current;
            }
        });

        $this->assertSame(['red', 'green', 'blue'], $visitedValues);
    }

    /**
     * Verifies traversal over nested arrays.
     * Collects key paths and values to ensure correct traversal.
     */
    public function testTraversalOverNestedArrays(): void
    {
        $config = new Config([
            'categories' => [
                'fruits' => ['apple', 'banana'],
                'vegetables' => ['carrot', 'lettuce'],
            ],
        ]);

        $visited = [];

        $config->traverse(static function ($current, $key, $config, $path) use (&$visited): void {
            if (!is_array($current)) {
                $visited[] = [
                    'keyPath' => $path,
                    'value' => $current,
                ];
            }
        });

        $expected = [
            ['keyPath' => ['categories', 'fruits', 0], 'value' => 'apple'],
            ['keyPath' => ['categories', 'fruits', 1], 'value' => 'banana'],
            ['keyPath' => ['categories', 'vegetables', 0], 'value' => 'carrot'],
            ['keyPath' => ['categories', 'vegetables', 1], 'value' => 'lettuce'],
        ];

        $this->assertSame($expected, $visited);
    }

    /**
     * Ensures that traversal handles an empty array without errors.
     * Confirms that the callback is not called.
     */
    public function testTraversalOverEmptyArray(): void
    {
        $config = new Config([]);

        $called = false;

        $config->traverse(static function ($current) use (&$called): void {
            $called = true;
        });

        $this->assertFalse($called);
    }

    /**
     * ==========================
     * Category 2: Modifying Values
     * ==========================
     */

    /**
     * Modifies even numbers by multiplying them by 10.
     * Verifies that the array reflects these changes.
     */
    public function testModifyScalarValues(): void
    {
        $config = new Config([
            'numbers' => [1, 2, 3, 4, 5],
        ]);

        $config->traverse(static function (&$current): void {
            if (\is_numeric($current) && $current % 2 === 0) {
                $current *= 10;
            }
        });

        $this->assertSame([1, 20, 3, 40, 5], $config->get('numbers'));
    }

    /**
     * Changes the name 'carrot' to 'cucumber' in nested arrays.
     * Confirms the modification is correctly applied.
     */
    public function testModifyNestedValues(): void
    {
        $config = new Config([
            'items' => [
                ['type' => 'fruit', 'name' => 'apple'],
                ['type' => 'vegetable', 'name' => 'carrot'],
                ['type' => 'fruit', 'name' => 'banana'],
            ],
        ]);

        $config->traverse(static function (&$current, $key): void {
            if ($key === 'name' && $current === 'carrot') {
                $current = 'cucumber';
            }
        });

        $expected = [
            'items' => [
                ['type' => 'fruit', 'name' => 'apple'],
                ['type' => 'vegetable', 'name' => 'cucumber'],
                ['type' => 'fruit', 'name' => 'banana'],
            ],
        ];

        $this->assertSame($expected, $config->toArray());
    }

    public function testAppendValueToNestedArray(): void
    {
        $config = new Config([
            'items' => [
                ['type' => 'fruit', 'name' => 'apple'],
                ['type' => 'vegetable', 'name' => 'carrot'],
                ['type' => 'fruit', 'name' => 'banana'],
            ],
        ]);

        $config->traverse(static function (&$current, $key): void {
            if ($key === 'items') {
                $current[] = ['type' => 'fruit', 'name' => 'orange'];
            }
        });

        $expected = [
            'items' => [
                ['type' => 'fruit', 'name' => 'apple'],
                ['type' => 'vegetable', 'name' => 'carrot'],
                ['type' => 'fruit', 'name' => 'banana'],
                ['type' => 'fruit', 'name' => 'orange'],
            ],
        ];

        $this->assertSame($expected, $config->toArray());
    }

    public function testModifyNodeWith2Callbacks(): void
    {
        $sut = $this->makeInstance([
            'root' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ]);

        $secondCallbackVisited = [];

        $this->assertSame('value1', $sut->get(['root', 'key1']), 'Key1 should exists');
        $this->assertSame('value2', $sut->get(['root', 'key2']), 'Key2 should exists');

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path): void {
                if ($path === ['root', 'key1']) {
                    $current = 'new value1';
                }

                if ($path === ['root', 'key2']) {
                    $config->set($path, 'new value2');
                }
            },
            function (&$current, $key, ConfigInterface $config, array $path) use (&$secondCallbackVisited): void {
                $pathString = \implode('.', $path);
                $pathString = '2° callback: ' . $pathString;

                if ($path === ['root', 'key1']) {
                    $secondCallbackVisited[$pathString] = $current;
                }

                if ($path === ['root', 'key2']) {
                    $secondCallbackVisited[$pathString] = $current;
                }
            }
        );

        $this->assertSame(
            'new value1',
            $secondCallbackVisited['2° callback: root.key1'],
            'Key1 should be modified'
        );

        $this->assertSame(
            'new value2',
            $secondCallbackVisited['2° callback: root.key2'],
            'Key2 should be modified'
        );

        $this->assertSame('new value1', $sut->get(['root', 'key1']), 'Key1 should be modified');
        $this->assertSame('new value2', $sut->get(['root', 'key2']), 'Key2 should be modified');
    }

    /**
     * Removes the number 3 from the Instance.
     * Checks that the array no longer contains the value 3.
     */
    public function testRemoveElementsUsingConfigInstance(): void
    {
        $config = new Config([
            'numbers' => [1, 2, 3, 4, 5],
        ]);

        $config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($current === 3) {
                $config->delete($path);
                return SignalCode::CONTINUE;
            }

            return SignalCode::NONE;
        });

        $this->assertSame([1, 2, 4, 5], \array_values($config->get('numbers')));
    }

    /**
     * ==========================
     * Category 3: Removing Empty Nodes
     * ==========================
     */

    /**
     * Removes 'item1' and ensures 'group' still exists with 'item2'.
     */
    public function testRemoveElementParentRemains(): void
    {
        $config = new Config([
            'group' => [
                'item1' => 'value1',
                'item2' => 'value2',
            ],
        ]);

        $config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($key === 'item1') {
                $config->delete($path);
                return SignalCode::CONTINUE;
            }

            return SignalCode::NONE;
        });

        $this->assertSame(['item2' => 'value2'], $config->get('group'));
    }

    /**
     * Removes all items under 'group'.
     * Confirms that 'group' is also removed when empty.
     */
    public function testRemoveAllChildrenParentRemoved(): void
    {
        $config = new Config([
            'group' => [
                [[[['item1' => 'value1']]]],
            ],
        ]);

        $count = 0;
        $config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) use (&$count): ?int {
            if ($key === 'item1') {
                $config->delete($path);

                while ($path !== []) {
                    \array_pop($path);
                    if ($config->get($path) !== []) {
                        break;
                    }

                    $config->delete($path);
                }

                return SignalCode::CONTINUE;
            }

            ++$count;
            return SignalCode::NONE;
        });

        $this->assertFalse($config->has('group.item1'));
        $this->assertNull($config->get('group'));
        $this->assertCount(0, $config);
        $array = $config->toArray();
        $this->assertSame([], $array);
    }

    /**
     * Removes 'item' deep in the nested structure.
     * Checks that all empty parent levels are removed.
     */
    public function testRemoveNestedElementsAndEmptyParents(): void
    {
        $config = new Config([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'item' => 'value',
                    ],
                ],
            ],
        ]);

        $config->traverse(static function (&$current, $key, ConfigInterface $config, $path): ?int {
            if ($key === 'item') {
                $config->delete($path);

                while ($path !== []) {
                    \array_pop($path);
                    if ($config->get($path) !== []) {
                        break;
                    }

                    $config->delete($path);
                }

                return SignalCode::CONTINUE;
            }

            return SignalCode::NONE;
        });

        $this->assertNull($config->get('level1'));
        $this->assertCount(0, $config);
        $array = $config->toArray();
        $this->assertSame([], $array);
    }

    /**
     * ==========================
     * Category 4: Edge Cases
     * ==========================
     */

    /**
     * Ensures that traversal handles scalar values at the root level.
     * Verifies that the callback is called with correct values.
     */
    public function testNonArrayValuesHandled(): void
    {
        $config = new Config([
            'scalarValue' => 'simple string',
        ]);

        $called = false;

        $config->traverse(function ($current, $key) use (&$called): void {
            $called = true;
            $this->assertSame('scalarValue', $key);
            $this->assertSame('simple string', $current);
        });

        $this->assertTrue($called);
    }

    public function testDataStructureWithFalseyValueAreNotDeleted(): void
    {
        $data = [
            'falseValue' => false,
            'nullValue' => null,
            'emptyString' => '',
            'zeroValue' => 0,
            'emptyArray' => [],
            'validArray' => ['key' => 'value'],
            'deeperArray' => [
                'key' => [
                    'key' => 'value',
                ],
            ],
        ];

        $config = new Config($data);

        $this->assertArrayHasKey('falseValue', $data);
        $this->assertArrayHasKey('nullValue', $data);

        $this->assertTrue($config->has('falseValue'));
        $this->assertFalse($config->has('nullValue'));
        $this->assertTrue($config->has('emptyArray'));

        $visited = [];

        $config->traverse(static function (
            $current,
            $key,
            ConfigInterface $config,
            array $path
        ) use (&$visited): void {
            $visited[] = $key;
        });

        $this->assertSame([
            'falseValue',
            'nullValue',
            'emptyString',
            'zeroValue',
            'emptyArray',
            'validArray',
            'key',
            'deeperArray',
            'key',
            'key',
        ], $visited);

        $this->assertTrue($config->has('falseValue'), 'False value should be present');
//      $this->assertTrue($config->has('nullValue'), 'Null value should be present');
        $this->assertTrue($config->has('emptyString'), 'Empty string should be present');
        $this->assertTrue($config->has('zeroValue'), 'Zero value should be present');
        $this->assertTrue($config->has('emptyArray'), 'Empty array should be present');
        $this->assertTrue($config->has('validArray'), 'Valid array should be present');
        $this->assertTrue($config->has('deeperArray'), 'Deeper array should be present');
    }

    /**
     * Verifies that keys with special characters and numeric keys are handled.
     * Checks that traversal includes these keys.
     */
    public function testKeysWithSpecialCharacters(): void
    {
        $config = new Config([
            'key.with.dots' => [
                'subkey' => 'value',
            ],
            123 => 'numeric key value',
        ]);

        $visitedKeys = [];

        $config->traverse(static function ($current, $key) use (&$visitedKeys): void {
            $visitedKeys[] = $key;
        });

        $this->assertContains('key.with.dots', $visitedKeys);
        $this->assertContains(123, $visitedKeys);
    }

    /**
     * ==========================
     * Category 5: Key Path Usage
     * ==========================
     */

    /**
     * Confirms that the key path is accurately constructed during traversal.
     * Uses the key path to verify the location of 'finalValue'.
     */
    public function testKeyPathIsCorrectlyBuilt(): void
    {
        $config = new Config([
            'level1' => [
                'level2' => [
                    'level3' => 'finalValue',
                ],
            ],
        ]);

        $config->traverse(function ($current, $key, $config, $path): void {
            if ($current === 'finalValue') {
                $this->assertSame(['level1', 'level2', 'level3'], $path);
            }
        });
    }

    /**
     * Uses the key path to target and modify a specific node.
     * Checks that the value is updated correctly.
     */
    public function testUseKeyPathForSpecificNodes(): void
    {
        $config = new Config([
            'settings' => [
                'option1' => 'value1',
                'option2' => 'value2',
            ],
        ]);

        $config->traverse(static function (&$current, $key, $config, $path): void {
            if (implode('.', $path) === 'settings.option2') {
                $current = 'updatedValue2';
            }
        });

        $this->assertSame('updatedValue2', $config->get('settings.option2'));
    }

    public function testWhichOfTheseIsDoneFirst(): void
    {
        $config = new Config([
            'items' => [
                'item1' => 'value1',
                'item2' => [
                    'subitem1' => 'sub value1',
                ],
            ],
        ]);

        $listOfCalls = [];
        $config->traverse(static function (&$current, $key) use (&$listOfCalls): void {
            if ($key === 'subitem1') {
                $listOfCalls[] = 'subitem1';
            }

            if ($key === 'item2') {
                $listOfCalls[] = 'item2';
            }

            if ($key === 'items') {
                $listOfCalls[] = 'items';
            }
        });

        $this->assertSame([
            'items',
            'item2',
            'subitem1',
        ], $listOfCalls);
    }

    public function testCallbackAddsNewKeys(): void
    {
        $config = new Config([
            'items' => [
                'item1' => 'value1',
            ],
        ]);

        /**
         * Explanation:
         * This example is a little bit counterintuitive
         * - The first condition will modify the 'item1' key.
         * - The 'item3' key will be added when the 'item1' key is modified with the value 'newValue3'.
         * - Because the 'item3' is added after the traversal of the 'item1' key, the 'item3' is available
         *   in the callback, so we can change the value of 'item3' to 'modifiedValue3'.
         *   The same is for the 'item2' key.
         * The flow is parent to child.
         */
        $config->traverse(static function (&$current, $key, Config $config, array $path): void {
            static $childModified = false;
            if ($key === 'item1') {
                $current = 'modifiedValue1';
                $config->set('items.item3', 'newValue3');
                $config->set('items.item2', 'newValue1');
            }

            if ($key === 'item3') {
                $current = 'modifiedValue3';
            }

            if ($key === 'item2') {
                $current = 'newValue2';
            }
        });

        $this->assertSame('modifiedValue1', $config->get('items.item1'));
        $this->assertSame('newValue2', $config->get('items.item2'));
        $this->assertSame('modifiedValue3', $config->get('items.item3'));
    }

    public function testPassConfigInstanceToTheCallback(): void
    {

        $config = new Config([
            'items' => [
                'item1' => 'value1',
            ],
        ]);

        $isCalled = false;
        /**
         * @var mixed $current
         * @var array-key|string|int $key
         * @var Config $config
         * @var array<array-key, string> $path
         */
        $config->traverse(function (
            &$current,
            $key,
            Config $config,
            array $path
        ) use (&$isCalled): void {
            $isCalled = true;
            $fullKeyPath = \implode('.', $path);
            if ($fullKeyPath === 'items') {
                $config->set($path, []);
                $config->set('items.item2', 'newValue2');
            }

            $this->assertNotNull($config->get($path));
        });

        $this->assertTrue($isCalled);
        $this->assertSame(['item2' => 'newValue2'], $config->get('items'));
        $this->assertSame('newValue2', $config->get('items.item2'));
        $this->assertTrue(true);
    }

    public function testDeleteElementWithTheInstance(): void
    {

        $config = new Config([
            'items' => [
                'item1' => 'value1',
                'item2' => 'value2',
            ],
        ]);

        $this->assertNotNull($config->get('items.item1'));
        $this->assertNotNull($config->get('items.item2'));

        /**
         * Explanation:
         * - The first condition will delete the 'item1' key.
         * - The second condition will set the 'item2' value to null.
         *
         * The result is that both 'item1' and 'item2' will be null.
         * If performance is a concern assigning null to the value
         * it is faster than deleting using the delete method.
         */
        $config->traverse(function (&$current, $key, Config $config, array $path): ?int {
            $fullKeyPath = \implode('.', $path);
            if ($fullKeyPath === 'items.item1') {
                $config->delete($path);
                return SignalCode::CONTINUE;
            }

            if ($fullKeyPath === 'items.item2') {
                $current = null;
            }

            return SignalCode::NONE;
        });

        $this->assertNull($config->get('items.item1'));
//        $this->assertTrue($config->has('items.item2'));
        $this->assertNull($config->get('items.item2'));
        $this->assertTrue(true);
    }

    private function getSampleArray(): array
    {
        return [
            'nodes' => [
                [
                    'element' => [
                        [
                            'properties' => [
                                'identifier' => 'path/to/resource1',
                            ],
                        ],
                        [
                            'properties' => [
                                'identifier' => 'path/to/resource2',
                            ],
                        ],
                    ],
                ],
                [
                    'element' => [
                        [
                            'properties' => [
                                'identifier' => 'path/to/resource3',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testRemoveElementsWithComplexCondition(): void
    {
        $config = new Config($this->getSampleArray());

        $config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($key === 'identifier' && ($current === 'path/to/resource2' || $current === 'path/to/resource1' )) {
                do {
                    $config->delete($path);
                    \array_pop($path);
                } while ($config->get($path) === []);

                return SignalCode::CONTINUE;
            }

            return SignalCode::NONE;
        });

        $expected = [
            'nodes' => [
                1 => [
                    'element' => [
                        [
                            'properties' => [
                                'identifier' => 'path/to/resource3',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $config->toArray());
    }

    public function testOrderOfTraversalExecution(): void
    {
        $config = new Config([
            'items' => [
                'item1' => [
                    'subitem1' => 'value1',
                    'subitem2' => 'value2',
                ],
            ],
        ]);

        $orderOfCallbacks = [];

        $config->traverse(static function ($current, $key) use (&$orderOfCallbacks): void {
            if ($key === 'subitem1') {
                $orderOfCallbacks[] = 'subitem1';
            }

            if ($key === 'subitem2') {
                $orderOfCallbacks[] = 'subitem2';
            }

            if ($key === 'item1') {
                $orderOfCallbacks[] = 'item1';
            }

            if ($key === 'items') {
                $orderOfCallbacks[] = 'items';
            }

            if ($key === null) {
                $orderOfCallbacks[] = 'root';
            }
        });

        $expected = ['items', 'item1', 'subitem1', 'subitem2'];
        $this->assertSame($expected, $orderOfCallbacks);
    }
}
