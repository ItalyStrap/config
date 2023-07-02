<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

use ArrayObject;

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
class Config extends ArrayObject implements ConfigInterface
{

    /**
     * @use \ItalyStrap\Config\ArrayObjectTrait<TKey,TValue>
     */
    use ArrayObjectTrait, AccessValueInArrayWithNotationTrait, DeprecatedTrait;

    /**
     * @var array<TKey, TValue>
     */
    private array $storage = [];

    /**
     * @var TValue
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $temp;

    /**
     * @var TValue|null
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $default;

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
     * @param TKey $index
     * @param TValue $default
     * @return TValue
     */
    public function get($index, $default = null)
    {
        $this->default = $default;

        if (! $this->has($index)) {
            return $default;
        }

        // The class::temp variable is always setted by the class::has() method
        return $this->temp;
    }

    /**
     * @param TKey $index
     */
    public function has($index): bool
    {
        /**
         * @psalm-suppress MixedAssignment
         */
        $this->temp = $this->search($this->storage, $index, $this->default);
        $this->default = null;
        return isset($this->temp);
    }

    /**
     * @param TKey $index
     * @param TValue $value
     */
    public function add($index, $value): Config
    {
        $levels = \explode($this->delimiter, (string)$index);
        if (\is_int($index) || ! $levels) {
            /**
             * @psalm-suppress InvalidArrayAccess
             */
            $this->storage[ $index ] = $value;
        }

        $this->appendValue($this->storage, $levels, $value);
        return $this;
    }

    /**
     * @param TKey ...$with_indexes
     */
    public function remove(...$with_indexes): Config
    {
        \array_walk(
            $with_indexes,
            /**
             * @param mixed $indexes
             * @psalm-suppress MixedArgumentTypeCoercion
             */
            [$this, 'removeIndexesFromStorage']
        );

        return $this;
    }

    /**
     * @param array<array-key, TKey>|string $indexes
     */
    private function removeIndexesFromStorage($indexes): void
    {
        foreach ((array)$indexes as $k) {
            $levels = \explode($this->delimiter, $k);
            if (! $levels) {
                unset($this->storage[ $k ]);
                continue;
            }

            $this->deleteValue($this->storage, $levels);
        }
    }

    /**
     * @param array<TKey, TValue>|\stdClass|string ...$array_to_merge
     */
    public function merge(...$array_to_merge): Config
    {

        foreach ($array_to_merge as $index => $array) {
            if ($array instanceof \Traversable) {
                $array = \iterator_to_array($array);
            }

            if (! \is_array($array)) {
                $array = (array) $array;
            }

            // Make sure any value given is casting to array
            $array_to_merge[ $index ] = $array;
        }

        /**
         * We don't need to foreach here, \array_replace_recursive() do the job for us.
         * @psalm-suppress PossiblyInvalidArgument
         */
        $this->storage = \array_replace_recursive($this->storage, ...$array_to_merge);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * @inheritDoc
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return \strval(\json_encode($this->toArray(), JSON_THROW_ON_ERROR));
    }

    /**
     * @link https://github.com/balambasik/input/blob/master/src/Input.php
     *
     * @param iterable<array-key, mixed> $array
     * @param string|int $index
     * @param mixed $default
     *
     * @return mixed|null
     */
    private function search(iterable $array, $index, $default = null)
    {

        if (\is_int($index) || ! $levels = \explode($this->delimiter, $index)) {
            /**
             * @psalm-suppress InvalidArrayAccess
             */
            return $array[ $index ] ?? $default;
        }

        return $this->findValue($array, $levels, $default);
    }
}
