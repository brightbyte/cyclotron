<?php
namespace Wikimedia\Cyclotron;

/**
 * Simple directed graph representation.
 * @package Wikimedia\Cyclotron
 */
class Graph {
	
	protected $children;
	protected $parents;
	protected $vertices;

	/**
	 * @param string[][]|int[][] $edges Array of pairs
	 *
	 * @return Graph
	 */
	public static function newFromEdges( array $edges ) {
		$children = [];
		$parents = [];
		$vert = [];

		foreach ( $edges as $e ) {
			$a = $e[0];
			$b = $e[1];

			$children[$a][] = $b;
			$parents[$b][] = $a;
			$vert[$a] = true;
			$vert[$b] = true;
		}

		$vert = array_keys( $vert );
		return new static( $vert, $children, $parents );
	}

	/**
	 * @param string[]|int[] $vertices List of vertices.
	 * @param Graph $g parent graph to be filtered against $vertices
	 *
	 * @return Graph
	 */
	public static function newSubgraph( array $vertices, Graph $g ) {
		$children = [];
		$parents = [];

		foreach ( $vertices as $v ) {
			$out = array_intersect( $vertices, $g->getOutNeighbours( $v ) );

			foreach ( $out as $w ) {
				$children[$v][] = $w;
				$parents[$w][] = $v;
			}
		}

		return new static( $vertices, $children, $parents );
	}

	/**
	 * @param string[]|int[] $vertices
	 * @param string[]|int[] $children
	 * @param string[]|int[] $parents
	 */
	protected function __construct( array $vertices, array $children, array $parents ) {
		$this->vertices = $vertices;
		$this->children = $children;
		$this->parents = $parents;
	}

	/**
	 * @param mixed $v
	 *
	 * @return string[]|int[]
	 */
	public function getOutNeighbours( $v ) {
		return $this->children[ $v ] ?? [];
	}

	/**
	 * @param mixed $v
	 *
	 * @return string[]|int[]
	 */
	public function getInNeighbours( $v ) {
		return $this->parents[ $v ] ?? [];
	}

	/**
	 * @return string[]|int[]
	 */
	public function getVertices() {
		return $this->vertices;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$s = [];

		foreach ( $this->vertices as $v ) {
			$s[] = $v;
			$s[] = ' -> ';
			$s[] = join( ',', $this->getOutNeighbours( $v ) );
			$s[] = "\n";
		}

		return join( '', $s );
	}

}
