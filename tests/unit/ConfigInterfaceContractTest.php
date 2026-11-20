<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;
use ItalyStrap\Config\ConfigInterface;

/**
 * ConfigInterface widens the string keys of StoreInterface, so callers typed against
 * the interface can use array paths, whose segments are never split on the delimiter.
 */
class ConfigInterfaceContractTest extends TestCase
{
    /**
     * @return iterable<string, array{0: string}>
     */
    public function keyMethodsProvider(): iterable
    {
        yield 'get' => ['get'];
        yield 'set' => ['set'];
    }

    /**
     * @dataProvider keyMethodsProvider
     */
    public function testInterfaceDeclaresKeyWithoutStringRestriction(string $method): void
    {
        $key = (new \ReflectionMethod(ConfigInterface::class, $method))->getParameters()[0];

        $this->assertSame(
            ConfigInterface::class,
            $key->getDeclaringClass() !== null ? $key->getDeclaringClass()->getName() : null,
            \sprintf('%s() must be declared by ConfigInterface', $method)
        );
        $this->assertNull($key->getType(), \sprintf('%s() $key must accept array paths', $method));
    }

    public function testArrayPathKeepsKeysContainingTheDelimiter(): void
    {
        $config = $this->makeInstance();

        $config->set(['some.dotted.key'], 'value');

        $this->assertSame(['some.dotted.key' => 'value'], $config->toArray());
        $this->assertSame('value', $config->get(['some.dotted.key']));
    }

    private function makeInstance(): ConfigInterface
    {
        return new Config();
    }
}
