<?php

/**
 * Config Class that handle the classes configuration
 *
 *
 * @package ItalyStrap\Config
 */

declare(strict_types=1);

namespace ItalyStrap\Config;

/**
 * Config Class
 */
class ConfigFactory
{
    /**
     * Load and return the Config object.
     *
     * @param array $config   File path and filename to the config array; or it is the
     *                                configuration array.
     * @param  array $defaults Specify a defaults array, which is then merged together
     *                                with the initial config array before creating the object.
     *
     * @return Config Returns the Config object
     */
    public function make($config = [], $defaults = []): ConfigInterface
    {
        if ($defaults !== []) {
            \trigger_error(
                'The second argument `$default` is deprecated and will be removed in the next major version.'
                . ' Use the first argument `$config` to provide a default value and `class::merge()` method'
                . ' to add more arrays to merge with the default one.',
                \E_USER_DEPRECATED
            );
        }

        return new Config($config, $defaults);
    }
}
