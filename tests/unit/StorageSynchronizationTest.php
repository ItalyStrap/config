<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;

/**
 * Test class to verify storage synchronization behavior between
 * internal $storage array and parent ArrayObject.
 */
class StorageSynchronizationTest extends TestCase
{
    private function makeInstance(array $default = []): ConfigInterface
    {
        return new Config($default);
    }

    // =========================================================================
    // SCENARIO 0: Existing methods set() and delete() - pre-existing behavior
    // =========================================================================

    public function testForeachAfterSetReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        $config->set('key2', 'value2');

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('key2', $result);
        $this->assertSame('value2', $result['key2']);
    }

    public function testForeachAfterDeleteReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1', 'key2' => 'value2']);

        $config->delete('key1');

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayNotHasKey('key1', $result);
    }

    public function testPropertyAccessAfterSetReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        $config->set('key2', 'value2');

        /** @var string $value */
        $value = $config->key2;

        $this->assertSame('value2', $value);
    }

    public function testPropertyAccessAfterDeleteReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1', 'key2' => 'value2']);

        $config->delete('key1');

        // Property access should return null for deleted key
        /** @var mixed $value */
        $value = $config->key1;

        $this->assertNull($value);
    }

    public function testGetIteratorAfterSetReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        $config->set('key2', 'value2');

        $iterator = $config->getIterator();
        $result = \iterator_to_array($iterator);

        $this->assertArrayHasKey('key2', $result);
        $this->assertSame('value2', $result['key2']);
    }

    public function testGetIteratorAfterDeleteReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1', 'key2' => 'value2']);

        $config->delete('key1');

        $iterator = $config->getIterator();
        $result = \iterator_to_array($iterator);

        $this->assertArrayNotHasKey('key1', $result);
    }

    public function testCountAfterSetReturnsUpdatedCount(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        $config->set('key2', 'value2');

        $this->assertCount(2, $config);
    }

    public function testCountAfterDeleteReturnsUpdatedCount(): void
    {
        $config = $this->makeInstance(['key1' => 'value1', 'key2' => 'value2']);

        $config->delete('key1');

        $this->assertCount(1, $config);
    }

    public function testGetArrayCopyAfterSetReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        $config->set('key2', 'value2');

        $copy = $config->getArrayCopy();

        $this->assertArrayHasKey('key2', $copy);
        $this->assertSame('value2', $copy['key2']);
    }

    public function testGetArrayCopyAfterDeleteReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1', 'key2' => 'value2']);

        $config->delete('key1');

        $copy = $config->getArrayCopy();

        $this->assertArrayNotHasKey('key1', $copy);
    }

    public function testJsonSerializeAfterSetReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        $config->set('key2', 'value2');

        $json = \json_encode($config);
        $decoded = \json_decode($json, true);

        $this->assertArrayHasKey('key2', $decoded);
        $this->assertSame('value2', $decoded['key2']);
    }

    public function testJsonSerializeAfterDeleteReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['key1' => 'value1', 'key2' => 'value2']);

        $config->delete('key1');

        $json = \json_encode($config);
        $decoded = \json_decode($json, true);

        $this->assertArrayNotHasKey('key1', $decoded);
    }

    // =========================================================================
    // SCENARIO 1: Iteration after node manipulation methods
    // =========================================================================

    public function testForeachAfterAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        // Access via foreach (uses getIterator() internally)
        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('plugins', $result);
        $this->assertContains('plugin3', $result['plugins']);
    }

    public function testForeachAfterPrependToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->prependTo('plugins', 'plugin0');

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('plugins', $result);
        $this->assertSame('plugin0', $result['plugins'][0]);
    }

    public function testForeachAfterInsertAtReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin3']]);

        $config->insertAt('plugins', 'plugin2', 1);

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('plugins', $result);
        $this->assertSame('plugin2', $result['plugins'][1]);
    }

    public function testForeachAfterDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2', 'plugin3']]);

        $config->deleteFrom('plugins', 'plugin2');

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('plugins', $result);
        $this->assertNotContains('plugin2', $result['plugins']);
    }

    /**
     * Test that covers line 242 in deleteFrom: when all elements are removed,
     * the key is deleted entirely and foreach reflects this change.
     */
    public function testForeachAfterDeleteFromRemovesEntireKeyWhenArrayBecomesEmpty(): void
    {
        $config = $this->makeInstance([
            'key1' => 'value1',
            'plugins' => ['plugin1']
        ]);

        // This triggers the $oldValue === [] branch (line 242)
        $config->deleteFrom('plugins', 'plugin1');

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayNotHasKey('plugins', $result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('key1', $result);
    }

    // =========================================================================
    // SCENARIO 2: Property access (ARRAY_AS_PROPS) after node manipulation
    // =========================================================================

    public function testPropertyAccessAfterAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        // Access via object property ($config->plugins)
        /** @var array $plugins */
        $plugins = $config->plugins;

        $this->assertIsArray($plugins);
        $this->assertContains('plugin3', $plugins);
    }

    public function testPropertyAccessAfterPrependToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->prependTo('plugins', 'plugin0');

        /** @var array $plugins */
        $plugins = $config->plugins;

        $this->assertIsArray($plugins);
        $this->assertSame('plugin0', $plugins[0]);
    }

    public function testPropertyAccessAfterInsertAtReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin3']]);

        $config->insertAt('plugins', 'plugin2', 1);

        /** @var array $plugins */
        $plugins = $config->plugins;

        $this->assertIsArray($plugins);
        $this->assertSame('plugin2', $plugins[1]);
    }

    public function testPropertyAccessAfterDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2', 'plugin3']]);

        $config->deleteFrom('plugins', 'plugin2');

        /** @var array $plugins */
        $plugins = $config->plugins;

        $this->assertIsArray($plugins);
        $this->assertNotContains('plugin2', $plugins);
    }

    // =========================================================================
    // SCENARIO 3: ArrayAccess after node manipulation
    // =========================================================================

    public function testArrayAccessAfterAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        // Access via ArrayAccess ($config['plugins'])
        /** @var array $plugins */
        $plugins = $config['plugins'];

        $this->assertIsArray($plugins);
        $this->assertContains('plugin3', $plugins);
    }

    public function testArrayAccessAfterDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2', 'plugin3']]);

        $config->deleteFrom('plugins', 'plugin2');

        /** @var array $plugins */
        $plugins = $config['plugins'];

        $this->assertIsArray($plugins);
        $this->assertNotContains('plugin2', $plugins);
    }

    // =========================================================================
    // SCENARIO 4: count() after node manipulation
    // =========================================================================

    public function testCountAfterAppendToNewKeyReturnsUpdatedCount(): void
    {
        $config = $this->makeInstance(['key1' => 'value1']);

        // appendTo creates a new key if it doesn't exist
        $config->appendTo('newKey', 'value');

        $this->assertCount(2, $config);
    }

    public function testCountAfterDeleteFromRemovesKeyReturnsUpdatedCount(): void
    {
        $config = $this->makeInstance([
            'key1' => 'value1',
            'plugins' => ['plugin1']
        ]);

        // deleteFrom removes the key when the array becomes empty
        $config->deleteFrom('plugins', 'plugin1');

        $this->assertCount(1, $config);
    }

    // =========================================================================
    // SCENARIO 5: getArrayCopy() / toArray() after node manipulation
    // =========================================================================

    public function testGetArrayCopyAfterAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        $copy = $config->getArrayCopy();

        $this->assertArrayHasKey('plugins', $copy);
        $this->assertContains('plugin3', $copy['plugins']);
    }

    public function testToArrayAfterDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2', 'plugin3']]);

        $config->deleteFrom('plugins', 'plugin2');

        $array = $config->toArray();

        $this->assertArrayHasKey('plugins', $array);
        $this->assertNotContains('plugin2', $array['plugins']);
    }

    // =========================================================================
    // SCENARIO 6: getIterator() after node manipulation
    // =========================================================================

    public function testGetIteratorAfterAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        $iterator = $config->getIterator();
        $result = \iterator_to_array($iterator);

        $this->assertArrayHasKey('plugins', $result);
        $this->assertContains('plugin3', $result['plugins']);
    }

    public function testGetIteratorAfterDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2', 'plugin3']]);

        $config->deleteFrom('plugins', 'plugin2');

        $iterator = $config->getIterator();
        $result = \iterator_to_array($iterator);

        $this->assertArrayHasKey('plugins', $result);
        $this->assertNotContains('plugin2', $result['plugins']);
    }

    // =========================================================================
    // SCENARIO 7: jsonSerialize() after node manipulation
    // =========================================================================

    public function testJsonSerializeAfterAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        $json = \json_encode($config);
        $decoded = \json_decode($json, true);

        $this->assertArrayHasKey('plugins', $decoded);
        $this->assertContains('plugin3', $decoded['plugins']);
    }

    public function testJsonSerializeAfterDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2', 'plugin3']]);

        $config->deleteFrom('plugins', 'plugin2');

        $json = \json_encode($config);
        $decoded = \json_decode($json, true);

        $this->assertArrayHasKey('plugins', $decoded);
        $this->assertNotContains('plugin2', $decoded['plugins']);
    }

    // =========================================================================
    // SCENARIO 8: Multiple operations in sequence
    // =========================================================================

    public function testMultipleNodeManipulationsFollowedByIteration(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin2']]);

        $config->prependTo('plugins', 'plugin1');
        $config->appendTo('plugins', 'plugin3');
        $config->insertAt('plugins', 'plugin2.5', 2);

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame(['plugin1', 'plugin2', 'plugin2.5', 'plugin3'], $result['plugins']);
    }

    public function testMixedNodeManipulationsAndStandardSetOperations(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1']]);

        $config->appendTo('plugins', 'plugin2');
        $config->set('newKey', 'newValue');
        $config->deleteFrom('plugins', 'plugin1');

        $result = $config->toArray();

        $this->assertSame(['plugin2'], $result['plugins']);
        $this->assertSame('newValue', $result['newKey']);
    }

    // =========================================================================
    // SCENARIO 9: Nested path operations and iteration
    // =========================================================================

    public function testForeachAfterNestedAppendToReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'settings' => [
                'features' => ['feature1', 'feature2']
            ]
        ]);

        $config->appendTo('settings.features', 'feature3');

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('settings', $result);
        $this->assertContains('feature3', $result['settings']['features']);
    }

    public function testPropertyAccessAfterNestedDeleteFromReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'settings' => [
                'features' => ['feature1', 'feature2', 'feature3']
            ]
        ]);

        $config->deleteFrom('settings.features', 'feature2');

        /** @var array $settings */
        $settings = $config->settings;

        $this->assertIsArray($settings);
        $this->assertNotContains('feature2', $settings['features']);
    }

    // =========================================================================
    // SCENARIO 10: Clone behavior after node manipulation
    // =========================================================================

    public function testCloneAfterNodeManipulationCreatesIndependentCopy(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        $cloned = clone $config;

        // Cloned should be empty according to __clone implementation
        $this->assertCount(0, $cloned);

        // Original should still have data
        $this->assertCount(1, $config);
        $this->assertContains('plugin3', $config->get('plugins'));
    }

    // =========================================================================
    // SCENARIO 11: exchangeArray behavior
    // =========================================================================

    public function testExchangeArrayAfterNodeManipulationReplacesData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        // exchangeArray should work with the updated data
        $oldData = $config->exchangeArray(['newKey' => 'newValue']);

        $this->assertSame(['plugins' => ['plugin1', 'plugin2', 'plugin3']], $oldData);

        $this->assertSame('newValue', $config->get('newKey'));
        $this->assertNull($config->get('plugins'));
    }

    public function testExchangeArrayReturnsAllOldData(): void
    {
        $config = $this->makeInstance([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ]);

        $oldData = $config->exchangeArray(['newKey' => 'newValue']);

        // Must return ALL old keys, not just the first one
        $this->assertCount(3, $oldData);
        $this->assertArrayHasKey('key1', $oldData);
        $this->assertArrayHasKey('key2', $oldData);
        $this->assertArrayHasKey('key3', $oldData);
        $this->assertSame('value1', $oldData['key1']);
        $this->assertSame('value2', $oldData['key2']);
        $this->assertSame('value3', $oldData['key3']);
    }

    public function testForeachAfterExchangeArrayReturnsNewData(): void
    {
        $config = $this->makeInstance(['oldKey' => 'oldValue']);

        $config->exchangeArray(['newKey' => 'newValue', 'anotherKey' => 'anotherValue']);

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayNotHasKey('oldKey', $result);
        $this->assertArrayHasKey('newKey', $result);
        $this->assertArrayHasKey('anotherKey', $result);
        $this->assertSame('newValue', $result['newKey']);
    }

    // =========================================================================
    // SCENARIO 12: Direct ArrayObject method access
    // =========================================================================

    public function testOffsetExistsAfterAppendToNewKey(): void
    {
        $config = $this->makeInstance(['existingKey' => 'value']);

        $config->appendTo('newPlugins', 'plugin1');

        $this->assertTrue($config->offsetExists('newPlugins'));
        $this->assertTrue(isset($config['newPlugins']));
    }

    public function testOffsetGetAfterNodeManipulation(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1']]);

        $config->appendTo('plugins', 'plugin2');

        $plugins = $config->offsetGet('plugins');

        $this->assertIsArray($plugins);
        $this->assertContains('plugin2', $plugins);
    }

    // =========================================================================
    // SCENARIO 13: serialize/unserialize behavior
    // =========================================================================

    public function testSerializeAfterNodeManipulationPreservesData(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        $serialized = \serialize($config);
        /** @var Config $unserialized */
        $unserialized = \unserialize($serialized);

        $plugins = $unserialized->get('plugins');

        $this->assertIsArray($plugins);
        $this->assertContains('plugin3', $plugins);
    }

    // =========================================================================
    // SCENARIO 14: var_export / __set_state behavior (if implemented)
    // =========================================================================

    public function testVarExportAfterNodeManipulation(): void
    {
        $config = $this->makeInstance(['plugins' => ['plugin1', 'plugin2']]);

        $config->appendTo('plugins', 'plugin3');

        // var_export uses getArrayCopy internally for ArrayObject
        $exported = \var_export($config, true);

        $this->assertStringContainsString('plugin3', $exported);
    }

    // =========================================================================
    // SCENARIO 15: traverse() method and storage synchronization
    // =========================================================================

    public function testForeachAfterTraverseModifiesValueReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'key1' => 'value1',
            'key2' => 'value2'
        ]);

        // Modify values via traverse
        $config->traverse(static function (&$value, $key): void {
            if (\is_string($value)) {
                $value = \strtoupper($value);
            }
        });

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame('VALUE1', $result['key1']);
        $this->assertSame('VALUE2', $result['key2']);
    }

    public function testForeachAfterTraverseRemovesNodeReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ]);

        // Remove a node via traverse using SignalCode::REMOVE_NODE
        $config->traverse(static function ($value, $key): ?int {
            if ($key === 'key2') {
                return \ItalyStrap\Config\SignalCode::REMOVE_NODE;
            }
            return null;
        });

        $result = [];
        foreach ($config as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayNotHasKey('key2', $result);
        $this->assertArrayHasKey('key3', $result);
    }

    public function testPropertyAccessAfterTraverseModifiesValueReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'plugins' => ['plugin1', 'plugin2']
        ]);

        // Modify nested values via traverse
        $config->traverse(static function (&$value): void {
            if ($value === 'plugin1') {
                $value = 'modified_plugin1';
            }
        });

        /** @var array $plugins */
        $plugins = $config->plugins;

        $this->assertContains('modified_plugin1', $plugins);
    }

    public function testCountAfterTraverseRemovesNodeReturnsUpdatedCount(): void
    {
        $config = $this->makeInstance([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3'
        ]);

        $config->traverse(static function ($value, $key): ?int {
            if ($key === 'key2') {
                return \ItalyStrap\Config\SignalCode::REMOVE_NODE;
            }
            return null;
        });

        $this->assertCount(2, $config);
    }

    public function testGetArrayCopyAfterTraverseModifiesValueReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'nested' => [
                'a' => 1,
                'b' => 2
            ]
        ]);

        $config->traverse(static function (&$value): void {
            if (\is_int($value)) {
                $value *= 10;
            }
        });

        $copy = $config->getArrayCopy();

        $this->assertSame(10, $copy['nested']['a']);
        $this->assertSame(20, $copy['nested']['b']);
    }

    public function testJsonSerializeAfterTraverseRemovesNodeReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'keep' => 'value',
            'remove' => 'this'
        ]);

        $config->traverse(static function ($value, $key): ?int {
            if ($key === 'remove') {
                return \ItalyStrap\Config\SignalCode::REMOVE_NODE;
            }
            return null;
        });

        $json = \json_encode($config);
        $decoded = \json_decode($json, true);

        $this->assertArrayHasKey('keep', $decoded);
        $this->assertArrayNotHasKey('remove', $decoded);
    }

    public function testArrayAccessAfterTraverseReturnsUpdatedData(): void
    {
        $config = $this->makeInstance([
            'items' => ['a', 'b', 'c']
        ]);

        $config->traverse(static function (&$value): void {
            if ($value === 'b') {
                $value = 'B_MODIFIED';
            }
        });

        $items = $config['items'];

        $this->assertContains('B_MODIFIED', $items);
    }
}
