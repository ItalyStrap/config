<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

use ArrayObject;
use ItalyStrap\Storage\MultipleTrait;
use ItalyStrap\Storage\SetMultipleStoreTrait;

/**
 * @todo Immutable: https://github.com/jkoudys/immutable.php
 * @todo Maybe some ideas iterator: https://github.com/clean/data/blob/master/src/Collection.php
 * @todo Maybe some ideas json to array: https://github.com/Radiergummi/libconfig/blob/master/src/Libconfig/Config.php
 * @todo Maybe some ideas: https://www.simonholywell.com/post/2017/04/php-and-immutability-part-two/
 * @todo Maybe add recursion? https://www.php.net/manual/en/class.arrayobject.php#123572
 *
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
     * @param TKey|string $key
     * @param TValue $default
     * @return TValue
     */
    public function get($key, $default = null)
    {
        $this->default = $default;

        if (!$this->has((string)$key)) {
            return $default;
        }

        // The class::temp variable is always set by the class::has() method
        return $this->temp;
    }

    /**
     * @param TKey|string $key
     */
    public function has($key): bool
    {
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->temp = $this->findValue(
            $this->storage,
            $this->buildLevels((string)$key),
            $this->default
        );
        $this->default = null;
        return isset($this->temp);
    }

    /**
     * @param TKey|string $key
     * @param TValue|mixed $value
     */
    public function set(string $key, $value): bool
    {
        return $this->insertValue($this->storage, $this->buildLevels((string)$key), $value);
    }

    public function update(string $key, $value): bool
    {
        return $this->set($key, $value);
    }

    public function delete(string $key): bool
    {
        return $this->deleteValue($this->storage, $this->buildLevels($key));
    }

    /**
     * @param array<TKey, TValue>|\stdClass|string ...$array_to_merge
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

    /**
     * @deprecated This is a soft deprecation, I'm working on a different solution to dump a Json format,
     * in the meantime you can use:
     * (string)\json_encode(mew Config(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
     * @psalm-suppress RedundantCastGivenDocblockType
     */
    public function toJson(): string
    {
        return (string)\json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param string $key
     * @return array<array-key>
     */
    private function buildLevels(string $key): array
    {
        return \explode($this->delimiter ?: '.', $key);
    }
}
