<?php
	
	// { Change Job Status
	function changeJobStatus($xTaskID, $xStatus) {
		
		$job;
		$task;
		$buildings   = array();
		$locations   = array();
		$elements    = array();
		$buildingIDs = array();
		$locationIDs = array();
		$elementIDs  = array();
		
		// { Get this Job
		$sql = 'SELECT j.jobID FROM Job j, Inspection i WHERE j.jobID = i.jobID AND i.inspectionID = ' . $xTaskID . ' AND i.isCurrent = 1';
		if($result = mysql_query($sql)) {
			
			$job = mysql_fetch_assoc($result);
			
		}
		else {
			
			debug($sql);
			
		}
		// } Get this Job
		
		debug('Setting Job ' . $job['jobID'] . ' to ' . $xStatus);
		
		// { Get this Task
		$sql = 'SELECT * FROM Inspection WHERE inspectionID = ' . $xTaskID . ' AND isCurrent = 1';
		if($result = mysql_query($sql)) {
			
			$task = mysql_fetch_assoc($result);
			
		}
		else {
			
			debug($sql);
			
		}
		// } Get this Task
		
		/*
		// { Get Buildings
		$sql = 'SELECT * FROM Building WHERE inspectionID = ' . $xTaskID;
		
		if($result = mysql_query($sql)) {
			
			while($building = mysql_fetch_assoc($result)) {
				
				array_push($buildings,   $building)              ;
				array_push($buildingIDs, $building['buildingID']);
				
			}
			
		}
		else {
			
			debug($sql);
			
		}
		// } Get Buildings
		
		// { Get Locations
		if(count($buildings) > 0) {
			
			$sql = 'SELECT * FROM Location WHERE buildingID IN(' . implode($buildingIDs, ', ') . ')';
			
			if($result = mysql_query($sql)) {
				
				while($location = mysql_fetch_assoc($result)) {
					
					array_push($locations,   $location)              ;
					array_push($locationIDs, $location['locationID']);
					
				}
				
			}
			else {
				
				debug($sql);
				
			}
		}
		// } Get Locations
		
		// { Get Elements
		if(count($locations) > 0) {
			
			$sql = 'SELECT * FROM Element WHERE locationID IN(' . implode($locationIDs, ', ') . ')';
			
			if($result = mysql_query($sql)) {
				
				while($element = mysql_fetch_assoc($result)) {
					
					array_push($elements,   $element)             ;
					array_push($elementIDs, $element['elementID']);
					
				}
				
			}
			else {
				
				debug($sql);
				
			}
			
		}
		// } Get Elements
		*/
		
		// { Create query
		$sql = 'UPDATE Job j, Inspection i SET j.status = ' . $xStatus . ', '; // i.lastChanged = i.lastChaned + 1, ';
		
		switch($xStatus) {
			
			// 0.1 Unassigned {
			case '0.1':
				$sql = $sql .
					'i.assignedID = 0, ' .
					'i.isCompleted = 0, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 0, ' .
					'i.isNew = 1, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 0 '
				;
				break;
			// }
			
			// 0.2 Unassigned (Declined) {
			case '0.2':
				
				$sql = $sql .
					'i.assignedID = 0, ' .
					'i.isCompleted = 0, ' .
					'i.isDeclined = 1, ' .
					'i.isAccepted = 0, ' .
					'i.isNew = 1, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 0 '
				;
				break;
			// }
			
			// 1.0 Assigned & Waiting {
			case '1.0':
				$sql = $sql .
					'i.isCompleted = 0, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 0, ' .
					'i.isNew = 1, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 0 '
				;
				break;
			// }
			
			// 1.2 Assigned & Waiting (Resub) {
			case '1.1':
				$sql = $sql .
					'i.isCompleted = 0, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 0, ' .
					'i.isNew = 1, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 1 '
				;
				break;
			// }
			
			// 2.0 Accepted {
			case '2.0':
				$sql = $sql .
					'i.isCompleted = 0, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 1, ' .
					'i.isNew = 0, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 0 '
				;
				break;
			// }
			
			// 2.1 Accepted (Resub) {
			case '2.1':
				$sql = $sql .
					'i.isCompleted = 0, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 1, ' .
					'i.isNew = 0, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 1 '
				;
				break;
			// }
			
			// 4.0 Completed {
			case '4.0':
				$sql = $sql .
					'i.isCompleted = 1, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 1, ' .
					'i.isNew = 0, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 0 '
				;
				break;
			// }
			
			// 4.1 Completed (Resub) {
			case '4.1':
				$sql = $sql .
					'i.isCompleted = 1, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 1, ' .
					'i.isNew = 0, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 0, ' .
					'i.isResubmitted = 1 '
				;
				break;
			// }
			
			// 9.9 Approved {
			case '9.9':
				$sql = $sql .
					'i.isCompleted = 1, ' .
					'i.isDeclined = 0, ' .
					'i.isAccepted = 1, ' .
					'i.isNew = 0, ' .
					'i.isLive = 1, ' . 
					'i.isApproved = 1 '
				;
				break;
			// }
			
		}
		
		$sql = $sql . 'WHERE j.jobID = ' . $job['jobID'] . ' AND i.inspectionID = ' . $xTaskID . ' AND i.isCurrent = 1';
		// } Create query
		
		// { Try query
		if(mysql_query($sql)) {
			
			return true;
			
		}
		else {
			
			debug($sql);
			return false;
			
		}
		// } Try query
		
	}
	// } Change Job Status
	
?>