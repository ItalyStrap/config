<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\Config\SignalCode;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigTraverseMethodBench
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

    public function benchEmptyCallback(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): void {
        });
    }

    /**
     * ====================================
     * Traverse and modify node
     * ====================================
     */

    public function benchModifyNode1Level(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): void {
            if ($path === ['root']) {
                $current = 'new value';
            }
        });
    }

    public function benchModifyNode1LevelWithObj(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): void {
            if ($path === ['root']) {
                $config->set($path, 'new value');
            }
        });
    }

    public function benchModifyNode4Level(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): void {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $current = 'new value';
            }
        });
    }

    public function benchModifyNode4LevelWithObj(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): void {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $config->set($path, 'new value');
            }
        });
    }

    public function benchModifyNode4LevelReturn1(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $current = 'new value';
                return SignalCode::STOP_TRAVERSAL;
            }

            return SignalCode::NONE;
        });
    }

    public function benchModifyNode4LevelWithObjReturn1(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $config->set($path, 'new value');
                return SignalCode::STOP_TRAVERSAL;
            }

            return SignalCode::NONE;
        });
    }

    public function benchModifyNode4LevelWithImplode(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): void {
            if (\implode('.', $path) === 'root.key4.key3.key3') {
                $current = 'new value';
            }
        });
    }

    /**
     * ====================================
     * Traverse and delete node
     * ====================================
     */

    public function benchDeleteNode1Level(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root']) {
                return SignalCode::REMOVE_NODE;
            }

            return SignalCode::NONE;
        });
    }

    public function benchDeleteNode1LevelWithObj(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root']) {
                $config->delete($path);
                return SignalCode::CONTINUE;
            }

            return SignalCode::NONE;
        });
    }

    public function benchDeleteNode4Level(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                return SignalCode::REMOVE_NODE;
            }

            return SignalCode::NONE;
        });
    }

    public function benchDeleteNode4LevelWithObj(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $config->delete($path);
                return SignalCode::CONTINUE;
            }

            return SignalCode::NONE;
        });
    }

    public function benchDeleteNode4LevelWithObjReturn1(): void
    {
        $this->config->traverse(static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($path === ['root', 'key4', 'key3', 'key3']) {
                $config->delete($path);
                return SignalCode::STOP_TRAVERSAL;
            }

            return SignalCode::NONE;
        });
    }

    /**
     * ====================================
     * Traverse with multiple callbacks
     * ====================================
     */

    public function benchStopTraversalAtRootWith3Callbacks(): void
    {
        $this->config->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path): ?int {
                if ($path === ['root']) {
                    return SignalCode::STOP_TRAVERSAL;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                // Do nothing
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                // Do nothing
            }
        );
    }

    public function benchSkipChildrenTraversalAtRootWith3Callbacks(): void
    {
        $this->config->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path): ?int {
                if ($path === ['root']) {
                    return SignalCode::SKIP_CHILDREN;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                // Do nothing
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                // Do nothing
            }
        );
    }
}
