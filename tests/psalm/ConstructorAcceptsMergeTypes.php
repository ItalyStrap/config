<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Psalm;

use ItalyStrap\Config\Config;

/**
 * Psalm fixture: the constructor accepts the same types as Config::merge().
 *
 * @see https://github.com/ItalyStrap/config/issues/16
 */
final class ConstructorAcceptsMergeTypes
{
    public function withIterator(): Config
    {
        return new Config(new \ArrayIterator(['key' => 'value']));
    }

    public function withIteratorAggregate(): Config
    {
        return new Config(new \ArrayObject(['key' => 'value']));
    }

    public function withStdClass(): Config
    {
        $object = new \stdClass();
        $object->key = 'value';

        return new Config($object);
    }
}
