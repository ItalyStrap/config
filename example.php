<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

require(__DIR__ . '/vendor/autoload.php');

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

$input = new ArgvInput();
$output = new ConsoleOutput();
$style = new SymfonyStyle($input, $output);

$style->title('Config Examples:');

$config = (new ConfigFactory())->make([ 'test' => null ]);
$config->merge([ 'test' => 'value', 'test2' => 'value2' ]);

$style->section('Get method');
$style->writeln('Get a value from the configuration');
$style->text('Value of test: ' . $config->get('test'));
$style->text('Value of non-existing key: ' . ($config->get('key-does-not-exists') ?? 'null'));
$style->text('Value from default: ' . ($config->get('key-does-not-exists', 'default-value')));
$style->newLine();

$style->section('Has Method');
$style->writeln('Check if keys exist in the configuration:');
$style->text('Does key "test" exist? ' . ($config->has('test') ? 'Yes' : 'No'));
$style->text('Does key "key-does-not-exists" exist? ' . ($config->has('key-does-not-exists') ? 'Yes' : 'No'));
$style->newLine();

$style->section('Set Method');
$style->writeln('Set a value in the configuration:');
$config->set('test', 'new-value');
$style->text('Value of test: ' . $config->get('test'));
$style->newLine();

$style->section('Delete Method');
$style->writeln('Delete a value from the configuration:');
$config->delete('test');
$style->text('Value of test: ' . ($config->has('test') ? 'Exists' : 'Not exists'));
$style->newLine();

$style->section('Merging Configurations');
$style->writeln('Merge configurations:');
$default = [
    'key'   => 'value',
    'key1'  => [
        'subKey'    => 'someValue',
    ],
];

$config->merge(
    $default,
    [
        'key1' => [
            'subKey'    => 'someValue',
            'nested'    => [
                'subSubKey' => 'nestedValue'
            ]
        ]
    ]
);
$style->text('Merged configuration:');
$style->listing([
    'key1.subKey: "' . $config->get('key1.subKey') . '"',
    'key1.nested.subSubKey: "' . $config->get('key1.nested.subSubKey') . '"',
]);
$style->newLine();


$style->section('Accessing with dot notation');

$style->writeln('Access value with dot notation:');
$style->text('Value of key1.subKey: ' . ($config->has('key1.subKey') ? 'Exists' : 'Not exists'));
$style->text('Value of key1.subKey: ' . $config->get('key1.subKey'));
$config->delete('key1.subKey');
$style->text('Value of key1.subKey: ' . ($config->has('key1.subKey') ? 'Exists' : 'Not exists'));
$config->set('key1.subKey', 'new-value');
$style->text('New value of key1.subKey: ' . $config->get('key1.subKey'));
$style->newLine();

$style->section('Accessing with array notation');

$style->writeln('Access value with array notation:');
$style->text("Value of ['key1','subKey']: " . ($config->has(['key1','subKey']) ? 'Exists' : 'Not exists'));
$style->text("Value of ['key1','subKey']: " . $config->get(['key1','subKey']));
$config->delete(['key1','subKey']);
$style->text("Value of ['key1','subKey']: " . ($config->has(['key1','subKey']) ? 'Exists' : 'Not exists'));
$config->set(['key1','subKey'], 'new-value');
$style->text("New value of ['key1','subKey']: " . $config->get(['key1','subKey']));
$style->newLine();

$style->section('Merging with all traversable');

$style->writeln('Merge with all traversable:');
$config->merge(new \ArrayIterator([
    'key2' => [
        'subKey2'   => 'value2',
    ]
]));

$generator = function (): \Traversable {
    yield 'key3' => 'value3';
};

$config->merge($generator());
$style->text('Merged configuration:');
$style->listing([
    'key2.subKey2: "' . $config->get('key2.subKey2') . '"',
    'key3: "' . $config->get('key3') . '"',
]);
$style->newLine();

$style->section('Traversing Configuration');

$style->writeln('Modifying Numeric Values:');
$config = new Config([
    'numbers' => [1, 2, 3, 4, 5],
]);
$config->traverse(static function (&$current): void {
    if (is_numeric($current) && $current % 2 === 0) {
        $current *= 10;
    }
});
$style->listing($config->toArray()['numbers']);
$style->newLine();

$style->writeln('Modifying Elements Based on a Condition:');
$config = new Config([
    'items' => [
        ['name' => 'Item 1', 'price' => 100],
        ['name' => 'Item 2', 'price' => 200],
        ['name' => 'Item 3', 'price' => 300],
    ],
]);
$visited = [];
$config->traverse(
    static function (&$current, $key, ConfigInterface $config, array $keyPath): void {
        if (\is_array($current) && array_key_exists('price', $current) && $current['price'] > 200) {
            $current['price'] = 250;
        }
    },
    static function (&$current, $key, ConfigInterface $config, array $keyPath) use (&$visited): void {
        if (\is_array($current) && \array_key_exists('price', $current)  && \array_key_exists('name', $current)) {
            $visited[] = $current['name'] . ' - ' . $current['price'];
        }
    },
);
$style->listing($visited);
$style->newLine();

$style->writeln('Removing Elements Based on a Condition:');
$config = new Config([
    'items' => [
        ['name' => 'Item 1', 'remove' => false],
        ['name' => 'Item 2', 'remove' => true],
        ['name' => 'Item 3', 'remove' => false],
    ],
]);
$visited = [];
$config->traverse(
    static function (&$current, $key, ConfigInterface $config, array $keyPath): ?int {
        if (\is_array($current) && \array_key_exists('remove', $current) && $current['remove'] === true) {
            return SignalCode::REMOVE_NODE;
        }

        return SignalCode::NONE;
    },
    static function (&$current, $key, ConfigInterface $config, array $keyPath) use (&$visited): void {
        if (\is_array($current) && \array_key_exists('name', $current) && \array_key_exists('remove', $current)) {
            $visited[] = $current['name'] . ' - Not Removed';
        }
    },
);
$style->listing($visited);
$style->newLine();
