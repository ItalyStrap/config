<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Benchmark;

use ItalyStrap\Config\Config;

/**
 * @BeforeMethods({"setUp"})
 */
final class ConfigMergeBench {

	private Config $config;
	private \stdClass $stdClass;
	private array $config_arr;
	private array $default_arr;

	public function setUp(): void {
		$this->config_arr = require __DIR__ . '/../_data/config/config.php';
		$this->default_arr = require __DIR__ . '/../_data/config/default.php';

		$this->config = new Config();
		$this->stdClass = (object)[
			'value' => 'stdclass-result',
			'withSubValue' => [
				'subValue' => 'stdclass-sub-result',
			],
		];
	}

	public function benchMergeSingleParam(): void {
		$this->config->merge(['index' => 'value']);
	}

	public function benchMergeDoubleParams(): void {
		$this->config->merge($this->config_arr, $this->default_arr);
	}

	public function benchMergeMultipleParams(): void {
		$this->config->merge(
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr,
			$this->config_arr
		);
	}

	public function benchMergeStdClassAsSecondParams(): void {
		$this->config->merge(['index' => 'value'], $this->stdClass);
	}

	public function benchMergeMultipleStdClassParams(): void {
		$this->config->merge(
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
			$this->stdClass,
		);
	}
}
