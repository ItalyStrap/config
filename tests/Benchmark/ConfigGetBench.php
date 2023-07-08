<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;
use ItalyStrap\Tests\Stubs\ConfigKeys;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigGetBench
{

    private Config $config;

    public function setUp(): void
    {
        $this->config = new Config(require __DIR__ . '/../_data/config/config.php');
    }

    public function benchGetWithEmptyIndex(): void
    {
        $this->config->get('');
    }

    public function benchGetWithEmptyIndexAndEmptyDefaultValue(): void
    {
        $this->config->get('', '');
    }

    public function benchGetEmptyIndexWithDefault(): void
    {
        $this->config->get('', ConfigKeys::DEFAULT_VALUE);
    }

    public function benchGetSingleIndex(): void
    {
        $this->config->get(ConfigKeys::BASIC_KEY);
    }

    public function benchGetSingleIndexWithDefault(): void
    {
        $this->config->get(ConfigKeys::BASIC_KEY, ConfigKeys::DEFAULT_VALUE);
    }

    public function benchGetSingleWrongIndexWithDefault(): void
    {
        $this->config->get(ConfigKeys::BASIC_KEY_WRONG, ConfigKeys::DEFAULT_VALUE);
    }

    public function benchGetFilledConfigWithLongKeys(): void
    {
        $this->config->get(ConfigKeys::FILLED_CONFIG_LONG_KEY);
    }

    public function benchGetFilledConfigWithLongWrongKeysAndDefaultValue(): void
    {
        $this->config->get(ConfigKeys::FILLED_CONFIG_LONG_KEY_WRONG, ConfigKeys::DEFAULT_VALUE);
    }

    public function benchGetObjectConfig(): void
    {
        $this->config->get(ConfigKeys::CONFIG_OBJECT_SUB_KEY);
    }

    public function benchGetObjectConfigWithWrongKey(): void
    {
        $this->config->get(ConfigKeys::CONFIG_OBJECT_SUB_KEY_WRONG, ConfigKeys::DEFAULT_VALUE);
    }

    public function benchGetSubIteratorConfigRecursiveKey(): void
    {
        $this->config->get(ConfigKeys::SUB_ITERATOR_CONFIG_RECURSIVE_KEY);
    }

    public function benchGetSubIteratorConfigRecursiveKeyWrong(): void
    {
        $this->config->get(ConfigKeys::SUB_ITERATOR_CONFIG_RECURSIVE_KEY_WRONG, ConfigKeys::DEFAULT_VALUE);
    }
}
