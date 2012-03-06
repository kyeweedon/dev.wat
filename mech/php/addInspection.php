<?php
	require_once('/var/www/html/mech/php/all.php');
	
	$reply = array();
	
	$reply['status'] = 'bad';
	
	$sql = ''                .
		'INSERT INTO '       .
			'Inspection '    .
		'SET '               .
			'jobID = '       . $_POST['jobID']       . ', ' .
			'creatorID = '   . $_SESSION['userID']   . ', ' .
			'assignedID = '  . $_POST['inspectorID'] . ', ' .
			'isCausation = ' . $_POST['isCausation'] . ', ' .
			'isScope = '     . $_POST['isScope']     . ', ' .
			'dateCreated = ' . time()                . ', ' .
			'isNew = '       . '1'                   . ', ' .
			'isLive = '      . '1' .
		'';

	//debug('Query defined...');
	//debug($sql);
	
	if(mysql_query($sql)) {
	   
	   // Update Job status
	   $sql = 
			'UPDATE '        .
				'Job '       .
			'SET '           .
				'status = "1.0" ' .
			'WHERE '         .
				'jobID = '   . $_POST['jobID']  .
			'';
		
		if(mysql_query($sql)) {
		
			// Update Job status
			$reply['status'] = 'good';
			
		}
		else {
			
			$reply['status'] = 'bad';
			
		}
	   
	}
	else {
		
	  $reply['status'] = 'bad';
	  
	}
	
	// Return status
	echo json_encode($reply);
   
?>