<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;
use ItalyStrap\Tests\Stubs\ConfigKeys;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigHasBench
{

    private Config $config;

    public function setUp(): void
    {
        $this->config = new Config(require __DIR__ . '/../_data/config/config.php');
    }

    public function benchHasWithEmptyIndex(): void
    {
        $this->config->has('');
    }

    public function benchHasWithEmptyIndexAndEmptyDefaultValue(): void
    {
        $this->config->has('', '');
    }

    public function benchHasEmptyIndexWithDefault(): void
    {
        $this->config->has('');
    }

    public function benchHasSingleIndex(): void
    {
        $this->config->has(ConfigKeys::BASIC_KEY);
    }

    public function benchHasSingleIndexWithDefault(): void
    {
        $this->config->has(ConfigKeys::BASIC_KEY);
    }

    public function benchHasSingleWrongIndexWithDefault(): void
    {
        $this->config->has(ConfigKeys::BASIC_KEY_WRONG);
    }

    public function benchHasFilledConfigWithLongKeys(): void
    {
        $this->config->has(ConfigKeys::FILLED_CONFIG_LONG_KEY);
    }

    public function benchHasFilledConfigWithLongWrongKeysAndDefaultValue(): void
    {
        $this->config->has(ConfigKeys::FILLED_CONFIG_LONG_KEY_WRONG);
    }

    public function benchHasObjectConfig(): void
    {
        $this->config->has(ConfigKeys::CONFIG_OBJECT_SUB_KEY);
    }

    public function benchHasObjectConfigWithWrongKey(): void
    {
        $this->config->has(ConfigKeys::CONFIG_OBJECT_SUB_KEY_WRONG);
    }

    public function benchHasSubIteratorConfigRecursiveKey(): void
    {
        $this->config->get(ConfigKeys::SUB_ITERATOR_CONFIG_RECURSIVE_KEY);
    }

    public function benchHasSubIteratorConfigRecursiveKeyWrong(): void
    {
        $this->config->get(ConfigKeys::SUB_ITERATOR_CONFIG_RECURSIVE_KEY_WRONG, ConfigKeys::DEFAULT_VALUE);
    }
}
