<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigBench
{

    private Config $config;

    public function setUp(): void
    {
        $this->config = new Config();
    }

    public function benchAdd()
    {
        $this->config->add('index', 'value');
    }

    public function benchRemove()
    {
        $this->config->remove(['index']);
    }

    public function benchToArray()
    {
        $this->config->toArray();
    }

    public function benchToJson()
    {
        $this->config->toJson();
    }
}
