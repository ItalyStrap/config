<?php
/**
 * Config Class that handle the classes configuration
 *
 *
 * @package ItalyStrap\Config
 */

namespace ItalyStrap\Config;

/**
 * Config Class
 */
class Config_Factory {

	/**
	 * Load and return the Config object.
	 *
	 * @param  string|array $config   File path and filename to the config array; or it is the
	 *                                configuration array.
	 * @param  string|array $defaults Specify a defaults array, which is then merged together
	 *                                with the initial config array before creating the object.
	 *
	 * @return Config Returns the Config object
	 */
	public static function make( $config = [], $defaults = [] ) : Config {
		return new Config( $config, $defaults );
	}
}
