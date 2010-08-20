<?php

class GProgram {
	protected $x;
	protected $y;
	protected $a;
	protected $b;
	protected $c;
	protected $d;
	protected $e;
	protected $h;
	
	protected $autoindex;
	protected $blocks = array();
	protected $steps = array();
	protected $tools = array();
	protected $tool_definitions = array();
	
	protected $failed = false;
	protected $reason;
	
	public function __construct( $x, $y, $a, $b, $c, $d, $e, $h, $autoindex = false ) {
		$this->x = $x;
		$this->y = $y;
		$this->a = $a;
		$this->b = $b;
		$this->c = $c;
		$this->d = $d;
		$this->e = $e;
		$this->h = $h;
		$this->autoindex = $autoindex;
	}
	
	public function __get( $name ) {
		if ( isset( $this->$name ) ) {
			return $this->$name;
		}
		
		return null;
	}
	
	public function addBlock( GCode $gcode ) {
		$this->blocks[] = $gcode;
		
		if ( $gcode->T ) {
			$this->tools[$gcode->T] = true;
		}
	}
	
	public function addToolDefinition( $tool, $a, $f ) {
		if ( isset( $this->tool_definitions[$tool] ) ) {
			$this->failed = true;
			$this->reason = "Duplicate tool definition for tool T$tool";
		}
		$this->tool_definitions[$tool] = array( $tool, $a, $f );
	}
	
	public function validate() {
		if ( $this->failed ) {
			throw new Exception( $this->reason );
		}
		
		foreach( $this->tools as $tool ) {
			$tool = str_pad( $tool, 2, "0", STR_PAD_LEFT );
			if ( isset( $this->tool_definitions[$tool] ) === false ) {
				throw new Exception( "Tool $tool has no definition" );
			}
		}
		
		//Must have a G03
		//No 2 blocks can have the same number
		//All tools must have a definition
		//No tool definitions should have the same tool number
	}
	
	public function toString() {
		$out = "0 X{$this->x} Y{$this->y} A{$this->a} B{$this->b} C{$this->c} D{$this->d} E{$this->e} H{$this->h}\n";
		foreach ( $this->blocks as $gcode ) {
			$valid = $gcode->validProperties;
			$out .= "N{$gcode->num} ";
			$out .= "G{$gcode->G} ";
			foreach( $valid as $property ) {
				if ( $property != "num" && $property != "G" ) {
					$out .= "{$property}{$gcode->$property} ";
				}
			}
			$out .= "\n";
		}
		$out .= "%\n";
		
		return $out;
	}
}