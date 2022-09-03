<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

/**
 * @template TKey as array-key
 * @template TValue
 */
trait ArrayObjectTrait {

	/**
	 * @param TKey $index
	 * @psalm-suppress InvalidArgument
	 */
	public function offsetExists( $index ): bool {
		return $this->has( $index );
	}

	/**
	 * @param TKey $index
	 * @return TValue
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

	public function count(): int {
		parent::exchangeArray( $this->storage );
		return parent::count();
	}

	public function getArrayCopy(): array {
		parent::exchangeArray( $this->storage );
		return parent::getArrayCopy();
	}

	public function __clone() {
		$this->storage = [];
		parent::exchangeArray( $this->storage );
	}
}
