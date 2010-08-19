<?php

class GProgram {
	
	protected $blocks = array();
	protected $steps = array();
	
	public function __construct() {
		
	}
	
	public function addBlock( GCode $gcode ) {
		$gcode->validate();
		$this->blocks[] = $gcode;
	}
	
	public function validate() {
		//Must have a G03
		//No 2 blocks can have the same number
	}
}