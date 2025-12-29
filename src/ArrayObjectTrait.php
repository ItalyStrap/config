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
    #[\ReturnTypeWillChange]
    public function offsetGet($index)
    {
        return $this->get($index);
    }

    /**
     * @param TKey $index
     * @param TValue $newval
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($index, $newval)
    {
        $this->set($index, $newval);
    }

    /**
     * @param TKey $index
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($index)
    {
        $this->delete($index);
    }

    /**
     * @param array<TKey, TValue> $array
     * @return array<TKey, TValue>
     */
    public function exchangeArray($array): array
    {
        $oldData = $this->storage;

        // Replace storage with new array
        $this->storage = $array;
        parent::exchangeArray($this->storage);

        return $oldData;
    }

    public function __clone()
    {
        $this->storage = [];
        parent::exchangeArray($this->storage);
    }
}
