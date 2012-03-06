<?php
	
	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	require_once('/var/www/html/mech/php/PhpConsole.php');
	require_once('/var/www/html/mech/php/dbConfig.php');
	require_once('/var/www/html/app/authenticate.php');
	
	// ===============
	// FUNCTIONS {
	
	// FUNCTIONS }
	// ===============
	
	// ===========
	// DEBUG {
	
	if(!isset($_POST['json'])) {	
		
		// Empty Device 
		$_POST['json'] = '{'          .
			'"userName":"dave", '     .
			'"password":"watson3283", ' .
			'"Task":[], '             .
			'"Building":[], '         .
			'"BuildingImage":[], '    .
			'"Location":[], '         .
			'"Element":[],'           .
			'"ElementImage":[],'      .
			'"ElementDetail":[]'      .
		'}';

	}
	
	// DEBUG }
	// ===========
	
	// ==========
	// MAIN {
	
	$json = json_decode($_POST['json'], true);
	$reply;
	$reply['connectionType'] = 'getData';
	
	// If credentials are valid
	if($user = authenticate($json['userName'], $json['password'])) {
		debug('Authentication successful');
		
		// Initialise reply
		$reply['Task']          = array();
		$reply['Building']      = array();
		$reply['Location']      = array();
		$reply['Element']       = array();
		$reply['ElementDetail'] = array();
		$reply['BuildingImage'] = array();
		$reply['ElementImage']  = array();
		
		// Build D lists
		$Dtasks          = $json['Task']         ;
		$Dbuildings      = $json['Building']     ;
		$DbuildingImages = $json['BuildingImage'];
		$Dlocations      = $json['Location']     ;
		$Delements       = $json['Element']      ;
		$DelementImages  = $json['ElementImage'] ;
		$DelementTypes   = $json['ElementDetail'];
		
		// Deal with Tasks {

		// Build query
		$sql = ''         .
			'SELECT '     .
				'i.inspectionID AS serverID, ' .
				'j.claimNumber, ' .
				'j.firstName, ' .
				'j.lastName, ' .
				'j.locAddress AS address, ' .
				'j.locPostCode AS postcode, ' .
				'j.locSuburb AS town, ' .
				'j.phoneMobile AS mobile, ' .
				'j.phoneLandline AS landLine, ' .
				'i.appointment AS appointment, ' .
				'i.causationNotes, ' .
				'j.claimBrief, ' .
				'i.isCancelled, ' .
				'i.isCompleted, ' .
				'i.isCausation, ' .
				'i.isAccepted, ' .
				'i.isScope, ' .
				'i.isNew, ' .
				'i.isResubmitted, ' .
				'i.resubmissionNote, ' .
				'i.dateCreated AS dateCreated, ' .
				'i.lastChanged AS lastUpdate, ' .
				'i.isLive ' .
			'FROM '       .
				'Job j, ' .
				'Inspection i ' .
			'WHERE '      .
				'i.jobID = j.jobID AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'i.assignedID = ' . $user['userID'] .
			'';
		//debug($sql);
	
		// Ensure successful query
		if($Stasks = mysql_query($sql)) {
			
			// For each task returned
			while($STv = mysql_fetch_assoc($Stasks)) {
				debug('Checking: ST' . $STv['serverID']);
				
				$onD = false;
				$isL = false;
				$old = false;
				
				if($STv['isLive'] == 1) {
					$isL = true;
				}
				
				// Check against each Device task
				foreach($Dtasks as $DTk => $DTv) {
					debug('Against: DT' . $DTv['serverID']);
					
					// If D has it...
					if($STv['serverID'] == $DTv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						debug('if(' . $STv['lastUpdate'] . ' > ' . $DTv['lastUpdate'] . ')');
						if($STv['lastUpdate'] > $DTv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if((!$onD && $isL) || ($onD && $old)) {
					
					// Add task to reply
					array_push($reply['Task'], $STv);
					debug('Inspection added to reply');
				}
			}
		}
		else {
			debug('Tasks query failed');
		}
		// }
		
		// Deal with Buildings {
		
		// Build query
		$sql = ''         .
			'SELECT '     .
				'b.buildingID AS serverID, ' .
				'b.inspectionID AS parentID, ' .
				'b.nameID AS buildingNameID, ' .
				'b.typeID AS buildingTypeID, ' .
				'b.notes, ' .
				'b.aspectID, ' .
				'b.stories, ' .
				'b.age, ' .
				'b.conditionID AS buildingConditionID, ' .
				'b.roofID AS roofingID, ' .
				'b.roofPitch AS pitch, ' .
				'b.claddingID, ' .
				'b.floorID AS flooringID, ' .
				'b.siteSlopeID, ' .
				'b.periodID, ' .
				'b.lastChanged AS lastUpdate, ' .
				'b.isLive ' .
			'FROM '       .
				'Job j, ' .
				'Inspection i, ' .
				'Building b ' .
			'WHERE '      .
				'i.jobID = j.jobID'          . ' AND ' .
				'b.inspectionID = i.inspectionID AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'i.assignedID = ' . $user['userID']  .
			'';
	
		// Ensure successful query
		if($Sbuildings = mysql_query($sql)) {
			
			// For each building returned
			while($SBv = mysql_fetch_assoc($Sbuildings)) {
				debug('Checking: SB' . $SBv['serverID']);
				
				$onD = false;
				$isL = false;
				$old = false;
				
				if($SBv['isLive'] == 1) {
					$isL = true;
				}
				
				// Check against each Device building
				foreach($Dbuildings as $DBk => $DBv) {
					debug('Against: DB' . $DBv['serverID']);
					
					// If D has it...
					if($SBv['serverID'] == $DBv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						if($SBv['lastUpdate'] > $DBv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if((!$onD && $isL) || ($onD && $old)) {
					
					// Add building to reply
					array_push($reply['Building'], $SBv);
					debug('Building added to reply');
				}
			}
		}
		else {
			debug('Buildings query failed');
			debug($sql);
		}
		// }
		
		// Deal with Building Images {
		
		// Build query
		$sql = ''         .
			'SELECT '     .
				'img.imageID AS serverID, ' .
				'img.imageFull AS image, ' .
				'img.imageThumb AS thumbnail, ' .
				'img.lastChanged AS lastUpdate, ' .
				'img.buildingID AS parentID ' .
			'FROM '       .
				'Image img, ' .
				'Inspection i, ' .
				'Building b ' .
			'WHERE '      .
				'i.assignedID = ' . $user['userID'] . ' AND ' .
				'b.inspectionID = i.inspectionID AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'img.buildingID = b.buildingID AND ' .
				'b.isLive = 1' .
			'';
	
		// Ensure successful query
		if($SbuildingImages = mysql_query($sql)) {
			
			// For each buildingImage returned
			while($SBIv = mysql_fetch_assoc($SbuildingImages)) {
				debug('Checking: SBI' . $SBIv['serverID']);
				
				$onD = false;
				$old = false;
				
				// Check against each Device building
				foreach($DbuildingImages as $DBIk => $DBIv) {
					debug('Against: DBI' . $DBIv['serverID']);
					
					// If D has it...
					if($SBIv['serverID'] == $DBIv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						if($SBIv['lastUpdate'] > $DBIv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if(!$onD || ($onD && $old)) {
					
					// Resolve Images
					$imgFullPath  = '/var/www' . $SBIv['image'];
					$imgThumbPath = '/var/www' . $SBIv['thumbnail'];
					
					$imgFull  = file_get_contents($imgFullPath);
					$imgThumb = file_get_contents($imgThumbPath);
					
					// Add building Image to reply
					$SBIv['image']     = base64_encode($imgFull);
					$SBIv['thumbnail'] = base64_encode($imgThumb);
					array_push($reply['BuildingImage'], $SBIv);
					debug('BuildingImage added to reply');
				}
			}
		}
		else {
			debug('BuildingImages query failed');
			debug($sql);
		}
		// }
		
		// Deal with Locations {
		
		// Build query
		$sql = ''         .
			'SELECT '     .
				'l.locationID AS serverID, ' .
				'l.buildingID AS parentID, ' .
				'l.eventID, ' .
				'l.roomID AS roomNameID, ' .
				'l.notes, ' .
				'l.length, ' .
				'l.width, ' .
				'l.height, ' .
				'l.lastChanged AS lastUpdate, ' .
				'l.isLive ' .
			'FROM '       .
				'Job j, ' .
				'Inspection i, ' .
				'Building b, ' .
				'Location l ' .
			'WHERE '      .
				'i.jobID = j.jobID'               . ' AND ' .
				'b.inspectionID = i.inspectionID' . ' AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'l.buildingID = b.buildingID'     . ' AND ' .
				'i.assignedID = ' . $user['userID']         .
			'';
	
		// Ensure successful query
		if($Slocations = mysql_query($sql)) {
			
			// For each location returned
			while($SLv = mysql_fetch_assoc($Slocations)) {
				debug('Checking: SL' . $SLv['serverID']);
				
				$onD = false;
				$isL = false;
				$old = false;
				
				if($SLv['isLive'] == 1) {
					$isL = true;
				}
				
				// Check against each Device location
				foreach($Dlocations as $DLk => $DLv) {
					debug('Against: DL' . $DLv['serverID']);
					
					// If D has it...
					if($SLv['serverID'] == $DLv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						//debug('is ' . $inspection['lastChange'] . ' > ' . $task['timeStamp'] . '?');
						if($SLv['lastUpdate'] > $DLv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if((!$onD && $isL) || ($onD && $old)) {
					
					// Add task to reply
					array_push($reply['Location'], $SLv);
					debug('Location added to reply');
				}
			}
		}
		else {
			debug('Locations query failed');
			debug($sql);
		}
		// }
		
		// Deal with Elements {
		
		// Build query
		$sql = ''         .
			'SELECT '     .
				'e.elementID AS serverID, ' .
				'e.locationID AS parentID, ' .
				'e.elementTypeID AS elementDetailID, ' .
				'e.damageID AS damageID, ' .
				'e.areaAffected, ' . 
				'e.isPAP AS isPrepareAndPaint, ' .
				'e.isReinstall, ' .
				'e.isRemove, ' .
				'e.isRepair, ' .
				'e.isSAI AS isSupplyAndInstall, ' .
				'e.isCleanup AS isClean, ' .
				'e.notes, ' .
				'e.paintQty AS paintQuantity, ' .
				'e.eventID, ' .
				'e.lastChanged AS lastUpdate, ' .
				'e.isLive ' .
			'FROM '       .
				'Job j, ' .
				'Inspection i, ' .
				'Building b, ' .
				'Location l, ' .
				'Element e ' .
			'WHERE '      .
				'i.jobID = j.jobID'               . ' AND ' .
				'b.inspectionID = i.inspectionID' . ' AND ' .
				'l.buildingID = b.buildingID'     . ' AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'e.locationID = l.locationID'     . ' AND ' .
				'i.assignedID = ' . $user['userID']         .
			'';
	
		// Ensure successful query
		if($Selements = mysql_query($sql)) {
			
			// For each element returned
			while($SEv = mysql_fetch_assoc($Selements)) {
				debug('Checking: SE' . $SEv['serverID']);
				
				$onD = false;
				$isL = false;
				$old = false;
				
				if($SEv['isLive'] == 1) {
					$isL = true;
				}
				
				// Check against each Device element
				foreach($Delements as $DEk => $DEv) {
					debug('Against: DE' . $DEv['serverID']);
					
					// If D has it...
					if($SEv['serverID'] == $DEv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						//debug('is ' . $inspection['lastChange'] . ' > ' . $task['timeStamp'] . '?');
						if($SEv['lastUpdate'] > $DEv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if((!$onD && $isL) || ($onD && $old)) {
					
					// Add element to reply
					array_push($reply['Element'], $SEv);
					debug('Element added to reply');
				}
			}
		}
		else {
			debug('Elements query failed');
			debug($sql);
		}
		// }
		
		// Deal with Element Images {
			
		// Build query
		$sql = ''         .
			'SELECT '     .
				'img.imageID AS serverID, ' .
				'img.imageFull AS image, ' .
				'img.imageThumb AS thumbnail, ' .
				'img.lastChanged AS lastUpdate, ' .
				'img.elementID AS parentID ' .
			'FROM '       .
				'Image img, ' .
				'Inspection i, ' .
				'Building b, ' .
				'Location l, ' .
				'Element e ' .
			'WHERE '      .
				'i.assignedID = ' . $user['userID'] . ' AND ' .
				'b.inspectionID = i.inspectionID AND ' .
				'l.buildingID = b.buildingID AND ' .
				'e.locationID = l.locationID AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'img.elementID = e.elementID AND ' .
				'e.isLive = 1' .
			'';
	
		// Ensure successful query
		if($SelementImages = mysql_query($sql)) {
			
			// For each elementImage returned
			while($SEIv = mysql_fetch_assoc($SelementImages)) {
				debug('Checking: SEI' . $SEIv['serverID']);
				
				$onD = false;
				$old = false;
				
				// Check against each Device elementImage
				foreach($DelementImages as $DEIk => $DEIv) {
					debug('Against: DEI' . $DEIv['serverID']);
					
					// If D has it...
					if($SEIv['serverID'] == $DEIv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						if($SEIv['lastUpdate'] > $DEIv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if(!$onD || ($onD && $old)) {
					
					// Resolve Images
					$imgFullPath  = '/var/www' . $SEIv['image'];
					$imgThumbPath = '/var/www' . $SEIv['thumbnail'];
					
					$imgFull  = file_get_contents($imgFullPath);
					$imgThumb = file_get_contents($imgThumbPath);
					
					// Add ElementImage to reply
					$SEIv['image']     = base64_encode($imgFull);
					$SEIv['thumbnail'] = base64_encode($imgThumb);
					array_push($reply['ElementImage'], $SEIv);
					debug('ElementImage added to reply');
					
					//echo '<img src="data:image/jpg;base64,' . $SEIv['image'] . '"/>';
					//echo '<img src="data:image/jpg;base64,' . $SEIv['thumbnail'] . '"/>';
				}
			}
		}
		else {
			debug('ElementImages query failed');
			debug($sql);
		}
		// }
			
		// Deal with ElementTypes {
		
		// Build query
		$sql =
			'SELECT '     .
				'et.elementTypeID AS serverID, ' .
				'et.inspectionID AS taskID, ' .
				'et.category, ' .
				'"All" AS damageCategory, ' .
				'et.isCustom, ' .
				'et.unitsID AS unitID, ' .
				'et.name, ' .
				'et.lastChanged AS lastUpdate, ' .
				'et.isLive ' .
			'FROM '       .
				'Job j, ' .
				'Inspection i, ' .
				'ElementType et ' .
			'WHERE '      .
				'i.jobID = j.jobID'         . ' AND ' .
				'i.inspectionID = et.inspectionID' . ' AND ' .
				'i.isCompleted = 0 AND ' .
				'i.isCancelled = 0 AND ' .
				'i.assignedID = ' . $user['userID'] . ' AND ' .
				'et.isCustom = 1'
			;
		//echo $sql;
		//debug($sql);
		// Ensure successful query
		if($SelementTypes = mysql_query($sql)) {
			
			// For each elementType returned
			while($SETv = mysql_fetch_assoc($SelementTypes)) {
				debug('Checking: SET' . $SETv['serverID']);
				
				$onD = false;
				$isL = false;
				$old = false;
				
				if($SETv['isLive'] == 1) {
					$isL = true;
				}
				
				// Check against each Device elementType
				foreach($DelementTypes as $DETk => $DETv) {
					debug('Against: DET' . $DETv['serverID']);
					
					// If D has it...
					if($SETv['serverID'] == $DETv['serverID']) {
						
						//debug('Match...');
						$onD = true;
						
						// If out of date...
						//debug('is ' . $inspection['lastChange'] . ' > ' . $task['timeStamp'] . '?');
						if($SETv['lastUpdate'] > $DETv['lastUpdate']) {
							$old = true;
							debug('Old!');
						}
						else {
							debug('Current');
						}
						break;
					}
				}
				
				if((!$onD && $isL) || ($onD && $old)) {
					
					// Add elementType to reply
					array_push($reply['ElementDetail'], $SETv);
					debug('ElementType added to reply');
				}
			}
		}
		else {
			debug('ElementTypes query failed');
			debug($sql);
		}
		// }
		
		$reply['status'] = 'good';
		
	}
	
	// Credentials invalid
	else {
		$reply['status'] = 'bad';
		$reply['msg']    = 'Invalid authentication';
	}
	
	echo json_encode($reply);
	
	// MAIN }
	// ==========
	
?>