<?php
	
	require_once('/var/www/html/mech/php/all.php');
	
	$table    = $_POST['table'];
	$key      = $_POST['key'];
	$value    = $_POST['value'];
	$index    = $_POST['index'];
	$selector = $_POST['selector'];
	
	$reply['status'] = 'bad';
	
	// Get all companies who are insurers
	$sql =
		'UPDATE '.
			$table .
		' SET '.
			$key . ' = ' . $value .
		' WHERE '.
			$index . ' = ' . $selector
	;
	
	if(mysql_query($sql)) {
		
		$reply['status'] = 'good';
	
	}
	else {
		
		debug($sql);
		
	}
	
	// Return Inspectors as json
	echo json_encode($reply);
	
?>