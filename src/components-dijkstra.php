<?php
/*
 * Calculates strongly connected components of a graph in linear time. 
 * See <https://en.wikipedia.org/wiki/Path-based_strong_component_algorithm>
 */

// FIXME: find the bug!

function readEdges( $file = null ) {
	$edges = [];
	$f = fopen( $file ?: 'php://stdin', 'r' );
	while ( $s = fgets( $f ) ) {
		if ( preg_match( '!^\s*(\w+)[\s,:;/]+(\w+)\s*$!', $s, $m ) ) {
			$a = $m[1];
			$b = $m[2];
			
			$edges[] = [ $a, $b ];
		}
	}

	if ( $file  ) {
		fclose( $f );
	}

	return $edges;
} 

class DijkstraGraph {
	
	private $edges;
	private $adjecency;
	private $vertices;

	private $pending;
	private $candidates;
	private $ordinals;
	private $counter;
	private $done;

	private $components = null;
	
	public function __construct( $edges ) {
		$this->edges = $edges;
		$this->adjecency = self::computeAdjecency( $edges );
		$this->vertices = array_keys( $this->adjecency );
		$this->pending = [];
		$this->candidates = [];
		$this->ordinals = [];
		$this->counter = 0;
		$this->done = [];
	}
	
	private static function computeAdjecency( $edges ) {
		$adjecency = [];
		
		foreach ( $edges as $e ) {
			$a = $e[0];
			$b = $e[1];
			
			$adjecency[$a][] = $b;
		}
		
		return $adjecency;
	}

	
	public function getComponents() {
		if ( $this->components !== null ) {
			return $this->components;
		}
		$this->components = [];
		$this->done = [];
		
		if ( !$this->vertices ) {
			return $this->components;
		}

		foreach ( $this->vertices as $v ) {
			$this->walk( $v );
		}
		#$root = $this->vertices[0];
		#$this->walk( $root );

		return $this->components;
	}
	
	private function walk( $v ) {
		$this->ordinals[$v] = $this->counter;
		$this->counter += 1;
		$this->pending[] = $v;
		$this->candidates[] = $v;

		foreach ( $this->adjecency[$v] ?? [] as $w ) {
			if ( !isset( $this->ordinals[$w] ) ) {
				$this->walk( $w ); 
			} elseif ( !isset( $this->done[$w] ) ) {
				$ord = $this->ordinals[$w];
				
				while ( $this->candidates ) {
					$top = end( $this->candidates );
					if ( $this->ordinals[$top] <= $ord ) {
						break;
					}
					array_pop( $this->candidates );
				}
			}
		}
		
		$top = end( $this->candidates );
		if ( $v === $top ) {
			$comp = [];
			
			do {
				$x = array_pop( $this->pending );
				$comp[] = $x;
				$this->done[$x] = true;
			} while ( $x != $v );
			
			$this->components[] = $comp;
		}
	}
}

$file = $argv[1] ?? null;

$edges = readEdges( $file );
$graph = new Graph( $edges );
$components = $graph->getComponents();

print_r( $components );


