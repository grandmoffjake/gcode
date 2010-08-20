<?php
header( "Content-type: text/plain" );
if ( ! $_REQUEST['filename'] ) {
	die( "No filename" );
}

require "lib/class.GProgram.php";
require "lib/class.GCode.php";
$filename = basename( $_REQUEST['filename'] );
if ( substr( $filename, -6 ) == ".gcode" ) {
	$obj = file_get_contents( "data/{$filename}" );
	$program = unserialize( $obj );
}

$program->validate();

echo $program->toString();