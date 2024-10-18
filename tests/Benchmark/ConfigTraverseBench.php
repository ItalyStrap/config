<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigTraverseBench
{
    private Config $config;

    public function setUp(): void
    {
        $this->config = new Config([
            'root' => [
                'key' => 'value',
                'key2' => 'value2',
                'key3' => 'value3',
                'key4' => [
                    'key' => 'value',
                    'key2' => 'value2',
                    'key3' => [
                        'key' => 'value',
                        'key2' => 'value2',
                        'key3' => 'value3',
                    ],
                ],
                [
                    ['key' => 'value'],
                    ['key2' => 'value2'],
                    ['key3' => 'value3'],

                ],
            ],
        ]);
    }

    public function benchTraverseEmpty(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
        });
    }

    /**
     * ====================================
     * Traverse and modify value
     * ====================================
     */

    public function benchTraverseModifyValueOneLevel(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root']) {
                $current = 'new value';
            }
        });
    }

    public function benchTraverseModifyValueFourLevel(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $current = 'new value';
            }
        });
    }

    public function benchTraverseModifyValueFourLevelWithImplode(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if (\implode('.', $path) === 'root.key4.key3.key3') {
                $current = 'new value';
            }
        });
    }

    public function benchTraverseModifyValueOneLevelWithObject(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root']) {
                $config->set($path, 'new value');
            }
        });
    }

    public function benchTraverseModifyValueFourLevelWithObject(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $config->set($path, 'new value');
            }
        });
    }

    /**
     * ====================================
     * Traverse and delete value
     * ====================================
     */

    public function benchTraverseDeleteValueOneLevel(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root']) {
                $current = null;
            }
        });
    }

    public function benchTraverseDeleteValueFourLevel(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $current = null;
            }
        });
    }

    public function benchTraverseDeleteValueFourLevelWithImplode(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if (\implode('.', $path) === 'root.key4.key3.key3') {
                $current = null;
            }
        });
    }

    public function benchTraverseDeleteValueOneLevelWithObject(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root']) {
                $config->delete($path);
            }
        });
    }

    public function benchTraverseDeleteValueFourLevelWithObject(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path) {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $config->delete($path);
            }
        });
    }
}
