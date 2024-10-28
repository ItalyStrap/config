<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

use ItalyStrap\Event\EventDispatcher;
use ItalyStrap\Event\EventDispatcherInterface;
use ItalyStrap\Storage\MultipleTrait;
use ItalyStrap\Storage\SetMultipleStoreTrait;

use function func_get_args;
use function get_option;
use function remove_theme_mod;
use function set_theme_mod;
use function strtolower;
use function update_option;

/**
 * Class FilteredConfig
 * @package ItalyStrap\Config
 * @credits https://github.com/TypistTech/wp-option-store/blob/master/src/FilteredOptionStore.php
 *
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements \ItalyStrap\Config\ConfigInterface<TKey,TValue>
 * @psalm-suppress DeprecatedInterface
 * @deprecated No alternative available yet
 */
#[\Deprecated]
class ConfigThemeMods implements ConfigInterface
{
    /**
     * @use \ItalyStrap\Config\ArrayObjectTrait<TKey,TValue>
     */
    use ArrayObjectTrait;
    use DeprecatedTrait;
    use MultipleTrait;
    use SetMultipleStoreTrait;

    private ConfigInterface $config;
    private EventDispatcherInterface $dispatcher;

    /**
     * @param \ItalyStrap\Config\ConfigInterface<TKey,TValue> $config
     * @param EventDispatcherInterface|null $dispatcher
     */
    public function __construct(ConfigInterface $config, EventDispatcherInterface $dispatcher = null)
    {
        $this->config = $config;
        $this->dispatcher = $dispatcher ?? new EventDispatcher();
    }

    /**
     * @param TKey $key
     * @param TValue $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        /** This filter is documented in wp-includes/theme.php */
        return $this->dispatcher->filter(
            'theme_mod_' . strtolower((string)$key),
            $this->config->get($key, $default)
        );
    }

    /**
     * @param TKey $key
     */
    public function has($key): bool
    {
        return $this->config->has($key);
    }

    public function set(string $key, $value): bool
    {
        return $this->config->set($key, $value) && \set_theme_mod((string)$key, $value);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function add($key, $value)
    {
        $this->set($key, $value);
        return $this;
    }

    /**
     * @param TKey ...$with_keys
     */
    public function remove(...$with_keys)
    {
        $this->config->remove(...$with_keys);
        foreach ($with_keys as $key) {
            remove_theme_mod((string)$key);
        }
        return $this;
    }

    /**
     * @param array<TKey, TValue>|\stdClass|string ...$array_to_merge
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function merge(...$array_to_merge): ConfigThemeMods
    {
        $this->config->merge(...$array_to_merge);
        $theme = (string)get_option('stylesheet');
        update_option("theme_mods_$theme", $this->all());
        return $this;
    }

    public function traverse(callable $visitor): void
    {
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->config->all();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->config->toArray();
    }

    public function toJson(): string
    {
        return $this->config->toJson();
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function getIterator(): \Traversable
    {
        return $this->config->getIterator();
    }

    public function count(): int
    {
        return $this->config->count();
    }

    public function update(string $key, $value): bool
    {
		return true;
    }

    public function delete(string $key): bool
    {
		return true;
    }
}
