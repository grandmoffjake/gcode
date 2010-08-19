<?php
$code = 200;
if ( ! $_REQUEST['action'] ) {
	$code = 403;
	die( "No action" );	
}
if ( ! $_REQUEST['code'] ) {
	$code = 403;
	die( "No code" );
}

header( "Content-type: application/json", true, $code );;
require "lib/class.GCode.php";
if ( $_REQUEST['action'] == "getProperties" ) {
	$gcode = new GCode( $_REQUEST['code'], $_REQUEST['autoindex'] );
	echo( json_encode( $gcode->getValid() ) );
	exit(1);
} else if ( $_REQUEST['action'] == "validateProperty" ) {
	if ( $_REQUEST['property'] == "NUM" )
		$_REQUEST['property'] = "num";
		
	$gcode = new GCode( $_REQUEST['code'], $_REQUEST['autoindex'] );
	try {
		$gcode->{$_REQUEST['property']} = $_REQUEST['value'];
	} catch ( Exception $e ) {
		echo( json_encode( array( "result" => "fail", "reason" => $e->getMessage(), "id" => $_REQUEST['id'], "property" => $_REQUEST['property'] ) ) );
		exit(1);
	}
	
	echo( json_encode( array( "result" => "success", "id" => $_REQUEST['id'], "property" => $_REQUEST['property'] ) ) );
	exit(1);
}