<?php
	require_once('/var/www/html/mech/php/all.php');
	
	// Define
	$successCount       = 0;
	$jobID              = $_SESSION['selectedJob'];
	$taskID;
	$reply['buildings'] = array();
	$reply['locations'] = array();
	$reply['elements']  = array();
	
	// Job {
	$sql =
		'SELECT '.
			'jobID, ' .
			'status, ' .
			'claimNumber, ' .
			'CONCAT(firstName, " ", lastName) AS claimant, ' .
			'locAddress AS address1, ' .
			'CONCAT(locSuburb, ", ", locPostCode) AS address2, ' .
			'phoneMobile, ' .
			'phoneLandline, ' .
			'claimBrief, ' .
			'wantsCausation, ' .
			'wantsScope ' .
		'FROM '.
			'Job ' .
		'WHERE '.
			'jobID = ' . $jobID
	;
	
	if($job = mysql_query($sql)) {
	
		// Add job to reply
		$reply['job']    = mysql_fetch_assoc($job);
		$successCount += 1;
		
	}
	else {
		debug($sql);
	} // }
	
	// Task {
	$sql =
		'SELECT '.
			'i.inspectionID, ' .
			'i.assignedID, ' .
			'i.causationNotes, ' .
			'i.isScope, ' .
			'i.isCausation, ' .
			'i.isCompleted, ' .
			'i.isCancelled, ' .
			'i.isAccepted, ' .
			'i.isDeclined, ' .
			'CONCAT(u.firstName, " ", u.lastName) AS assignedName ' .
		'FROM '.
			'Inspection i, ' .
			'User u ' .
		'WHERE '.
			'i.jobID = ' . $jobID . ' AND ' .
			'i.assignedID = u.userID AND ' .
			'i.isCurrent = 1 AND ' .
			'i.isCancelled = 0'
	;
	
	if($task = mysql_query($sql)) {
	
		if(mysql_num_rows($task) == 1) {
			
			// Add task to reply
			$task = mysql_fetch_assoc($task);
			$reply['task'] = $task;
			$successCount += 1;
			$taskID = $task['inspectionID'];
			
		}
		else {
			
			// No tasks created
			$taskID = 'none';
		}
		
	}
	else {
		debug($sql);
	} // }
	
	if($taskID == 'none') {
		
		$reply['task']      = 'none';
		$reply['buildings'] = 'none';
		$reply['locations'] = 'none';
		$reply['elements']  = 'none';
		
		$successCount += 4;
		
	}
	else {
	
		// Buildings {
		$sql =
			'SELECT ' .
				'b.buildingID, ' .
				'bn.name, ' .
				'b.notes, ' .
				'CONCAT(br.category, ": ", br.name) AS roof, ' .
				'CONCAT(bt.category, ": ", bt.name) AS type, ' .
				'CONCAT(bc.category, ": ", bc.name) AS cladding, ' .
				'CONCAT(bf.category, ": ", bf.name) AS floor, ' .
				'b.age, ' .
				'bp.name AS period, ' .
				'ba.name AS aspect, ' .
				'bs.name AS slope ' .
			'FROM '.
				'Building b, ' .
				'BuildingName bn, ' .
				'BuildingRoof br, ' .
				'BuildingType bt, ' .
				'BuildingFloor bf, ' .
				'BuildingSiteSlope bs, ' .
				'BuildingAspect ba, ' .
				'BuildingCladding bc, ' .
				'BuildingPeriod bp ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID . ' AND ' .
				'b.nameID = bn.buildingNameID AND ' .
				'b.roofID = br.buildingRoofID AND ' .
				'b.typeID = bt.buildingTypeID AND ' .
				'b.floorID = bf.buildingFloorID AND ' .
				'b.aspectID = ba.buildingAspectID AND ' .
				'b.claddingID = bc.buildingCladdingID AND ' .
				'b.siteSlopeID = bs.buildingSiteSlopeID AND ' .
				'b.periodID = bp.buildingPeriodID AND ' .
				'b.isLive = 1'
		;
		
		if($buildings = mysql_query($sql)) {
			
			if(mysql_num_rows($buildings) > 0) {
				
				// Per Building
				while($building = mysql_fetch_assoc($buildings)) {
					
					// Get image
					$sql = 'SELECT imageThumb FROM Image WHERE buildingID = ' . $building['buildingID'];
					$image = mysql_query($sql);
					if(mysql_num_rows($image) == 1) {
						$image = mysql_fetch_assoc($image);
						$image = substr($image['imageThumb'], 5);
					}
					else {
						$image = '';
					}
					
					// Add building to reply
					$id = $building['buildingID'];
					unset($building['buildingID']);
					$building['image'] = $image;
					$reply['buildings'][$id] = $building;
					
				}
				
			}
			else {
				
				$reply['buildings'] = 'none';
				
			}
			
			$successCount += 1;
			
		}
		else {
			debug($sql);
		} // }
		
		// Locations {
		$sql =
			'SELECT '.
				'l.locationID, ' .
				'l.buildingID, ' .
				'l.notes, ' .
				'l.length, ' .
				'l.width, ' .
				'l.height, ' .
				'lr.name ' .
			'FROM '.
				'Building b, ' .
				'Location l, ' .
				'LocationRoom lr ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID             . ' AND ' .
				'l.buildingID = '   . 'b.buildingID'      . ' AND ' .
				'l.roomID = '       . 'lr.locationRoomID' . ' AND ' .
				'l.isLive = '       . '1'
		;
		
		if($locations = mysql_query($sql)) {
			
			if(mysql_num_rows($locations) > 0) {
			
				// Per Location
				while($location = mysql_fetch_assoc($locations)) {
					
					// Add Location to reply
					$id = $location['locationID'];
					unset($location['locationID']);
					$reply['locations'][$id] = $location;
				}
			}
			else {
				
				$reply['locations'] = 'none';
				
			}
			
			$successCount += 1;
			
		}
		else {
			debug($sql);
		} // }
		
		// Elements {
		$sql =
			'SELECT '.
				'e.elementID, ' .
				'e.locationID, ' .
				'et.category, ' .
				'et.name, ' .
				'et.attribute, ' .
				'e.notes, ' .
				'ev.category AS causeCategory, ' .
				'ev.name AS cause, ' .
				'd.name AS cond, ' .
				'e.paintQty, ' .
				'u.short AS units, ' .
				'e.areaAffected AS qty, ' .
				'e.isPAP, ' .
				'e.isReinstall, ' .
				'e.isRemove, ' . 
				'e.isRepair, ' .
				'e.isSAI, ' .
				'e.isCleanup ' .
			'FROM '.
				'Building b, ' .
				'Location l, ' .
				'Element e, ' .
				'ElementType et, ' .
				'Event ev, ' .
				'Damage d, ' .
				'Units u ' .
			'WHERE '.
				'b.inspectionID = '  . $taskID            . ' AND ' .
				'l.buildingID = '    . 'b.buildingID'     . ' AND ' .
				'e.locationID = '    . 'l.locationID'     . ' AND ' .
				'e.elementTypeID = ' . 'et.elementTypeID' . ' AND ' .
				'e.eventID = '       . 'ev.eventID'       . ' AND ' .
				'e.damageID = '      . 'd.damageID'       . ' AND ' .
				'et.unitsID = '      . 'u.unitsID'        . ' AND ' .
				'l.isLive = '        . '1'
		;
		//debug($sql);
		if($elements = mysql_query($sql)) {
		
			if(mysql_num_rows($elements) > 0) {
				
				// Per Element
				while($element = mysql_fetch_assoc($elements)) {
					
					// Build rectification
					$rect = array();
					if($element['isPAP']       == 1) { array_push($rect, 'PP'); }
					if($element['isReinstall'] == 1) { array_push($rect, 'RI'); }
					if($element['isRemove']    == 1) { array_push($rect, 'RM'); }
					if($element['isRepair']    == 1) { array_push($rect, 'RP'); }
					if($element['isSAI']       == 1) { array_push($rect, 'SI'); }
					if($element['isCleanup']   == 1) { array_push($rect, 'CL'); }
					
					$element['rectification'] = implode($rect, ', ');
					unset($element['isPAP']);
					unset($element['isReinstall']);
					unset($element['isRemove']);
					unset($element['isRepair']);
					unset($element['isSAI']);
					unset($element['isCleanup']);
					
					// Add Element to reply
					$id = $element['elementID'];
					unset($element['elementID']);
					$reply['elements'][$id] = $element;
				}
			}
			else {
				
				$reply['elements'] = 'none';
				
			}
			
			$successCount += 1;
			
		}
		else {
			debug($sql);
		} // }
	}
	
	// Ensure all succeeded
	if($successCount == 5) {
		$reply['status'] = 'good';
	}
	else {
		$reply['status'] = 'bad';
	}
	
	echo json_encode($reply);
?>