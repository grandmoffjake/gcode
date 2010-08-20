<?php
$files = scandir( "./data/" );
?>
<html>
Filename:
<input type="text" id="iframe_save_filename">
<ul>
<?php foreach ( $files as $file ) { ?>
	<?php if ( substr( $file, -6 ) == ".gcode" ) { ?>
	<li><?php echo $file ?></li>
	<?php } ?>
<?php } ?>
</ul>
<button id="iframe_save_button">Save</button>
</html>