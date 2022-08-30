<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigGetBench {

	private Config $config;

	public function setUp(): void {
		$this->config = new Config(require __DIR__ . '/../_data/config/config.php');
	}

	public function benchGetEmptyIndex(): void {
		$this->config->get('');
	}

	public function benchGetEmptyIndexWithDefault(): void {
		$this->config->get('', 'default-value');
	}

	public function benchGetSingleIndex(): void {
		$this->config->get('tizio');
	}

	public function benchGetSingleIndexWithDefault(): void {
		$this->config->get('tizio', 'default-value');
	}

	public function benchGetFilledConfig(): void {
		$this->config->get('filled-config.first.iterator-aggregate.property3');
	}
}
