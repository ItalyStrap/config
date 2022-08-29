<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;

$anonymous_class = new class() {
	public string $var1 = 'value 1';
	public string $var2 = 'value 2';
	public string $var3 = 'value 3';
};

$iterator_aggregate_test = new class implements \IteratorAggregate {
	public string $property1 = "Public property one";
	public string $property2 = "Public property two";
	public string $property3 = "Public property three";
	private string $property4;

	public function __construct() {
		$this->property4 = "last property";
	}

	public function getIterator() {
		return new \ArrayIterator($this);
	}
};

return [
	'tizio'     => [],
	'caio'      => '',
	'sempronio' => '',
	'recursive' => [
		'subKey'	=> 'newSubValue',
	],
	'object' => new Config(
		[
			'key'	=> 'val',
			'sub-object'	=> new Config(
				[
					'sub-key'	=> 'sub-value',
				]
			),
		]
	),
	'anonymous-class' => $anonymous_class,
	'iterator-aggregate-test' => $iterator_aggregate_test,
	'iterator-with-default-file' => new \ArrayIterator(require 'default.php'),
	'iterator-iterator' => new \IteratorIterator(new \ArrayIterator(require 'default.php')),
	'iterator-iterator-config' => new \IteratorIterator(new \ArrayIterator(new Config(require 'default.php'))),
];
