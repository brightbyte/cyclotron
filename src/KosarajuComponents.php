<?php
/*
 * Calculates strongly connected components of a graph in linear time. 
 * See <https://en.wikipedia.org/wiki/Path-based_strong_component_algorithm>
 */
namespace Wikimedia\Cyclotron;

/**
 * One-off algorithm object
 * @package Wikimedia\Cyclotron
 */
class KosarajuComponents {

	/**
	 * @var Graph
	 */
	private $graph;

	private $visited;
	private $topo;

	private $roots = null;
	private $components = null;
	
	public function __construct( Graph $graph ) {
		$this->graph = $graph;

		$this->visited = [];
		$this->topo = [];
		$this->roots = [];
	}

	public function getComponents() {
		if ( $this->components !== null ) {
			return $this->components;
		}
		$this->components = [];

		$vertices = $this->graph->getVertices();
		if ( !$vertices ) {
			return $this->components;
		}

		// topo-sort
		foreach ( $vertices as $v ) {
			$this->visit( $v );
		}

		foreach ( $this->topo as $v ) {
			$this->assign( $v, $v );
		}

		return $this->components;
	}

	public function getComponentSubgraphs() {
		$components = $this->getComponents();
		$subgraphs = [];

		foreach ( $components as $c ) {
			$subgraphs[] = Graph::newSubgraph( $c, $this->graph );
		}

		return $subgraphs;
	}

	private function visit( $v ) {
		if ( $this->visited[$v] ?? false ) {
			return;
		}

		$this->visited[$v] = true;

		foreach ( $this->graph->getOutNeighbours( $v ) ?? [] as $w ) {
			$this->visit( $w );
		}

		array_unshift( $this->topo, $v );
	}

	private function assign( $v, $root ) {
		if ( $this->roots[$v] ?? null ) {
			return;
		}

		$this->roots[$v] = $root;
		$this->components[$root][] = $v;

		foreach ( $this->graph->getInNeighbours( $v ) ?? [] as $w ) {
			$this->assign( $w, $root );
		}
	}

}
