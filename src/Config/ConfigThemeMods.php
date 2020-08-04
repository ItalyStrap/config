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
 */
class ConfigThemeMods implements ConfigInterface {

	use ArrayObjectTrait;

	/**
	 * @var ConfigInterface
	 */
	private $config;

	/**
	 * @var EventDispatcherInterface
	 */
	private $dispatcher;

	/**
	 * ConfigThemeMod constructor.
	 * @param ConfigInterface $config
	 * @param EventDispatcherInterface|null $dispatcher
	 */
	public function __construct( ConfigInterface $config, EventDispatcherInterface $dispatcher = null ) {
		$this->config = $config;
		$this->dispatcher = $dispatcher ?? new EventDispatcher();
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
	public function get( $parameter_key, $default = null ) {
		/** This filter is documented in wp-includes/theme.php */
		return $this->dispatcher->filter(
			'theme_mod_' . strtolower( $parameter_key ),
			$this->config->get( $parameter_key, $default )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function has( $parameter_key ): bool {
		return $this->config->has( $parameter_key );
	}

	/**
	 * @inheritDoc
	 */
	public function add( $parameter_key, $value ) {
		$this->config->add( ...func_get_args() );
		set_theme_mod( ...func_get_args() );
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function remove( ...$with_keys ) {
		$this->config->remove( ...$with_keys );
		foreach ( $with_keys as $key ) {
			remove_theme_mod( $key );
		}
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function merge( ...$array_to_merge ) {
		$this->config->merge( ...$array_to_merge );
		$theme = get_option( 'stylesheet' );
		update_option( "theme_mods_$theme", $this->all() );
		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(): array {
		return $this->config->toArray();
	}

	/**
	 * @inheritDoc
	 */
	public function toJson(): string {
		return $this->config->toJson();
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator(): iterable {
		return $this->config->getIterator();
	}

	/**
	 * @inheritDoc
	 */
	public function count(): int {
		return $this->config->count();
	}
}
