<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;

/**
 * @BeforeMethods({"setUp"})
 */
class ConfigSetBench
{
    private Config $config;

    public function setUp(): void
    {
        $this->config = new Config(require __DIR__ . '/../_data/config/config.php');
    }

    public function benchSetWithEmptyIndex(): void
    {
        $this->config->set('', 'value');
    }

    public function benchSetWithEmptyIndexAndEmptyValue(): void
    {
        $this->config->set('', '');
    }

    public function benchSetEmptyIndexWithDefault(): void
    {
        $this->config->set('', 'value');
    }

    public function benchSetSingleIndex(): void
    {
        $this->config->set('index', 'value');
    }
}
