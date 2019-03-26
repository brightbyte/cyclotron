<?php
require __DIR__ . '/../vendor/autoload.php';

use Wikimedia\Cyclotron\Graph;
use Wikimedia\Cyclotron\JohnsonCycles;
use Wikimedia\Cyclotron\KosarajuComponents;


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

$file = $argv[1] ?? null;

$edges = readEdges( $file );
$graph = graph::newFromEdges( $edges );

/*
$kosaranju = new KosarajuComponents( $graph );
$components = $kosaranju->getComponentSubgraphs();

print "---------\n";
foreach ( $components as $c ) {
	print $c;
	print "---------\n";
}
*/

$johnson = new JohnsonCycles( $graph ) ;
$cycles = $johnson->getCycles();

print_r( $cycles );