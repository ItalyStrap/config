<?php
declare(strict_types=1);

namespace ItalyStrap\Tests;

use ItalyStrap\Config\Config;

return [
	'tizio'     => [],
	'caio'      => '',
	'sempronio' => '',
	'recursive' => [
		'subKey'	=> 'newSubValue',
	],
	'object' => new Config(['key' => 'val']),
];
