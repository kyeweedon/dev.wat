<?php	
	
	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	if(isset($_POST['json'])) {
		
		$json = json_decode($_POST['json'], true);
		$reply;
		$reply['connectionType'] = 'auth';
	
		require('/var/www/html/app/authenticate.php');
		
		// If credentials are valid (1 record returned)
		if($user = authenticate($json['userName'], $json['password'])) {
			
			$reply['status']    = 'good';
			$reply['firstName'] = $user['firstName'];
			$reply['lastName']  = $user['lastName'];
			
		}
		// Invalid credentials
		else {
			
			$reply['status'] = 'bad';
			
		}
		
	}
	else {
		
		$reply['status'] = 'bad';
		
	}
	
	echo json_encode($reply);
	
?>