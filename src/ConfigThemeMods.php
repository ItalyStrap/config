<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

use ItalyStrap\Event\EventDispatcher;
use ItalyStrap\Event\EventDispatcherInterface;
use function func_get_args;
use function get_option;
use function remove_theme_mod;
use function set_theme_mod;
use function strtolower;
use function update_option;

/**
 * Class FilteredConfig
 * @package ItalyStrap\Config
 * @credits https://github.com/TypistTech/wp-option-store/blob/master/src/FilteredOptionStore.php
 *
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements \ItalyStrap\Config\ConfigInterface<TKey,TValue>
 * @psalm-suppress DeprecatedInterface
 */
class ConfigThemeMods implements ConfigInterface {

	private ConfigInterface $config;
	private EventDispatcherInterface $dispatcher;

	/**
	 * @param \ItalyStrap\Config\ConfigInterface<TKey,TValue> $config
	 * @param EventDispatcherInterface|null $dispatcher
	 */
	public function __construct( ConfigInterface $config, EventDispatcherInterface $dispatcher = null ) {
		$this->config = $config;
		$this->dispatcher = $dispatcher ?? new EventDispatcher();
	}

	/**
	 * @param TKey $index
	 * @param TValue $default
	 * @return mixed
	 */
	public function get( $index, $default = null ) {
		/** This filter is documented in wp-includes/theme.php */
		return $this->dispatcher->filter(
			'theme_mod_' . strtolower( (string)$index ),
			$this->config->get( $index, $default )
		);
	}

	/**
	 * @param TKey $index
	 */
	public function has( $index ): bool {
		return $this->config->has( $index );
	}

	/**
	 * @param TKey $index
	 * @param TValue $value
	 */
	public function add( $index, $value ) {
		$this->config->add( $index, $value );
		set_theme_mod( (string)$index, $value );
		return $this;
	}

	/**
	 * @param TKey ...$with_indexes
	 */
	public function remove( ...$with_indexes ) {
		$this->config->remove( ...$with_indexes );
		foreach ( $with_indexes as $key ) {
			remove_theme_mod( (string)$key );
		}
		return $this;
	}

	/**
	 * @param array<TKey, TValue>|\stdClass|string ...$array_to_merge
	 * @psalm-suppress PossiblyInvalidArgument
	 */
	public function merge( ...$array_to_merge ): ConfigThemeMods {
		$this->config->merge( ...$array_to_merge );
		$theme = (string)get_option( 'stylesheet' );
		update_option( "theme_mods_$theme", $this->all() );
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function all(): array {
		return $this->config->all();
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array {
		return $this->config->toArray();
	}

	public function toJson(): string {
		return $this->config->toJson();
	}

	public function getIterator(): iterable {
		return $this->config->getIterator();
	}

	public function count(): int {
		return $this->config->count();
	}

	/**
	 * @param TKey $index
	 * @psalm-suppress InvalidArgument
	 */
	public function offsetExists( $index ) {
		return $this->has( $index );
	}

	/**
	 * @param TKey $index
	 * @return mixed
	 * @psalm-suppress InvalidArgument
	 */
	public function offsetGet( $index ) {
		return $this->get( $index );
	}

	/**
	 * @param TKey $index
	 * @param TValue $newval
	 * @psalm-suppress InvalidArgument
	 */
	public function offsetSet( $index, $newval ) {
		$this->add( $index, $newval );
	}

	/**
	 * @param TKey $index
	 * @psalm-suppress InvalidArgument
	 */
	public function offsetUnset( $index ) {
		$this->remove( $index );
	}
}
