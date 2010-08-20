<?php
	require "lib/class.GProgram.php";
	require "lib/class.GCode.php";
	$codes = GCode::getGCodes();
	
	$program = new stdClass();
	$program->blocks = array();
	$program->tool_definitions = array();
	
	$obj = false;
	
	if ( $_REQUEST['filename'] ) {
		$filename = basename( $_REQUEST['filename'] );
		if ( substr( $filename, -6 ) == ".gcode" ) {
			$obj = file_get_contents( "data/{$filename}" );
			$program = unserialize( $obj );
		}
	}
?>
<html>
<head>
<title>Build a Program</title>
<link rel="stylesheet" type="text/css" href="css/ui-lightness/jquery-ui-1.8.4.custom.css"/>
<link rel="stylesheet" type="text/css" href="css/reset-fonts-grids.css"/>
<style>
#current_program {
	width: 100%;
	text-align: center;
	background-color: #CC9900;
}
#main {
	text-align: left;
}
#program_variables, #add_container, #tooling_definitions, #end_container {
	margin-left: 50px;
}
#tool_row {
	display: none;
}
#program_variables input, #add_container input, #tooling_definitions input, #end_container input {
	width: 40px;
}
#program_variables td, #add_container td, #tooling_definitions td, #end_container td {
	padding: 3px;
}
li:nth-child(2n) td {
	background-color: #ccc;
}

.dialog {
	height: 200px;
	width: 200px;
	display: none;
}
.dialog li {
	cursor:pointer;
}
</style>
</head>
<body>
<div id="save_dialog" class="dialog">
</div>
<form id="program_form" method="POST" action="program.php">
<div id="main">
	<div id="current_program">
		<?php 
			if ( $obj ) {
				echo $filename; 
				try { 
					$program->validate();
				} catch ( Exception $e ) {
					echo "<div>".$e->getMessage()."</div>";
				}
			} 
		?>
	</div>
	<div id="program_variables">
		<input type="hidden" id="gcode_filename" value="<?php echo isset( $filename ) ? $filename : "" ?>"/>
		<table>
			<thead><tr>
				<td>X</td>
				<td>Y</td>
				<td>A</td>
				<td>B</td>
				<td>C</td>
				<td>D</td>
				<td>E</td>
				<td>H</td>
			</tr></thead>
			<tbody><tr>
				<td><input type="text" id="program_variable_x" name="program_variable_x" value="<?php echo ( $program->x ) ? $program->x : 33.5 ?>"/></td>
				<td><input type="text" id="program_variable_y" name="program_variable_y" value="<?php echo ( $program->y ) ? $program->y : 24.0 ?>"/></td>
				<td><input type="text" id="program_variable_a" name="program_variable_a" value="<?php echo ( $program->a ) ? $program->a : 87 ?>"/></td>
				<td><input type="text" id="program_variable_b" name="program_variable_b" value="<?php echo ( $program->b ) ? $program->b : 300 ?>"/></td>
				<td><input type="text" id="program_variable_c" name="program_variable_c" value="<?php echo ( $program->c ) ? $program->c : 0 ?>"/></td>
				<td><input type="text" id="program_variable_d" name="program_variable_d" value="<?php echo ( $program->d ) ? $program->d : 0 ?>"/></td>
				<td><input type="text" id="program_variable_e" name="program_variable_e" value="<?php echo ( $program->e ) ? $program->e : 0 ?>"/></td>
				<td><input type="text" id="program_variable_h" name="program_variable_h" value="<?php echo ( $program->h ) ? $program->h : 2 ?>"/></td>
			</tr></tbody>
		</table>
		<input type="checkbox" id="autoindex" name="program_autoindex" value="1"<?php echo ( $program->autoindex ) ? " checked='checked'" : "" ?>> <label for="autoindex">Auto Index Enabled on Machine</label>
	</div>
	<hr>
	<div id="add_container">
		Your program:
		<select id="gcode_add">
		<?php foreach( $codes as $code ) { ?>
			<option value="<?php echo $code ?>">G<?php echo $code ?></option>
		<?php } ?>
		</select>
		<button id="add_code" type="button">Add Step</button>
		<ul id="program">
		<?php 
		
				foreach( $program->blocks as $num => $gcode ) {
		?>
			<li>
				<table border='1' cellpadding='2'>
		<?php
					$valid = $gcode->getValid();
					$header = "<thead><tr>";
					$data = "<tbody><tr>";
					
					$code = $gcode->G;
					
					$header .= "<td>&nbsp;</td>";
					$data .= "<td>G{$code}<input type='hidden' class='parameter param_g' name='param_{$num}_G' value='{$code}'/></td>";
						
					foreach ( $valid as $property ) {
						$header .= "<td align='center'>{$property}</td>";
						$data .= "<td><input type='text' class='parameter param_{$property}'";
						$data .= " name='param_{$num}_{$property}'";
						$data .= " id='param_{$num}_{$property}'";
						$data .= " value='{$gcode->$property}'";
						$data .= "/></td>";
					}
					$header .= "<td>&nbsp;</td>";
					$data .= "<td valign='middle'><button class='delete'>X</button></td>";
					$header .= "</tr></thead>";
					$data .= "</tr></tbody>";
		
					echo $header;
					echo $data;
		?>
				</table>
			</li>
		<?php
			}
		?>
		</ul>
	</div>
		
	<hr>
	<div id="tooling_definitions">
		Tool definitions
		<table>
			<thead><tr>
				<td>T</td>
				<td>F</td>
				<td>A</td>
				<td>&nbsp;</td>
			</tr></thead>
			<tbody>
				<tr id="tool_row">
					<td><select data-name="tool_def">
					<?php for ( $i = 1; $i <= 24; $i++ ) { ?>
						<option><?php echo str_pad( $i, 2, "0", STR_PAD_LEFT ) ?></option>
					<?php } ?>
					</select></td>
					<td><input type="text" data-name="tool_f"/></td>
					<td><input type="text" data-name="tool_a"/></td>
					<td><button type="button">X</button></td>
				</tr>
				<?php 
					foreach( $program->tool_definitions as $tool ) {
				?>
				<tr>
					<td><select name="tool_def[]">
					<?php for ( $i = 1; $i <= 24; $i++ ) { ?>
						<option <?php if ( $i == intval( $tool[0] ) ) { echo " selected"; } ?>><?php echo str_pad( $i, 2, "0", STR_PAD_LEFT ) ?></option>
					<?php } ?>
					</select></td>
					<td><input type="text" name="tool_f[]" value="<?php echo $tool[1] ?>"/></td>
					<td><input type="text" name="tool_a[]" value="<?php echo $tool[2] ?>"/></td>
					<td><button type="button">X</button></td>
				</tr>
				<?php 
					}
				?>
			</tbody>
		</table>
		<button id="tool_add" type="button">Add Tool</button>
	</div>
	<hr>
	<div id="end_container">
		<button id="gcode_save" type="submit" name="save">Save</button>
		<button id="gcode_load" type="submit" name="load">Load</button>
		<button id="gcode_run" type="submit" name="run" <?php if ( ! $obj ) { ?>disabled="disabled"<?php } ?>>Run</button>
	</div>
