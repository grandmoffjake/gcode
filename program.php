<?php
	require "lib/class.GCode.php";
	require "lib/class.GProgram.php";
	
	header( "Content-type: application/json" );
	
	$saved = false;
	$autoindex = isset( $_REQUEST['program_autoindex'] ) ? true : false;
	try {
		$program = new GProgram( 
			$_REQUEST['program_variable_x'],
			$_REQUEST['program_variable_y'],
			$_REQUEST['program_variable_a'],
			$_REQUEST['program_variable_b'],
			$_REQUEST['program_variable_c'],
			$_REQUEST['program_variable_d'],
			$_REQUEST['program_variable_e'],
			$_REQUEST['program_variable_h'],
			$autoindex
		);
		
		$params = array();
		foreach ( $_REQUEST as $key => $val ) {
			if ( substr( $key, 0, 6 ) == "param_" ) {
				$keyarr = explode( "_", $key );
				if ( isset( $params[$keyarr[1]] ) == false ) {
					$params[$keyarr[1]] = array();
				}
				$params[$keyarr[1]][$keyarr[2]] = $val;
			}
		}
		
		foreach ( $params as $code ) {
			$gcode = new GCode( $code["G"], $autoindex );
			foreach ( $code as $key => $value ) {
				if ( $key != "G" ) {
					$gcode->$key = $value;
				}
			}
			
			$program->addBlock( $gcode );
		}
		
		if ( isset( $_REQUEST['tool_def'] ) && sizeof( $_REQUEST['tool_def'] ) ) {
			foreach ( $_REQUEST['tool_def'] as $num => $val ) {
				$program->addToolDefinition( $val, $_REQUEST['tool_a'][$num], $_REQUEST['tool_f'][$num] );
			}
		}
		
		if ( isset( $_REQUEST['filename'] ) ) {
			$filename = basename( $_REQUEST['filename'] );
			if ( substr( $filename, -6 ) != ".gcode" )
				$filename .= ".gcode";
			
			file_put_contents( "data/{$filename}", serialize( $program ) );
			$saved = true;
		}
		
		$program->validate();
	} catch ( Exception $e ) {
		echo json_encode( array( "result" => "fail", "reason" => $e->getMessage(), "filename" => $filename, "saved" => $saved ) );
		exit(1);
	}
	
	echo json_encode( array( "result" => "success", "filename" => $filename ) );