<?php

class GCode {
	/*Program Step Number*/
	protected $num;
	
	/*GCode*/
	protected $G;
	/*X-axis value*/
	protected $X;
	/*Y-axis value*/
	protected $Y;
	/*X-direction pitch or tooling dimension*/
	protected $I;
	/*Y-direction pitch or tooling dimension*/
	protected $J;
	/*Tooling rotation angle (when auto index is used)*/
	protected $C;
	/*Punch diameter or micro-joint width*/
	protected $P;
	/*Punch diameter or micro-joint width*/
	protected $D;
	/*Nibbling length or progressive slot length*/
	protected $L;
	/*Punching radius or progressive slot length*/
	protected $R;
	/*Starting angle*/
	protected $A;
	/*Ending angle or angle pitch*/
	protected $B;
	/*No. of hits (patterns) in X direction*/
	protected $H;
	/*No. of hits (patterns) in Y direction*/
	protected $K;
	/*Nibbling pitch*/
	protected $S;
	/*Tool number*/
	protected $T;
	/*Table feeding speed*/
	protected $F;
	/*Repeat start block number*/
	protected $V;
	/*Repeat end block number*/
	protected $W;
	/*Selection of angle C definition (auto index only)*/
	protected $M;
	
	protected $autoIndex = false;
	
	protected static $validGCodes = array(
		"00", "01", "02", "03", "05",
		"10", "11", 
		"20", "21",
		"50", "51",
		"61", "62", "63", "64", "65", "66",
		"70", "71", "72", "73", "74",
		"80", "81", "83", "84",
		"90", "91", "95", "96", "97", "98"
	);
	protected $validProperties;
	
	public function __construct( $code, $autoIndex = false ) {
		$codes = GCode::$validGCodes;
		if ( in_array( $code, $codes ) === false ) {
			throw new Exception( "Invalid code" );
		}
		if ( $this->G == "62" && ! $this->autoIndex ) {
			throw new Exception( "G62 is only usable with auto index" );
		}
		
		$this->G = $code;
		$this->autoIndex = (bool) $autoIndex;
		
		$this->validProperties = $this->getValid();
	}
	
	public static function getGCodes() {
		return self::$validGCodes;
	}
	
