<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

/**
 * @template TKey as array-key
 * @template TValue
 */
trait ArrayObjectTrait
{
    /**
     * @param TKey $index
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function offsetExists($index): bool
    {
        return $this->has($index);
    }

    /**
     * @param TKey $index
     * @return TValue
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function offsetGet($index)
    {
        codecept_debug($index);
        return $this->get($index);
    }

    /**
     * @param TKey $index
     * @param TValue $newval
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function offsetSet($index, $newval)
    {
        $this->set($index, $newval);
    }

    /**
     * @param TKey $index
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function offsetUnset($index)
    {
        $this->delete($index);
    }

    public function count(): int
    {
        parent::exchangeArray($this->storage);
        return parent::count();
    }

    public function getArrayCopy(): array
    {
        parent::exchangeArray($this->storage);
        return parent::getArrayCopy();
    }

    public function __clone()
    {
        $this->storage = [];
        parent::exchangeArray($this->storage);
    }
}
