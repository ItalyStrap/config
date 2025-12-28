<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;

/**
 * Benchmarks for node manipulation methods: appendTo, prependTo, insertAt, deleteFrom.
 *
 * @BeforeMethods({"setUp"})
 */
final class ConfigNodeManipulationBench
{
    private Config $config;

    public function setUp(): void
    {
        $this->config = new Config([
            'plugins' => ['plugin1', 'plugin2', 'plugin3'],
            'settings' => [
                'features' => ['feature1', 'feature2'],
                'modules' => ['module1', 'module2', 'module3', 'module4', 'module5'],
            ],
            'deeply' => [
                'nested' => [
                    'path' => [
                        'items' => ['item1', 'item2'],
                    ],
                ],
            ],
            'empty_list' => [],
            'large_list' => \range(1, 100),
        ]);
    }

    // =========================================================================
    // appendTo benchmarks
    // =========================================================================

    public function benchAppendToExistingList(): void
    {
        $this->config->appendTo('plugins', 'plugin4');
    }

    public function benchAppendToEmptyList(): void
    {
        $this->config->appendTo('empty_list', 'value');
    }

    public function benchAppendToNestedPath(): void
    {
        $this->config->appendTo('settings.features', 'feature3');
    }

    public function benchAppendToDeeplyNestedPath(): void
    {
        $this->config->appendTo('deeply.nested.path.items', 'item3');
    }

    public function benchAppendMultipleValuesToList(): void
    {
        $this->config->appendTo('plugins', ['plugin4', 'plugin5', 'plugin6']);
    }

    public function benchAppendToLargeList(): void
    {
        $this->config->appendTo('large_list', 101);
    }

    public function benchAppendToNonExistentKeyCreatesArray(): void
    {
        $this->config->appendTo('new_key', 'value');
    }

    // =========================================================================
    // prependTo benchmarks
    // =========================================================================

    public function benchPrependToExistingList(): void
    {
        $this->config->prependTo('plugins', 'plugin0');
    }

    public function benchPrependToEmptyList(): void
    {
        $this->config->prependTo('empty_list', 'value');
    }

    public function benchPrependToNestedPath(): void
    {
        $this->config->prependTo('settings.features', 'feature0');
    }

    public function benchPrependToDeeplyNestedPath(): void
    {
        $this->config->prependTo('deeply.nested.path.items', 'item0');
    }

    public function benchPrependMultipleValuesToList(): void
    {
        $this->config->prependTo('plugins', ['plugin-1', 'plugin-2', 'plugin-3']);
    }

    public function benchPrependToLargeList(): void
    {
        $this->config->prependTo('large_list', 0);
    }

    // =========================================================================
    // insertAt benchmarks
    // =========================================================================

    public function benchInsertAtBeginning(): void
    {
        $this->config->insertAt('plugins', 'plugin0', 0);
    }

    public function benchInsertAtMiddle(): void
    {
        $this->config->insertAt('plugins', 'plugin1.5', 1);
    }

    public function benchInsertAtEnd(): void
    {
        $this->config->insertAt('plugins', 'plugin4', 3);
    }

    public function benchInsertAtNestedPath(): void
    {
        $this->config->insertAt('settings.modules', 'module2.5', 2);
    }

    public function benchInsertAtDeeplyNestedPath(): void
    {
        $this->config->insertAt('deeply.nested.path.items', 'item1.5', 1);
    }

    public function benchInsertMultipleValuesAtPosition(): void
    {
        $this->config->insertAt('plugins', ['pluginA', 'pluginB'], 1);
    }

    public function benchInsertAtMiddleOfLargeList(): void
    {
        $this->config->insertAt('large_list', 999, 50);
    }

    // =========================================================================
    // deleteFrom benchmarks
    // =========================================================================

    public function benchDeleteFromExistingList(): void
    {
        $this->config->deleteFrom('plugins', 'plugin2');
    }

    public function benchDeleteFromNestedPath(): void
    {
        $this->config->deleteFrom('settings.features', 'feature1');
    }

    public function benchDeleteFromDeeplyNestedPath(): void
    {
        $this->config->deleteFrom('deeply.nested.path.items', 'item1');
    }

    public function benchDeleteMultipleValuesFromList(): void
    {
        $this->config->deleteFrom('plugins', ['plugin1', 'plugin3']);
    }

    public function benchDeleteNonExistentValue(): void
    {
        $this->config->deleteFrom('plugins', 'non-existent');
    }

    public function benchDeleteFromNonExistentKey(): void
    {
        $this->config->deleteFrom('non_existent_key', 'value');
    }

    public function benchDeleteFromLargeList(): void
    {
        $this->config->deleteFrom('large_list', 50);
    }

    public function benchDeleteFromLargeListMultipleValues(): void
    {
        $this->config->deleteFrom('large_list', [10, 30, 50, 70, 90]);
    }
}
