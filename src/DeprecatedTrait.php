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
            $this->deleteValue($this->storage, $this->buildLevels($k));
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

    /**
     * @deprecated This is a soft deprecation, I'm working on a different solution to dump a Json format,
     * in the meantime you can use:
     * (string)\json_encode(new Config(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    #[\Deprecated]
    public function toJson(): string
    {
        return (string)\json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
