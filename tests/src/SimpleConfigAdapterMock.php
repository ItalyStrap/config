<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\ConfigInterface;
use Psr\SimpleCache\CacheInterface;

class SimpleConfigAdapterMock implements CacheInterface
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function get($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->config->set($key, $value);
    }

    public function delete($key): bool
    {
        return $this->config->delete($key);
    }

    public function clear(): bool
    {
        return (bool)($this->config = clone $this->config);
    }

    public function has($key): bool
    {
        return $this->config->has($key);
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return [];
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        return true;
    }
}
