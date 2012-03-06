<?php
	
	require_once('/var/www/html/mech/php/all.php');
	
	$table    = $_POST['table'];
	$key      = $_POST['key'];
	$index    = $_POST['index'];
	$selector = $_POST['selector'];
	
	$reply['status'] = 'bad';
	
	// Get all companies who are insurers
	$sql =
		'SELECT '.
			$key .
		' FROM '.
			$table .
		' WHERE '.
			$index . ' = ' . $selector
	;
	//debug($sql);
	if($record = mysql_query($sql)) {
		
		$record = mysql_fetch_assoc($record);
		
		$reply['status'] = 'good';
		$reply[$key] = $record[$key];
	
	}
	else {
		
		debug($sql);
		
	}
	
	// Return Inspectors as json
	echo json_encode($reply);
?>