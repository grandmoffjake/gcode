<?php
$files = scandir( "./data/" );
?>
<html>
<ul>
<?php foreach ( $files as $file ) { ?>
	<?php if ( substr( $file, -6 ) == ".gcode" ) { ?>
	<li><a target="_top" href="index.php?filename=<?php echo $file ?>"><?php echo $file ?></a></li>
	<?php } ?>
<?php } ?>
</ul>
</html>