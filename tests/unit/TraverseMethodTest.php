<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;

class TraverseMethodTest extends TestCase
{
    protected function makeInstance(array $default = []): Config
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

        $config->traverse(static function ($value) use (&$visitedValues): void {
            if (!is_array($value)) {
                $visitedValues[] = $value;
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

        $config->traverse(static function ($value, $key, $config, $keyPath) use (&$visited): void {
            if (!is_array($value)) {
                $visited[] = [
                    'keyPath' => $keyPath,
                    'value' => $value,
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

        $config->traverse(static function ($value) use (&$called): void {
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

        $config->traverse(static function (&$value): void {
            if (\is_numeric($value) && $value % 2 === 0) {
                $value *= 10;
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

        $config->traverse(static function (&$value, $key): void {
            if ($key === 'name' && $value === 'carrot') {
                $value = 'cucumber';
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

    /**
     * Removes the number 3 by setting it to null.
     * Checks that the array no longer contains the value 3.
     */
    public function testRemoveElementsBySettingNull(): void
    {
        $config = new Config([
            'numbers' => [1, 2, 3, 4, 5],
        ]);

        $config->traverse(static function (&$value): void {
            if ($value === 3) {
                $value = null; // This should remove the element
            }
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

        $config->traverse(static function (&$value, $key): void {
            if ($key === 'item1') {
                $value = null;
            }
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
                'item1' => 'value1',
            ],
        ]);

        $config->traverse(static function (&$value): void {
            $value = null; // Remove all items
        });

        $this->assertNull($config->get('group'));
        $this->assertCount(0, $config);
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

        $config->traverse(static function (&$value, $key): void {
            if ($key === 'item') {
                $value = null;
            }
        });

        $this->assertNull($config->get('level1'));
        $this->assertCount(0, $config);
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

        $config->traverse(function ($value, $key) use (&$called): void {
            $called = true;
            $this->assertSame('scalarValue', $key);
            $this->assertSame('simple string', $value);
        });

        $this->assertTrue($called);
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

        $config->traverse(static function ($value, $key) use (&$visitedKeys): void {
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

        $config->traverse(function ($value, $key, $config, $keyPath): void {
            if ($value === 'finalValue') {
                $this->assertSame(['level1', 'level2', 'level3'], $keyPath);
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

        $config->traverse(static function (&$value, $key, $config, $keyPath): void {
            if (implode('.', $keyPath) === 'settings.option2') {
                $value = 'updatedValue2';
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
        $config->traverse(static function (&$current, $key, Config $config) use (&$listOfCalls): void {
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
            'subitem1', // This is the first call
            'item2', // This is the second call
            'items' // This is the last call
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
         *   Normally you should not need to do this, but it is possible.
         *   Remember to check the order of the callback execution, deeper levels are traversed first.
         *   Because of that adding an element when the traversal is finished you will not be able
         *   to modify the value of the new element in the same callback.
         */
        $config->traverse(static function (&$current, $key, Config $config): void {
            static $childModified = false;
            if ($key === 'item1') {
                $current = 'modifiedValue1';
                $config->set('items.item3', 'newValue3');
                $childModified = true;
            }

            if ($key === 'item3') {
                $current = 'modifiedValue3';
            }

            if ($key === 'items') {
                $current['item2'] = 'newValue1';
                if ($childModified) {
                    $current['item2'] = 'newValue2';
                }
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
         * @var array<array-key, string> $keyPath
         */
        $config->traverse(function (
            &$current,
            $key,
            Config $config,
            array $keyPath
        ) use (&$isCalled): void {
            $isCalled = true;
            $fullKeyPath = \implode('.', $keyPath);
            if ($fullKeyPath === 'items') {
                $config->set($keyPath, []);
                $config->set('items.item2', 'newValue2');
            }

            $this->assertNotNull($config->get($keyPath));
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
        $config->traverse(function (&$current, $key, Config $config, array $keyPath): void {
            $fullKeyPath = \implode('.', $keyPath);
            if ($fullKeyPath === 'items.item1') {
                $config->delete($keyPath);
            }
            if ($fullKeyPath === 'items.item2') {
                $current = null;
            }
        });

        $this->assertNull($config->get('items.item1'));
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

        $config->traverse(static function (&$value, $key): void {
            if ($key === 'identifier' && $value === 'path/to/resource2') {
                // Remove the parent 'properties' array
                $value = null;
                // Set the parent to null by navigating back in the key path
                // Since we cannot directly modify the parent here, we rely on the unset in traversal
            }
        });

        $expected = [
            'nodes' => [
                [
                    'element' => [
                        [
                            'properties' => [
                                'identifier' => 'path/to/resource1',
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

        $expected = ['subitem1', 'subitem2', 'item1', 'items'];
        $this->assertSame($expected, $orderOfCallbacks);
    }
}
