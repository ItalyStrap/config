<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\Config\SignalCode;

final class TraverseMethodSignalTest extends TestCase
{
    private function makeInstance(array $default = []): ConfigInterface
    {
        return new Config($default);
    }

    private function getArray(): array
    {
        return [
            'root' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
                'key4' => [
                    'subkey1' => 'subvalue1',
                    'subkey2' => 'subvalue2',
                    'subkey3' => [
                        'deepkey1' => 'deepvalue1',
                        'deepkey2' => 'deepvalue2',
                        'deepkey3' => 'deepvalue3',
                    ],
                ],
                [
                    ['key' => 'value'],
                    ['key2' => 'value2'],
                    ['key3' => 'value3'],
                ],
            ],
        ];
    }

    public function testStopTraversalAtRootWithOneCallback(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $visitedPath = [];

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path) use (&$visitedPath): ?int {
                $visitedPath[] = $path;
                if ($path === ['root']) {
                    return SignalCode::STOP_TRAVERSAL;
                }

                return SignalCode::NONE;
            }
        );

        $this->assertSame([['root']], $visitedPath);
    }

    public function testStopTraversalAtRootWithTwoCallbacks(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $visitedPath = [];

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path) use (&$visitedPath): ?int {
                $visitedPath[] = $path;
                if ($path === ['root']) {
                    return SignalCode::STOP_TRAVERSAL;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                $this->fail('This callback should not be called');
            }
        );

        $this->assertSame([['root']], $visitedPath);
    }

    public function testStopTraversalAtRootWithThreeCallbacks(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $visitedPath = [];

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path) use (&$visitedPath): ?int {
                $visitedPath[] = $path;
                if ($path === ['root']) {
                    return SignalCode::STOP_TRAVERSAL;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                $this->fail('This 2° callback should not be called');
            },
            function (&$current, $key, ConfigInterface $config, array $path): void {
                $this->fail('This 3° callback should not be called');
            }
        );

        $this->assertSame([['root']], $visitedPath);
    }

    public function testSkipChildrenTraversalAtRootWithThreeCallbacks(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $firstVisitedPath = [];
        $secondVisitedPath = [];
        $thirdVisitedPath = [];

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path) use (&$firstVisitedPath): ?int {
                $firstVisitedPath[] = $path;
                if ($path === ['root']) {
                    return SignalCode::SKIP_CHILDREN;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path) use (&$secondVisitedPath): void {
                $secondVisitedPath[] = $path;
            },
            function (&$current, $key, ConfigInterface $config, array $path) use (&$thirdVisitedPath): void {
                $thirdVisitedPath[] = $path;
            }
        );

        $this->assertSame([['root']], $firstVisitedPath);
        $this->assertSame([['root']], $secondVisitedPath);
        $this->assertSame([['root']], $thirdVisitedPath);
    }

    public function testStopTraversalAtKey2WithThreeCallbacks(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $firstCallbackVisitedPath = [];
        $secondCallbackVisitedPath = [];
        $thirdCallbackVisitedPath = [];

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path) use (&$firstCallbackVisitedPath) {
                $firstCallbackVisitedPath[] = $path;
                return SignalCode::NONE;
            },
            static function (
                &$current,
                $key,
                ConfigInterface $config,
                array $path
            ) use (&$secondCallbackVisitedPath): ?int {
                $secondCallbackVisitedPath[] = $path;
                if ($path === ['root','key2']) {
                    return SignalCode::STOP_TRAVERSAL;
                }

                return SignalCode::NONE;
            },
            static function (&$current, $key, ConfigInterface $config, array $path) use (&$thirdCallbackVisitedPath) {
                $thirdCallbackVisitedPath[] = $path;

                return SignalCode::NONE;
            }
        );

        $this->assertSame([
            ['root'],
            ['root', 'key1'],
            ['root', 'key2'],
        ], $firstCallbackVisitedPath);

        $this->assertSame([
            ['root'],
            ['root', 'key1'],
            ['root', 'key2'],
        ], $secondCallbackVisitedPath);

        $this->assertSame([
            ['root'],
            ['root', 'key1'],
        ], $thirdCallbackVisitedPath);
    }

    public function testRemoveNodeWithOneCallback(): void
    {
        $sut = $this->makeInstance($this->getArray());

        $this->assertTrue($sut->has(['root', 'key2']), 'Key2 should exists');

        $sut->traverse(
            static function (&$current, $key, ConfigInterface $config, array $path): ?int {
                if ($path === ['root', 'key2']) {
                    return SignalCode::REMOVE_NODE;
                }

                return SignalCode::NONE;
            }
        );

        $this->assertFalse($sut->has(['root', 'key2']), 'Key2 should be removed');
    }

    public function testRemoveNodeWithTwoCallbacks(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $firsCallbackVisitedPath = [];
        $secondCallbackVisitedPath = [];

        $this->assertTrue($sut->has(['root', 'key4']), 'Key2 should exists');

        $sut->traverse(
            static function (
                &$current,
                $key,
                ConfigInterface $config,
                array $path
            ) use (&$firsCallbackVisitedPath): ?int {
                $firsCallbackVisitedPath[] = $path;
                if ($path === ['root', 'key4']) {
                    return SignalCode::REMOVE_NODE;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path) use (&$secondCallbackVisitedPath): void {
                $secondCallbackVisitedPath[] = $path;
            }
        );

        $this->assertFalse($sut->has(['root', 'key4']), 'Key2 should be removed');
        $this->assertSame('key4', $firsCallbackVisitedPath[4][1], 'Key4 should be visited');
        $this->assertNotSame('key4', $secondCallbackVisitedPath[4][1], 'Key4 should not be visited');
        $this->assertSame(0, $secondCallbackVisitedPath[4][1], 'The value should be the key 0');
        $this->assertFalse($sut->has(['root', 'key4']), 'Key2 should be removed');
        $this->assertCount(12, $firsCallbackVisitedPath, 'The first callback should be called 11 times');
        $this->assertCount(11, $secondCallbackVisitedPath, 'The second callback should be called 11 times');
    }

    public function testRemoveNodeWithThreeCallbacks(): void
    {
        $sut = $this->makeInstance($this->getArray());
        $firsCallbackVisitedPath = [];
        $secondCallbackVisitedPath = [];
        $thirdCallbackVisitedPath = [];

        $this->assertTrue($sut->has(['root', 'key4']), 'Key1 should exists');
        $this->assertTrue($sut->has(['root', 0]), 'Array should exists');

        $sut->traverse(
            static function (
                &$current,
                $key,
                ConfigInterface $config,
                array $path
            ) use (&$firsCallbackVisitedPath): void {
                $pathString = \implode('.', $path);
                $keyPathString = '1°: ' . $pathString;
                $firsCallbackVisitedPath[$keyPathString] = $path;
            },
            function (&$current, $key, ConfigInterface $config, array $path) use (&$secondCallbackVisitedPath): ?int {
                $pathString = \implode('.', $path);
                $keyPathString = '2°: ' . $pathString;
                $secondCallbackVisitedPath[$keyPathString] = $path;

                if ($path === ['root', 'key4']) {
                    return SignalCode::REMOVE_NODE;
                }

                if ($path === ['root', 0]) {
                    $config->delete($path);
                    return SignalCode::CONTINUE;
                }

                return SignalCode::NONE;
            },
            function (&$current, $key, ConfigInterface $config, array $path) use (&$thirdCallbackVisitedPath): void {
                $pathString = \implode('.', $path);
                $keyPathString = '3°: ' . $pathString;
                $thirdCallbackVisitedPath[$keyPathString] = $path;

                if ($path === ['root', 'key4']) {
                    $this->fail(\sprintf("This path: %s should not exists", $pathString));
                }

                if ($path === ['root', 0]) {
                    $this->fail(\sprintf("This path: %s should not exists", $pathString));
                }
            }
        );

        $this->assertFalse($sut->has(['root', 'key4']), 'Key4 should be removed');
        $this->assertFalse($sut->has(['root', 0]), 'Array should be removed');
        $this->assertCount(6, $firsCallbackVisitedPath, 'The first callback should be called 6 times');
        $this->assertCount(6, $secondCallbackVisitedPath, 'The second callback should be called 6 times');
        $this->assertCount(4, $thirdCallbackVisitedPath, 'The third callback should be called 4 times');
    }

    public function testUpdateDeprecatedKey(): void
    {
        $structure = [
            'oldKey' => 'value',
        ];
        $sut = $this->makeInstance($structure);

        $this->assertTrue($sut->has(['oldKey']), 'Old key should exists');
        $this->assertFalse($sut->has(['newKey']), 'New key should not exists');
        $this->assertCount(1, $sut, 'The array should have only one key');

        $updateDeprecatedKeys = static function (&$current, $key, ConfigInterface $config, array $path): ?int {
            if ($key === 'oldKey') {
                $config->set('newKey', $current);
                return SignalCode::REMOVE_NODE; // Remove the old key node
            }

            return SignalCode::NONE;
        };

        $validateValues = function (&$current, $key, ConfigInterface $config, array $path): void {
            // Validate the value based on a schema
            if (!\is_string($current)) {
                // Log or handle validation error
                $this->fail('Invalid value at ' . implode('.', $path));
            }
        };

        $logChanges = static function (&$current, $key, ConfigInterface $config, array $path): void {
            // Log the current value
            codecept_debug('Visited ' . \implode('.', $path) . ' with value: ' . \print_r($current, true));
        };

        $sut->traverse($updateDeprecatedKeys, $validateValues, $logChanges);

        $this->assertFalse($sut->has(['oldKey']), 'Old key should not exists');
        $this->assertTrue($sut->has(['newKey']), 'New key should exists');
        $this->assertCount(1, $sut, 'The array should have only one');
    }
}
