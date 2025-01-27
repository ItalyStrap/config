<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;

$std = new \stdClass();
$std->some_std_key = 'some-std-value';

$anonymous_class = new class () {
    public string $var1 = 'value 1';

    public string $var2 = 'value 2';

    public string $var3 = 'value 3';
};

$iterator_aggregate_test = new class ($anonymous_class) implements \IteratorAggregate {
    public string $property1 = "Public property one";

    public string $property2 = "Public property two";

    public string $property3 = "Public property three";

    public object $property4;

    public function __construct(object $object)
    {
        $this->property4 = $object;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this);
    }
};

$array1 = [
    "first",
    "second",
    "something",
    "another",
];

$array2 = [
    "first-key" => "first-value",
    "second-key" => "something-value",
    "srd-class" => $std,
    "anonymous-class" => $anonymous_class,
    'iterator-aggregate' => $iterator_aggregate_test
];

return [
    'tizio'     => [],
    'caio'      => '',
    'sempronio' => '',
    'recursive' => [
        'subKey'    => 'newSubValue',
    ],
    'object' => new Config(
        [
            'key'   => 'val',
            'sub-object'    => new Config(
                [
                    'sub-key'   => 'sub-value',
                ]
            ),
        ]
    ),
    'anonymous-class' => $anonymous_class,
    'iterator-aggregate-test' => $iterator_aggregate_test,
    'iterator-with-default-file' => new \ArrayIterator(require __DIR__ . '/default.php'),
    'iterator-iterator' => new \IteratorIterator(new \ArrayIterator(require __DIR__ . '/default.php')),
    'iterator-iterator-config' =>
        new \IteratorIterator(new \ArrayIterator(new Config(require __DIR__ . '/default.php'))),

    'filled-array' => \array_fill_keys($array1, $array2),
    'filled-config' => new Config(\array_fill_keys($array1, $array2), \array_fill_keys($array1, $array2)),
    'iterator-config' => new Config(new \ArrayIterator(require __DIR__ . '/default.php')),
    'iterator-iterator-config-config' =>
        new Config(new \IteratorIterator(new \ArrayIterator(require __DIR__ . '/default.php'))),
    'iterator-iterator-config-config-config' => new Config(
        new \IteratorIterator(new \ArrayIterator(new Config(require __DIR__ . '/default.php')))
    ),
];
