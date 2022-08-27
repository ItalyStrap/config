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
}
