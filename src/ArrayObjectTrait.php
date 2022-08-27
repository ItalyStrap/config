<?php
declare(strict_types=1);

namespace ItalyStrap\Config;

trait ArrayObjectTrait {

	/**
	 * @inheritDoc
	 */
	public function offsetExists( $index ) {
		return $this->has( $index );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet( $index ) {
		return $this->get( $index );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet( $index, $newval ) {
		$this->add( $index, $newval );
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($index) {
		$this->remove( $index );
	}
}
