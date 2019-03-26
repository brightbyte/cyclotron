<?php
/*
 * Calculates strongly connected components of a graph in linear time. 
 * See <https://en.wikipedia.org/wiki/Path-based_strong_component_algorithm>
 */
namespace Wikimedia\Cyclotron;

class JohnsonCycles {

	private $graph;

	private $stack;
	private $blocked;
	private $blockLists;

	private $cycles = null;

	public function __construct( Graph $graph ) {
		$this->graph = $graph;

		$this->stack = [];
		$this->blocked = [];
		$this->blockLists = [];
	}

	private function unblock( $v ) {
		unset( $this->blocked[ $v ] );
		if ( isset( $this->blockLists[$v] ) ) {
			foreach ( array_keys( $this->blockLists[$v] ) as $w ) {
				if ( $this->blocked[ $w ] ?? false ) {
					$this->unblock( $w );
				}
			}
		}
		unset( $this->blockLists[$v] );
	}

	private function cycle( Graph $g, $v, $s ) {
		if ( !$g->getVertices() ) {
			return false;
		}

		$f = false;
		$this->stack[] = $v;
		$this->blocked[$v] = true;

		foreach ( $g->getOutNeighbours( $v ) as $w ) {
			if ( $w === $s ) {
				$cy = array_values( $this->stack );
				$cy[] = $s;
				$this->cycles[] = $cy; // copy
				$f = true;
			} elseif ( !isset( $this->blocked[$w] ) ) {
				if ( $this->cycle( $g, $w, $s ) ) {
					$f = true;
				}
			}
		}

		if ( $f ) {
			$this->unblock( $v );
		} else {
			foreach ( $g->getOutNeighbours( $v) as $w ) {
				$this->blockLists[$w][$v] = true;
			}
		}

		array_pop( $this->stack );
		return $f;
	}

	public function getCycles() {
		if ( $this->cycles !== null ) {
			return $this->cycles;
		}

		$this->cycles = [];

		$subgraphs = ( new KosarajuComponents( $this->graph ) )->getComponentSubgraphs();

		while ( $subgraphs ) {
			/** @var Graph $g */
			$g = array_shift( $subgraphs );

			if ( $g->getSize() < 2 ) {
				continue;
			}

			$vert = $g->getVertices();
			$s = array_shift( $vert );

			$this->stack = [];
			$this->cycle( $g, $s, $s );

			if ( $g->getSize() > 2 ) {
				// $vert is missing $s, construct a subgraph with the remaining vertices
				$h = Graph::newSubgraph( $vert, $g );

				// get all the SCCs of the new subgraphs and put them into the work queue.
				$sub = ( new KosarajuComponents( $h ) )->getComponentSubgraphs();
				$subgraphs = array_merge( $subgraphs, $sub );
			}
		}

		return $this->cycles;
	}

}
