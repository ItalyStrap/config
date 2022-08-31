<?php
declare(strict_types=1);

namespace ItalyStrap\Tests\Stubs;

final class ConfigKeys {
	public const DEFAULT_VALUE = 'default-value';
	public const BASIC_KEY = 'tizio';
	public const BASIC_KEY_WRONG = 'tizio-wrong';
	public const FILLED_CONFIG_LONG_KEY = 'filled-config.first.iterator-aggregate.property3';
	public const FILLED_CONFIG_LONG_KEY_WRONG = 'filled-config.first.iterator-aggregate.property-not-exists';
	public const CONFIG_OBJECT_SUB_KEY = 'object.sub-object.sub-key';
	public const CONFIG_OBJECT_SUB_KEY_WRONG = 'object.sub-object.sub-key-not-exists';
	public const SUB_ITERATOR_CONFIG_RECURSIVE_KEY = 'iterator-iterator-config-config-config.recursive.subKey';
	public const SUB_ITERATOR_CONFIG_RECURSIVE_KEY_WRONG = 'iterator-iterator-config-config-config.recursive.wrong';
}