</div>
</form>
</body>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript">
(function(){
	function GProgram() {
		$("#add_code").bind( "click",this.addStep );
		$("#tool_add").bind( "click",this.addTool );
		$("#program_form").bind( "submit",this.handleForm );
		this.steps = <?php echo sizeof( $program->blocks ) ?>;
		this.tools = <?php echo sizeof( $program->tool_definitions ) ?>;
		
		$(".delete").bind( "click", this.deleteRow );
		$(".parameter").bind( "blur", this.checkProperty );
	}
	GProgram.prototype.handleForm = function( e ) {
		e.preventDefault();
		var button = e.originalEvent.explicitOriginalTarget;
		
		if ( button ) {
			if ( button.id == "gcode_save" ) {
				$("#save_dialog").children().unbind();
				$("#save_dialog").load( "save.php", function( response, status, xhr ) {
					$("#save_dialog li").bind( "click", function(e) {
						$("#iframe_save_filename").val( $(this).html() );
					} );
					$("#iframe_save_button").bind( "click", function(e) {
						var filename = $("#iframe_save_filename").val();
						if ( ! filename ) {
							alert( "No filename given" );
							return;
						}
						
						var data = $("#program_form").serialize();
						data += "&filename="+filename;
						
						$.post( "program.php", data, function(o) {
							if ( o.result == "fail" ) {
								alert( "Save successful, but program did not validate:\n"+o.reason );
							}
							
							$("#current_program").html( o.filename );
							$("#gcode_filename").val( o.filename );
							$("#gcode_run").attr( "disabled", null );
							
							$("#save_dialog").dialog("close");
						} );
					} );
					$("#save_dialog").dialog({modal:true});
				} );
			} else if ( button.id == "gcode_load" ) {
				$("#save_dialog").load( "load.php", function( response, status, xhr ) {
					$("#save_dialog").dialog({modal:true});
				} );
			} else if ( button.id == "gcode_run" ) {
				var filename = $("#gcode_filename").val();
				if ( filename ) {
					$("#save_dialog").html( "Running program..." );
					$("#save_dialog").load( "send.php?filename="+filename, function( response, status, xhr ) {
						
					} );
					$("#save_dialog").dialog({modal:true});
				}
			}
		}
	};
	GProgram.prototype.addStep = function() {
		var code = $("#gcode_add").val();
		var autoindex = ( $("#autoindex").is(':checked') ) ? 1 : 0;
		
		$.getJSON( "ajax.php", {
			action: "getProperties",
			code: code,
			autoindex: autoindex
		}, gp.buildStep );
	};
	GProgram.prototype.buildStep = function( o ) {
		var li = document.createElement( "li" );
		var html = "<table border='1' cellpadding='2'>";
		var header = "<thead><tr>";
		var data = "<tbody><tr>";
		var code = $("#gcode_add").val();
		
		header += "<td>&nbsp;</td>";
		data += "<td>G"+code+"<input type='hidden' class='parameter param_g' name='param_"+gp.steps+"_G' value='"+code+"'/></td>";
		for( var i = 0; i < o.length; i++ ) {
			header += "<td align='center'>"+o[i]+"</td>";
			data += "<td><input type='text' class='parameter param_"+o[i]+"'";
			data += " name='param_"+gp.steps+"_"+o[i]+"'";
			data += " id='param_"+gp.steps+"_"+o[i]+"'";
			if ( o[i] == "num" ) {
				var val = parseInt( $("#program li:last .param_num").val() );
				if ( isNaN( val ) ) {
					val = 0;
				} else if ( val > 254 ) {
					val = 254;
				}
				data += " value='"+(val+1)+"'";
			}
			data += "/></td>";
		}
		header += "<td>&nbsp;</td>";
		data += "<td valign='middle'><button class='delete'>X</button></td>";
		header += "</tr></thead>";
		data += "</tr></tbody>";
		html += header + data + "</table>";
		
		li.innerHTML = html;
		
		$(".delete", li).bind( "click", gp.deleteRow );
		$(".parameter", li).bind( "blur", gp.checkProperty );
		$("#program").append( li );
		
		gp.steps++;
	};
	GProgram.prototype.checkProperty = function( e ) {
		if ( $(this).val() ) {
			var li = $(this).closest( "li" );
			var code = $( ".param_g", li ).val();
			var property = $(this).attr("name").replace( /param\_[0-9]+\_/, "" ).toUpperCase();
			var autoindex = ( $("#autoindex").is(':checked') ) ? 1 : 0;
			
			$.getJSON( "ajax.php", {
				action: "validateProperty",
				autoindex: autoindex,
				code: code,
				property: property,
				value: $(this).val(),
				id: $(this).attr("id")
			}, function ( data ) {
				if ( data.result == "success" ) {
					if ( data.property == "num" ) {
						gp.reorderRows( data.id );
					}
				} else if ( data.result == "fail" ) {
					alert( data.reason );
					$("#"+data.id).val("").focus();
				}
			} );
		}
	};
	GProgram.prototype.reorderRows = function ( id ) {
		var num = parseInt( $("#"+id).val() );
		var li = $("#"+id).closest("li");
		
		var prev = parseInt( $( ".param_num", li.prev() ).val() );
		var next = parseInt( $( ".param_num", li.next() ).val() );
		
		if ( prev > num ) {
			while( prev > num && isNaN( prev ) == false ) {
				li.prev().before( li.remove() );
				var prev = parseInt( $( ".param_num", li.prev() ).val() );
			}
		} else if ( next < num ) {
			while( next < num && isNaN( next ) == false ) {
				li.next().after( li.remove() );
				var next = parseInt( $( ".param_num", li.next() ).val() );
			}
		}
	};
	GProgram.prototype.addTool = function( e ) {
		var tr = $("#tool_row");
		var newRow = tr.clone();
		
		$(newRow).attr("id", "tool_row_"+gp.tools++ );
	
		$($("select", newRow)[0]).attr( "name", $($("select", newRow)[0]).attr("data-name")+"[]" );
		$($("input", newRow)[0]).attr( "name", $($("input", newRow)[0]).attr("data-name")+"[]" );
		$($("input", newRow)[1]).attr( "name", $($("input", newRow)[1]).attr("data-name")+"[]" );
		
		$("button", newRow).bind( "click", gp.deleteTool );
		
		tr.parent().append( newRow );
	};
	GProgram.prototype.deleteTool = function ( e ) {
		var tr = $(this).closest("tr");
		$("button",tr).unbind();
		
		$(tr).remove();
	};
	GProgram.prototype.deleteRow = function ( e ) {
		var li = $(this).closest( "li" );
		$(li).unbind();
		$(li).remove();
	};
	gp = new GProgram();
})();
</script>
</html>