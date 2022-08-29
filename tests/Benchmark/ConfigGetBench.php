<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigGetBench {

	private Config $config;
	private array $config_arr;
	private array $default_arr;

	public function setUp(): void {
		$this->config_arr = require __DIR__ . '/../_data/config/config.php';
		$this->default_arr = require __DIR__ . '/../_data/config/default.php';

		$this->config = new Config($this->config_arr);
	}

	public function getEmptyIndex(): void {
		$this->config->get('');
	}

	public function getEmptyIndexWithDefault(): void {
		$this->config->get('', 'default-value');
	}

	public function getSingleIndex(): void {
		$this->config->get('tizio');
	}

	public function getSingleIndexWithDefault(): void {
		$this->config->get('tizio', 'default-value');
	}
}
