<?php
	require_once('/var/www/html/mech/php/all.php');
	
	// Define {
	$reply;
	$elementIDs  = $_POST['elementIDs'];
	$jobID       = $_POST['jobID'];
	$sc          = 0;
	// }
	
	// Get Job Image {
	$sql =
		'SELECT ' .
			'imageThumb, ' .
			'imageFull ' .
		'FROM ' .
			'Image ' .
		'WHERE ' .
			'isLive = 1 AND ' .
			'jobID = ' . $jobID
	;
	//debug($sql);
	// Try query
	if($images = mysql_query($sql)) {
		
		if(mysql_num_rows($images) == 1) {
			
			$image = mysql_fetch_assoc($images);
			$reply['job'] = $image;
		}
		else {
			$reply['job'] = 'none';
		}
		
		$sc++;
		
	} // }
	
	// Get Element Images {
	foreach($elementIDs as $k => $v) {
		
		$sql =
			'SELECT ' .
				'imageThumb, ' .
				'imageFull ' .
			'FROM ' .
				'Image ' .
			'WHERE ' .
				'isLive = 1 AND ' .
				'elementID = ' . $v
		;
		//debug($sql);
		// Try query
		if($images = mysql_query($sql)) {
			
			if(mysql_num_rows($images) == 1) {
				
				$image = mysql_fetch_assoc($images);
				//debug('Image ' . $v . ' = ' . $image['imageThumb']);
				$reply['elements'][$v] = $image;
				
			}
			else {
				
				$reply['elements'][$v] = 'none';
					
			}
			
			$sc++;
		}
	} // }
	
	// Validate success {
	if($sc == count($_POST['elementIDs']) + 1) {
		
		$reply['status'] = 'good';
		
	}
	else {
		
		$reply['status'] = 'bad';
		$reply['msg']    = 'Failed to get Images';
		
	} // }
	
	echo json_encode($reply);
?>