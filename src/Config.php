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
     * @use ArrayObjectTrait<TKey,TValue>
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
    private $temp;

    /**
     * @var ?TValue
     */
    private $default;

    private string $delimiter = '.';

    /**
     * Config constructor
     *
     * @param array<TKey, TValue> $config
     * @param array<TKey, TValue> $default Deprecated
     */
    public function __construct($config = [], $default = [])
    {
        /** @infection-ignore-all */
        if ($default !== []) {
            \trigger_error(
                'The second argument `$default` is deprecated and will be removed in the next major version.'
                . ' Use the first argument `$config` to provide a default value and `class::merge()` method'
                . ' to add more arrays to merge with the default one.',
                \E_USER_DEPRECATED
            );
        }

        /**
         * @var class-string<\ArrayIterator<TKey, TValue>>|class-string<ArrayObject<TKey, TValue>> $iteratorClass
         */
        $iteratorClass = \RecursiveArrayIterator::class;

        $this->merge($default, $config);
        parent::__construct(
            $this->storage,
            ArrayObject::ARRAY_AS_PROPS,
            $iteratorClass
        );
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
        return $this->temp !== null;
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
     * @param array<TKey, TValue>|\IteratorAggregate|\Iterator|\stdClass|string ...$array_to_merge
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

    /**
     * Traverse the config array and call the callback
     * for each element in the array recursively and in a depth-first order.
     *
     * The callback can accept up to four parameters:
     * - mixed &$value: The current value, passed by reference.
     * - string|int $key: The current key.
     * - Config $config: The Config instance.
     * - array $keyPath: The full key path to the current element.
     *
     * @param array<callable(TValue, TKey, Config, array): void> $visitor
     */
    public function traverse(callable ...$visitor): void
    {
        $this->traverseArray($this->storage, $visitor);
    }

    /**
     * @param array<TKey, TValue> $storage
     * @param array<array-key, callable> $visitors
     */
    private function traverseArray(array &$storage, array $visitors, array $keyPath = []): void
    {
        foreach ($storage as $key => &$current) {
            $path = \array_merge($keyPath, [$key]);
            $skipChildren = false;

            foreach ($visitors as $visitor) {
                /**
                 * @var null|int $signal
                 */
                $signal = $visitor($current, $key, $this, $path);

                if ($signal === SignalCode::NONE) {
                    continue; // Continue to the next callback
                }

                if ($signal === SignalCode::STOP_TRAVERSAL) {
                    break 2; // Stop the current iteration and the parent iteration
                }

                if ($signal === SignalCode::REMOVE_NODE) {
                    unset($storage[$key]);
                }

                if ($signal === SignalCode::REMOVE_NODE || $signal === SignalCode::CONTINUE) {
                    continue 2; // Skip to the next sibling node
                }

                if ($signal === SignalCode::SKIP_CHILDREN) {
                    $skipChildren = true;
                }
            }

            if (!$skipChildren && \is_array($current)) {
                $this->traverseArray($current, $visitors, $path);
            }
        }
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
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