	public function __set( $name, $value ) {
		if ( in_array( $name, $this->validProperties ) === false )
			throw new Exception( "Property $name is not available for G{$this->G}" );
		switch( $name ) {
			case "num":
			case "H":
			case "K":
			case "V":
			case "W":
				$value = intval( $value );
				if ( $value < 1 || $value > 255 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between 1 and 255 for code G{$this->G}" );
				break;
			case "X":
			case "Y":
			case "I":
			case "J":
			case "L":
			case "R":
				$value = floatval( $value );
				if ( $value < -9999.99 || $value > 9999.99 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between -9999.99 and 9999.99 for code G{$this->G}" );
				break;
			case "C":
			case "A":
			case "B":
				$value = floatval( $value );
				if ( $value < -360 || $value > 360 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between -360.00 and 360.00 for code G{$this->G}" );
				break;
			case "P":
			case "D":
				$value = floatval( $value );
				if ( $value < -300 || $value > 300 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between -300.00 and 300.00 for code G{$this->G}" );
				break;
			case "S":
				$value = intval( $value );
				if ( $value < 1 || $value > 10 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between 1 and 10 for code G{$this->G}" );
				break;
			case "T":
				$value = intval( $value );
				if ( $value < 1 || $value > 24 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between 1 and 24 for code G{$this->G}" );
				break;
			case "F":
				$value = intval( $value );
				if ( $value < 1 || $value > 40 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be between 1 and 40 for code G{$this->G}" );
				break;
			case "M":
				$value = intval( $value );
				if ( $value < 0 || $value > 2 )
					throw new Exception( "Block {$this->num}: Value for {$name} out of range.  Must be 0, 1, or 2 for code G{$this->G}" );
				break;
		}
		
		$this->$name = $value;
	}
	
	public function __get( $name ) {
		if ( isset( $this->$name ) ) {
			return $this->$name;
		}
	}
	
	public function getValid() {
		switch ( $this->G ) {
			case "01":
			case "02":
				$valid = array( "X", "Y", "T", "F" );
				break;
			case "03":
			case "00":
			case "21":
			case "91":
			case "90":
			case "11":
			case "96":
			case "98":
				$valid = array();
				break;
			case "05":
				$valid = array( "Y", "F" );
				break;
			case "10":
				$valid = array( "X", "F" );
				break;
			case "71":
				if ( ! $this->autoIndex )
					$valid = array( "X", "Y", "A", "T", "F" );
				else
					$valid = array( "X", "Y", "A", "I", "C", "H", "M", "T", "F" );
				break;
			case "72":
				if ( ! $this->autoIndex )
					$valid = array( "X", "Y", "R", "A", "B", "H", "T", "F" );
				else
					$valid = array( "X", "Y", "R", "A", "I", "C", "M", "T", "F" );
				break;
			case "80":
				$valid = array( "X", "Y", "J", "L", "A", "H", "S", "T", "F" );
				break;
			case "81":
				$valid = array( "X", "Y", "P", "R", "A", "B", "H", "S", "T", "F" );
				break;
			case "61":
				if ( ! $this->autoIndex )
					$valid = array( "X", "Y", "I", "J", "P", "L", "R", "A", "H", "T", "F" );
				else
					$valid = array( "X", "Y", "I", "J", "P", "L", "R", "A", "H", "C", "M", "T", "F" );
				break;
			case "62":
				$valid = array( "X", "Y", "I", "J", "C", "P", "R", "A", "B", "H", "M", "T", "F" );
				break;
			case "73":
			case "74":
				if ( ! $this->autoIndex )
					$valid = array( "X", "Y", "I", "J", "H", "K", "T", "F" );
				else
					$valid = array( "X", "Y", "I", "J", "C", "A", "H", "K", "M", "T", "F" );
				break;
			case "50":
				$valid = array( "X", "Y", "I", "C", "A", "H", "M", "T", "F" );
				break;
			case "51":
				$valid = array( "X", "Y", "I", "J", "P", "R", "H", "T", "F" );
				break;
			case "63":
			case "64":
				if ( ! $this->autoIndex )
					$valid = array( "X", "Y", "I", "J", "P", "L", "R", "T", "F" );
				else
					$valid = array( "X", "Y", "I", "J", "P", "L", "R", "A", "C", "M", "T", "F" );
				break;
			case "65":
			case "66":
				if ( ! $this->autoIndex ) 
					$valid = array( "X", "Y", "I", "J", "L", "R", "T", "F" );
				else
					$valid = array( "X", "Y", "I", "J", "L", "R", "A", "C", "M", "T", "F" );
				break;
			case "70":
				$valid = array( "F", "V", "W" );
				break;
			case "83":
			case "84":
				$valid = array( "X", "Y", "I", "J", "D", "H", "K", "V", "W" );
				break;
			case "20":
				$valid = array( "X", "Y" );
				break;
			case "95":
			case "97":
				$valid = array( "V" );
				break;
			default:
				throw new Exception( "Error.  Untrapped code" );
				break;
		}
		array_unshift( $valid, "num" );
		
		return $valid;
	}
	
	public function validate() {
		if ( ! $this->num ) {
			throw new Exception( "Code {$this->G} missing block number" );
		}
		foreach( $this->validProperties as $p ) {
			if ( is_null( $this->$p ) ) {
				throw new Exception( "Block {$this->num}: property $p not set for code G{$this->G}" );
			}
		}
		
		switch( $this->G ) {
			case "61":
				if ( $this->I == 0 )
					throw new Exception( "Block {$this->num}: I cannot be zero in G{$this->G}" );
				if ( $this->J == 0 )
					throw new Exception( "Block {$this->num}: J cannot be zero in G{$this->G}" );
				if ( $this->H > 254 )
					throw new Exception( "Block {$this->num}: H must be less than 255 in G{$this->G}" );
				break;
			case "63":
			case "64":
				if ( $this->I == 0 )
					throw new Exception( "Block {$this->num}: I cannot be zero in G{$this->G}" );
				if ( $this->J == 0 )
					throw new Exception( "Block {$this->num}: J cannot be zero in G{$this->G}" );
				if ( $this->I >= $this->L )
					throw new Exception( "Block {$this->num}: I must be less than L in G{$this->G}" );
				if ( 3*$this->J + 2*$this->P >= $this->R ) 
					throw new Exception( "Block {$this->num}: R must be less than (3xJ + 2xP) in G{$this->G}" );
				break;
			case "65":
			case "66":
				if ( $this->I == 0 )
					throw new Exception( "Block {$this->num}: I cannot be zero in G{$this->G}" );
				if ( $this->J == 0 )
					throw new Exception( "Block {$this->num}: J cannot be zero in G{$this->G}" );
				if ( $this->I >= $this->L )
					throw new Exception( "Block {$this->num}: I must be less than L in G{$this->G}" );
				if ( $this->J >= $this->R )
					throw new Exception( "Block {$this->num}: J must be less than R in G{$this->G}" );
				break;
		}
		
		return true;
	}
}