<?php 
$type=$_GET['type'];
$file="$type.apk";


if (file_exists("pages/$file")) {
	header('Content-Disposition: attachment; filename="'.basename($file).'"');
	ob_clean();
    flush();
	
	readfile("pages/$file");
	
	exit;
} else {
	header('Content-Disposition: attachment;');
}

