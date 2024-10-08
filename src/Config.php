<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

use ArrayObject;
use ItalyStrap\Storage\MultipleTrait;
use ItalyStrap\Storage\SetMultipleStoreTrait;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements \ItalyStrap\Config\ConfigInterface<TKey,TValue>
 * @template-extends \ArrayObject<TKey,TValue>
 * @psalm-suppress DeprecatedInterface
 */
class Config extends ArrayObject implements ConfigInterface, \JsonSerializable
{
    /**
     * @use \ItalyStrap\Config\ArrayObjectTrait<TKey,TValue>
     */
    use ArrayObjectTrait;
    use AccessValueInArrayWithNotationTrait;
    use DeprecatedTrait;
    use MultipleTrait;
    use SetMultipleStoreTrait;

    /**
     * @var array<TKey, TValue>
     */
    private array $storage = [];

    /**
     * @var ?TValue
     */
    private $temp = null;

    /**
     * @var ?TValue
     */
    private $default = null;

    private string $delimiter = '.';

    /**
     * Config constructor
     *
     * @param array<TKey, TValue> $config
     * @param array<TKey, TValue> $default
     */
    public function __construct($config = [], $default = [])
    {
        $this->merge($default, $config);
        parent::__construct($this->storage, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @param TKey|string|int|array $key
     * @param TValue $default
     * @return TValue
     */
    public function get($key, $default = null)
    {
        $this->default = $default;

        if (!$this->has($key)) {
            return $default;
        }

        // The class::temp variable is always set by the class::has() method
        return $this->temp;
    }

    /**
     * @param TKey|string|int|array $key
     */
    public function has($key): bool
    {
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->temp = $this->findValue(
            $this->storage,
            $this->buildLevels($key),
            $this->default
        );
        $this->default = null;
        return isset($this->temp);
    }

    /**
     * @param TKey|string|int|array $key
     * @param TValue|mixed $value
     */
    public function set($key, $value): bool
    {
        return $this->insertValue(
            $this->storage,
            $this->buildLevels($key),
            $value
        );
    }

    public function update($key, $value): bool
    {
        return $this->set($key, $value);
    }

    /**
     * @param TKey|string|int|array $key
     */
    public function delete($key): bool
    {
        return $this->deleteValue($this->storage, $this->buildLevels($key));
    }

    /**
     * @param array<TKey, TValue>|\IteratorAggregate|\stdClass|string ...$array_to_merge
     */
    public function merge(...$array_to_merge): Config
    {

        foreach ($array_to_merge as $key => $array) {
            if ($array instanceof \Traversable) {
                $array = \iterator_to_array($array);
            }

            if (! \is_array($array)) {
                $array = (array) $array;
            }

            // Make sure any value given is casting to array
            $array_to_merge[$key] = $array;
        }

        /**
         * We don't need to foreach here, \array_replace_recursive() do the job for us.
         * @psalm-suppress PossiblyInvalidArgument
         */
        $this->storage = \array_replace_recursive($this->storage, ...$array_to_merge);
        return $this;
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param TKey|string|int|array $key
     * @return array<array-key, string>
     */
    private function buildLevels($key): array
    {
        if (\is_array($key)) {
            /**
             * @psalm-suppress MixedArgument
             * @todo Remove this suppression when PHP 8.0 will be the minimum required version
             *       so Union Types will be available
             */
            return \array_map('strval', $key);
        }

        return \explode($this->delimiter, (string)$key);
    }
}
