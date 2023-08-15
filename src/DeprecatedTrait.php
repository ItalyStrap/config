<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait DeprecatedTrait
{
    /**
     * @deprecated
     */
    #[\Deprecated]
    public function push($index, $value): Config
    {
        return $this->add($index, $value);
    }

    /**
     * @deprecated
     */
    #[\Deprecated]
    public function add($key, $value): Config
    {
        $this->set($key, $value);
        return $this;
    }

    /**
     * @deprecated
     */
    #[\Deprecated]
    public function remove(...$with_keys): Config
    {
        \array_walk(
            $with_keys,
            /**
             * @param mixed $keys
             * @psalm-suppress MixedArgumentTypeCoercion
             */
            [$this, 'removeIndexesFromStorage']
        );

        return $this;
    }

    /**
     * @deprecated
     */
    #[\Deprecated]
    private function removeIndexesFromStorage($keys): void
    {
        foreach ((array)$keys as $k) {
            $this->deleteValue($this->storage, $this->buildLevels((string)$k));
        }
    }
}
