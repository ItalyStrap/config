<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait DeprecatedTrait
{
    /**
     * @deprecated
     */
    #[\Deprecated]
    public function push($key, $value): Config
    {
        return $this->add($key, $value);
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

    /**
     * @deprecated
     */
    #[\Deprecated]
    public function all(): array
    {
        return $this->getArrayCopy();
    }
}
