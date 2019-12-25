<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

/**
 * Class FilteredConfig
 * @package ItalyStrap\Config
 * @credits https://github.com/TypistTech/wp-option-store/blob/master/src/FilteredOptionStore.php
 */
class FilteredConfig extends Config
{
	/**
	 * Option getter.
	 *
	 * @param string $key
	 * @param string $default Name of option to retrieve.
	 *
	 * @return mixed|null Null if option not exists or its value is actually null.
	 */
	public function get( string $key, $default = null ) {
		return \apply_filters(
			$this->filterName( $key ),
			parent::get( $key, $default )
		);
	}
	/**
	 * Normalize option name and key to snake_case filter tag.
	 *
	 * @param string $default Name of option to retrieve.
	 *                           Expected to not be SQL-escaped.
	 *
	 * @return string
	 */
	private function filterName( string $default ): string {
		return \strtolower( $default );
	}
}