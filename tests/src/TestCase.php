<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\Test\Unit;
use Prophecy\PhpUnit\ProphecyTrait;
use UnitTester;

abstract class TestCase extends Unit
{

    use ProphecyTrait;

    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    protected array $config_arr = [];
    protected array $default_arr = [];

    /**
     * @var mixed
     */
    protected $empty_arr;

	// phpcs:ignore
	protected function _before() {
        $this->config_arr = require \codecept_data_dir('config/config.php');
        $this->default_arr = require \codecept_data_dir('config/default.php');
        $this->empty_arr = require \codecept_data_dir('config/empty.php');
    }

	// phpcs:ignore
	protected function _after() {
    }

    /**
     * @test
     */
    public function instanceOk()
    {
        $sut = $this->makeInstance();
    }
}
