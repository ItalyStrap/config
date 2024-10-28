<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use Codeception\Test\Unit;
use ItalyStrap\Config\ConfigInterface;
use ItalyStrap\Event\EventDispatcherInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use UnitTester;

abstract class TestCase extends Unit
{
    use ProphecyTrait;

    protected UnitTester $tester;

    protected \Prophecy\Prophecy\ObjectProphecy $config;

    public function makeDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher->reveal();
    }

    protected \Prophecy\Prophecy\ObjectProphecy $dispatcher;

    public function makeConfig($val = [], $default = []): ConfigInterface
    {
        $this->config->willBeConstructedWith(
            [
                $val,
                $default
            ]
        );

        return $this->config->reveal();
    }

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

        $this->config = $this->prophesize(ConfigInterface::class);
        $this->dispatcher = $this->prophesize(EventDispatcherInterface::class);

        parent::_before();
    }

	// phpcs:ignore
	protected function _after() {
        parent::_after();
    }

    protected function prepareSetMultipleReturnFalse(): void
    {
    }
}
