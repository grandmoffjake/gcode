<?php
	require "lib/class.GCode.php";
	$codes = GCode::getGCodes();
?>
<html>
<head>
<title>Build a Program</title>
<link rel="stylesheet" type="text/css" href="css/reset-fonts-grids.css"/>
<style>
#main {
	margin-left: 50px;
	text-align: left;
}
input {
	width: 40px;
}
td {
	padding: 3px;
}
li:nth-child(2n) td {
	background-color: #ccc;
}
</style>
</head>
<body>
<div id="main">
	<div id="add_container">
		<select id="gcode_add">
		<?php foreach( $codes as $code ) { ?>
			<option value="<?php echo $code ?>">G<?php echo $code ?></option>
		<?php } ?>
		</select>
		<input type="checkbox" id="autoindex" value="1"> <label for="autoindex">Auto Index Enabled on Machine</label>
		<button id="add_code">Add Step</button>
	</div>
	<form method="POST" action="program.php">
		Your Program:
		<ul id="program">
		</ul>
	</form>
	<div id="end_container">
	</div>
</div>
</body>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
(function(){
	function GProgram() {
		$("#add_code").bind( "click",this.addStep );
		this.steps = 0;
	}
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
		data += "<td>G"+code+"<input type='hidden' class='parameter param_g' value='"+code+"'/></td>";
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
		header += "<td>&nbsp;</button>";
		data += "<td valign='middle'><button class='delete'>X</button>";
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
//				li.parentNode.insertBefore( li.parentNode.removeChild( li ), li.previousSibling );
				var prev = parseInt( $( ".param_num", li.prev() ).val() );
			}
		} else if ( next < num ) {
			while( next < num && isNaN( next ) == false ) {
				li.next().after( li.remove() );
//				li.parentNode.insertBefore( li.parentNode.removeChild( li ), li.nextSibling.nextSibling );
				var next = parseInt( $( ".param_num", li.next() ).val() );
			}
		}
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