<?php
/*
 * Calculates strongly connected components of a graph in linear time. 
 * See <https://en.wikipedia.org/wiki/Path-based_strong_component_algorithm>
 */
namespace Wikimedia\Cyclotron;

class JohnsonCycles {

	private $graph;

	private $blocked;
	private $blockLists;

	private $cycles = null;

	public function __construct( Graph $graph ) {
		$this->graph = $graph;

		$this->blocked = [];
		$this->blockLists = [];
		$this->cycles = [];
	}

	public function getCycles() {
		if ( $this->cycles !== null ) {
			return $this->cycles;
		}
		$this->cycles = [];

		$stack = [];
		$s = 1;

		return $this->cycles;
	}

	private function unblock( $v ) {
		unset( $this->blocked[ $v ] );
		foreach ( $this->blockLists[$v] ?? [] as $w ) {
			if ( $this->blocked[ $w ] ?? false ) {
				$this->unblock( $w );
			}
		}
		unset( $this->blockLists[$v] );
	}

	private function cycle( Graph $comp, $v, $s, array &$stack ) {
		if ( !$comp->getVertices() ) {
			return false;
		}

		$f = false;
		$stack[] = $v;
		$this->blocked[$v] = true;

		foreach ( $comp->getOutNeighbours( $v ) as $w ) {
			if ( $w === $s ) {
				$stack[] = $s;
				$this->cycles[] = array_values( $stack ); // copy
				array_pop( $stack );
				$f = true;
			} elseif ( !isset( $this->blocked[$w] ) ) {
				if ( $this->cycle( $comp, $w, $s, $stack ) ) {
					$f = true;
				}
			}
		}

		if ( $f ) {
			$this->unblock( $v );
		} else {
			foreach ( $comp->getOutNeighbours( $v) as $w ) {
				$this->blockLists[$w][$v] = true;
			}
		}
	}

}
