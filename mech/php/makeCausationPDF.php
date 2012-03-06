<?php 

	require_once('/var/www/html/mech/php/all.php');
	require_once('/var/www/html/mech/php/class.ezpdf.php'); 
	
	// Get Job {
	$jobID  = $_SESSION['selectedJob'];
	
	$sql =
		'SELECT ' .
			'j.jobID, ' .
			'CONCAT(j.firstName, " ", j.lastName) AS "claimant", '.
			'j.locAddress AS address, ' .
			'j.locSuburb AS suburb, ' .
			'j.locPostCode AS postCode, '.
			'j.phoneMobile, ' .
			'j.phoneLandline, ' .
			'c.name AS insurer, '.
			'j.claimNumber, ' .
			'FROM_UNIXTIME(i.appointment) AS appointment, ' .
			'j.claimBrief, ' .
			'i.inspectionID, ' .
			'i.causationNotes, ' .
			'i.isScope, ' .
			'i.isCausation, ' .
			'CONCAT(u.firstName, " ", u.lastName) AS owner ' .
		'FROM '.
			'Job j, '.
			'User u, '.
			'Company c, '.
			'Inspection i ' .
		'WHERE '.
			'u.userID = j.ownerID AND ' .
			'c.companyID = j.insurerID AND ' .
			'j.jobID = i.jobID AND ' .
			'i.isCurrent = 1 AND ' .
			'j.jobID = ' . $jobID
		;
	
	// Try query
	if($result = mysql_query($sql)) {
		
		$job = mysql_fetch_assoc($result);
		$taskID = $job['inspectionID'];
		
	}
	else {
		debug('Failed getting Job Info: ' . $sql);	
	}
	// }
	
	$reply = 'bad';
	
	makeCausation($job, $taskID);
	
	$reply = 'good';
	
	echo json_encode($reply);
	
	// Make Causation Report {
	function makeCausation($job, $taskID) {
		
		debug('Making Causation');
		
		// Start Scope PDF creation
		$pdf       = new Cezpdf();
		$pageCount = 0;
		$cursorY   = 750;
		$pageTitle = "Causation Report";
		
		$inspector      = getInspectorInfo($taskID);
		$buildings      = getBuildings($taskID);
		$locations      = getLocations($taskID);
		$elements       = getElements($taskID);
		$buildingImages = getBuildingImages($buildings, $job['jobID']);
		$elementImages  = getElementImages($elements);
		
		// Debug
		debug(count($buildings)      . ' Buildings'      );
		debug(count($locations)      . ' Locations'      );
		debug(count($elements)       . ' Elements'       );
		debug(count($buildingImages) . ' Building Images');
		debug(count($elementImages)  . ' Element Images' );
		
		list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);

		// Title {
		$pdf->selectFont('/mech/fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->addText(45, $cursorY-=50, 14, "CLAIM DETAILS");
		// }
		
		// Main Image {
		if(count($buildingImages) > 0) {
			
			$pdf->addJpegFromFile('/var/www' . $buildingImages[0]['imageThumb'], 45, $cursorY-=160, '', 150);
			
		}
		else {
			
			$cursorY -= 160;
			
		}
		// }
		
		// Policy Holder (claimant) {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY+=138, 11, "Policy Holder");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(365, $cursorY, 11, "". $job['claimant']);
		// }
		
		// Address {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY-=25, 11, "Address");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(365, $cursorY, 11, $job['address']);
		$pdf->addText(365, $cursorY-=15, 11, $job['suburb']);
		$pdf->addText(365, $cursorY-=15, 11, $job['postCode']);
		// }
		
		// Contact {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY-=25, 11, "Contact");
		$pdf->selectFont('./fonts/Helvetica.afm');
		
		// If Mobile Given
		if($job['phoneMobile'] != '') {
			
			$mArea   = substr($job['phoneMobile'],0,4); 
			$mPrefix = substr($job['phoneMobile'],4,3); 
			$mNumber = substr($job['phoneMobile'],7,3); 
			//$sMob    = $mArea." ".$mPrefix." ".$mNumber;
			
		}
		else {
			
			$sMob = 'No mobile supplied';
			
		}
		$pdf->addText(365, $cursorY, 11, $job['phoneMobile']);
		
		// If Landline Given
		if($job['phoneLandline'] != '') {
			
			$sArea   = substr($job['phoneLandline'],0,2); 
			$sPrefix = substr($job['phoneLandline'],2,4); 
			$sNumber = substr($job['phoneLandline'],6,10); 
			//$sPhone  = "(".$sArea.") ".$sPrefix." ".$sNumber;
			
		}
		else {
			
			$sPhone = 'No landline supplied';
			
		}
		$pdf->addText(365, $cursorY-=15, 11, $job['phoneLandline']);
		// }
		
		// Insurer {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY-=25, 11, "Insurer");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(365, $cursorY, 11, $job['insurer']);
		// }
		
		// Claim Number {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY-=25, 11, "Insurer Ref.");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(365, $cursorY, 11, $job['claimNumber']);
		// }
		
		// Appointment {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY-=25, 11, "Inspection Time");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(365, $cursorY, 11, $job['appointment']);
		// }
		
		/*
		// JobID {
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(260, $cursorY-=25, 11, "Watson Ref.");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(365, $cursorY, 11, $job['jobID']);
		// }
		*/
		
		// Claim Brief {
		$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->addText(45, $cursorY-=40, 14, "INSTRUCTIONS");
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY-=15, 10, "by");
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(65, $cursorY, 10, $job['owner']);
		
		$cursorY -= 15;
		$pdf->selectFont('./fonts/Helvetica.afm');
		$lines = explode("\n",$job['claimBrief']); 
		foreach($lines as $line){ 
	    	$foo = $line;
			$foo = wordwrap($foo,110,"|");
	   		$Arrx = explode("|",$foo);
	   		$i = 0;
	   		while (isset($Arrx[$i]) && $Arrx[$i] != "") {
	    		$pdf->addText(45, $cursorY-=5, 10, $Arrx[$i]);
	     		$i++;
	       		$cursorY -= 10;
	    	}
		}
		// }
	
		// Causation Notes {
		$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->addText(45, $cursorY-=40, 14, "CAUSATION NOTES");
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY-=15, 10, "by");
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(65, $cursorY, 10, $inspector['name']);
		$pdf->selectFont('./fonts/Helvetica.afm');
	
		$cursorY -= 15;
		$lines = explode("\n",$job['causationNotes']); 
		foreach($lines as $line){ 
			$foo = $line;
			$foo = wordwrap($foo,110,"|");
			$Arrx = explode("|",$foo);
			$i = 0;
			while (isset($Arrx[$i]) && $Arrx[$i] != "") {
	    		$pdf->addText(45, $cursorY-=5, 10, $Arrx[$i]);
	       		$i++;
				$cursorY -= 10;
	    	}
		}
		// }
		
		// Build Content
		list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
		list($pdf, $pageCount, $cursorY) = buildContent($pdf, $pageCount, $pageTitle, $cursorY, 'causation', $buildings, $locations, $elements);
		
		// Build Photos {
		if(count($buildingImages) > 0 || count($elementImages) > 0) {
			
			list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
		}
		
		if(count($buildingImages) > 0) {
			
			$rowassist = 0;
		
			// Per Building Image {
			foreach($buildingImages as $k => $v) {
				
				if ($rowassist == 1) {
	    			$cursorY-=200;
				}
					
				if ($rowassist == 0) {
	    			$cursorY-=180;
	    			$rowassist = 1;
				}
	    				
				if ($cursorY <= 200) {
	        		list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
	        		$cursorY-=180;
				}
			
				list($pdf, $cursorY, $rowassist) = addBuildingImage($pdf, $cursorY, $v['imageFull'], $v['name'], $rowassist);
						
				$cursorY == $cursorY;
				
			}
		
		}
		else {
			
			$rowassist = 1;
			
		} // }
		
		// Per Element Image {
		foreach($elementImages as $k => $v) {
			
			if ($rowassist == 1) {
    			$cursorY-=233;
			}
    				
			if ($cursorY <= 100) {
        		list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
        		$cursorY-=180;
			}
		
			list($pdf, $cursorY, $rowassist) = addElementImage($pdf, $cursorY, $v['imageFull'], $v['name'], $rowassist, $v['room']);
					
			$cursorY == $cursorY;
			
		} // }
		// }
		
		// Display Report
		$cbuf = $pdf->output();
		$pdf->ezStream();
		
	} // }
	
	// Get Inspector Info {
	function getInspectorInfo($taskID) {
		
		$sql =
			'SELECT ' .
				'CONCAT(u.firstName, " ", u.lastName) AS "name", '.
				'u.email ' .
			'FROM '.
				'Inspection i, '.
				'User u ' .
			'WHERE '.
				'u.userID = i.assignedID AND ' .
				'i.inspectionID = ' . $taskID
			;
		
		// Try query
		if($result = mysql_query($sql)) {
			
			return mysql_fetch_assoc($result);
			
		}
		else {
			debug('Failed getting Inspector Info: ' . $sql);	
		}
		
	}
	// }
	
	// Get Buildings {
	function getBuildings($taskID) {
		
		$sql =
			'SELECT ' .
				'b.buildingID, ' .
				'bn.name, ' .
				'b.notes, ' .
				'br.name AS roof, ' .
				'CONCAT(bt.category, " ", bt.name) AS type, ' .
				'bc.name AS cladding, ' .
				'b.age, ' .
				'bp.name AS period, ' .
				'bf.name AS floor, ' .
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
		//debug($sql);
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Building
			while($building = mysql_fetch_assoc($result)) {
				
				array_push($return, $building);
				
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Buildings: ' . $sql);	
		}
		
	}
	// }
	
	// Get Locations {
	function getLocations($taskID) {
	
		$sql =
			'SELECT ' .
				'l.locationID, ' .
				'l.buildingID, ' .
				'lr.name, ' .
				'l.length, ' .
				'l.width, ' .
				'l.height, ' .
				'l.notes ' .
			'FROM '.
				'Building b, ' .
				'Location l, ' .
				'LocationRoom lr ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID . ' AND ' .
				'l.buildingID = b.buildingID AND ' .
				'l.roomID = lr.locationRoomID AND ' .
				'l.isLive = 1'
			;
		//debug($sql);
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Location
			while($location = mysql_fetch_assoc($result)) {
				
				array_push($return, $location);
			
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Locations: ' . $sql);	
		}
		
	}// }
	
	// Get Elements {
	function getElements($taskID) {
		
		$sql =
			'SELECT ' .
				'e.elementID, ' .
				'e.locationID, ' .
				'lr.name AS locationName, ' .
				'et.category, ' .
				'et.name, ' .
				'et.attribute, ' .
				'e.paintQty, ' .
				'e.areaAffected, ' .
				'u.short AS units, ' .
				'e.isPAP, ' .
				'e.isReinstall, ' .
				'e.isRemove, ' .
				'e.isRepair, ' .
				'e.isSAI, ' .
				'e.notes, ' .
				'e.isCleanup, ' .
				'd.name AS damage, ' .
				'ev.category AS eventCategory, ' .
				'ev.name AS event ' .
			'FROM ' .
				'Building b, ' .
				'Location l, ' .
				'LocationRoom lr, ' .
				'Element e, ' .
				'ElementType et, ' .
				'Units u, ' .
				'Damage d, ' .
				'Event ev ' .
			'WHERE '.
				'b.inspectionID = ' . $taskID . ' AND ' .
				'l.buildingID = b.buildingID AND ' .
				'e.locationID = l.locationID AND ' .
				'l.roomID = lr.locationRoomID AND ' .
				'e.elementTypeID = et.elementTypeID AND ' .
				'e.damageID = d.damageID AND ' .
				'e.eventID = ev.eventID AND ' .
				'et.unitsID = u.unitsID AND ' .
				'l.isLive = 1'
			;
		//debug($sql);
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Element
			while($element = mysql_fetch_assoc($result)) {
				
				array_push($return, $element);
			
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Elements: ' . $sql);	
		}
		
	} // }
	
	// Get Building Images {
	function getBuildingImages($buildings, $jobID) {
		
		$buildingIDs = array();
		
		// Get Building ID's
		foreach($buildings as $k => $v) {
			
			array_push($buildingIDs, $v['buildingID']);
			
		}
		
		$sql =
			'SELECT ' .
				'img.buildingID, ' .
				'bn.name, ' .
				'img.imageThumb, ' .
				'img.imageFull ' .
			'FROM '.
				'Image img, ' .
				'Building b, ' .
				'BuildingName bn ' .
			'WHERE '.
				'img.buildingID = b.buildingID AND ' .
				'bn.buildingNameID = b.nameID AND ' .
				'img.buildingID IN(' . implode($buildingIDs, ', ') . ')'
			;
		//debug($sql);
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Location
			while($image = mysql_fetch_assoc($result)) {
				
				array_push($return, $image);
			
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Building Images: ' . $sql);	
		}
		
	}
	// }
	
	// Get Element Images {
	function getElementImages($elements) {
		
		$elementIDs = array();
		
		// Get Building ID's
		foreach($elements as $k => $v) {
			
			array_push($elementIDs, $v['elementID']);
			
		}
		
		$sql =
			'SELECT ' .
				'img.elementID, ' .
				'img.imageThumb, ' .
				'img.imageFull, ' .
				'lr.name AS room, ' .
				'CONCAT(et.name, " ", et.attribute) AS name ' .
			'FROM '.
				'Image img, ' .
				'Element e, ' .
				'ElementType et, ' .
				'Location l, ' .
				'LocationRoom lr ' .
			'WHERE '.
				'e.elementID = img.elementID AND ' .
				'et.elementTypeID = e.elementTypeID AND ' .
				'e.locationID = l.locationID AND ' .
				'l.roomID = lr.locationRoomID AND ' .
				'e.elementID IN(' . implode($elementIDs, ', ') . ')'
			;
		
		// Try query
		if($result = mysql_query($sql)) {
			
			$return = array();
			
			// Per Location
			while($image = mysql_fetch_assoc($result)) {
				
				array_push($return, $image);
			
			}
			
			return $return;
			
		}
		else {
			debug('Failed getting Element Images: ' . $sql);	
		}
		
	}
	// }
	
	// Create New Page {
	function addNewPage($pdf, $pageCount, $pageTitle) {
	
		// If after first page
		if ($pageCount >= 1) {
			
			// Create blank
			$pdf->ezNewPage(); 
			$cursorY = 760;
			
		}
		$pageCount++;
		
		// Background
		$pdf->setColor(0.87,0.87,0.85);
		$pdf->filledRectangle(0,0,595,842);
	
		// White insert
		$pdf->setColor(1,1,1);
		$pdf->filledRectangle(25,80,545,700);
		$pdf->filledEllipse( 35, 80, 10, 0, 0, 8, 0, 360);
		$pdf->filledEllipse( 560, 80, 10, 0, 0, 8, 0, 360);
		$pdf->filledRectangle(35,70,525,20);
		$pdf->filledEllipse( 35, 780, 10, 0, 0, 8, 0, 360);
		$pdf->filledEllipse( 560, 780, 10, 0, 0, 8, 0, 360);
		$pdf->filledRectangle(35,770,525,20);
	
		// Header
		$pdf->addPngFromFile('/src/images/logoSmall.png', 45, 800, 90);
		
		// Page Numbers
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(550, 800, 10, $pageCount);
		
		// Footer
		$pdf->setColor(0.6,0.6,0.6);
		$pdf->selectFont('./fonts/Helvetica.afm');
		$copy = utf8_decode("©");
		$pdf->addText(343, 55, 8, $copy." 2012 Watson Inc.");	
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->addText(418, 55, 8, "103 Dobell Dr, Wangi Wangi, NSW 2267");
		$pdf->addText(264, 45, 8, "Interested in getting your own custom reports? Contact Watson for more information.");
	
		// For page 1
		if ($pageCount <= 1) {
			
			// Set Report Title
			$pdf->setColor(0.23,0.22,0.22);
			$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
			if ($pageTitle == "Scope of Works") {
				
				$pdf->addText(200, 750, 24, "SCOPE OF WORKS");
				
			} else {
				
				$pdf->addText(200, 750, 24, "CAUSATION REPORT");
				
			}
			$cursorY = 750;
		}
		
		return array ($pdf, $pageCount, $cursorY);
			
	} // }
	
	// Build Content {
	function buildContent($pdf, $pageCount, $pageTitle, $cursorY, $type, $buildings, $locations, $elements) {
		
		foreach($buildings as $Bk => $Bv) {
			
			// Draw Building
			if ($cursorY <= 250) {
	        	list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
			}
			$cursorY = addBuilding($pdf, $cursorY, $Bv);
			
			// Per Location
			foreach($locations as $Lk => $Lv) {
				
				// If belongs here
				if($Lv['buildingID'] == $Bv['buildingID']) {
					
					// Draw Location
					if ($cursorY <= 120) {
						list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
					}
					$cursorY = addLocation($pdf, $cursorY, $Lv);
					
					// Per Element
					foreach($elements as $Ek => $Ev) {
						
						// If belongs here
						if($Ev['locationID'] == $Lv['locationID']) {
							
							// Draw Element
							if ($cursorY <= 150) {
								list($pdf, $pageCount, $cursorY) = addNewPage($pdf, $pageCount, $pageTitle);
							}
							$cursorY = addCausationElement($pdf, $cursorY, $Ev);
							
						}
						
					}
					
				}
				
			}
			
		}
		
		return array($pdf, $pageCount, $cursorY);
		
	}
	// }
	
	// Add Building {
	function addBuilding($pdf, $cursorY, $building) {
		
		// Name
		$cursorY -= 50;
		$building['name'] = strtoupper($building['name']);
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->addText(45, $cursorY, 18, $building['name']);
	
		// Notes
		$pdf->selectFont('./fonts/Helvetica.afm');
		$lines = explode("\n",$building['notes']); 
		foreach($lines as $line){ 
			
	    	$foo = $line;
	    	$foo = wordwrap($foo,110,"|");
	    	$Arrx = explode("|",$foo);
	    	$i = 0;
	    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
	    		
	    		$pdf->addText(45, $cursorY-15, 10, $Arrx[$i]);
	       		//PDF_show_xy($pdf,$Arrx[$i], 45, $cursorY-15);
	       		$i++;
	       		$cursorY -= 10;
	       		
	    	}
		}
		
		// Roof
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 30, 12, "ROOF");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['roof']);
		
		// Type
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(300, $cursorY, 12, "TYPE");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(385, $cursorY, 12, $building['type']);
		
		// Cladding
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 15, 12, "CLADDING");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['cladding']);
		
		// Age
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(300, $cursorY, 12, "AGE");
		$pdf->selectFont('./fonts/Helvetica.afm');
		if($building['age'] == '0') {
			
			$pdf->addText(385, $cursorY, 12, $building['period']);
			
		}
		else {
			
			$pdf->addText(385, $cursorY, 12, $building['age']);
			
		}
		
		// Floor
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 15, 12, "FLOOR");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['floor']);
		
		// Aspect
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(300, $cursorY, 12, "ASPECT");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(385, $cursorY, 12, $building['aspect']);
		
		// Slope
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(45, $cursorY -= 15, 12, "SITE SLOPE");
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(135, $cursorY, 12, $building['slope']);
		
		$cursorY -= 20;
	
		return $cursorY;
	
	} // }
	
	// Add Location {
	function addLocation($pdf, $cursorY, $location) {
		
		// Bounds box start
		$boundsBoxStart = $cursorY;
		
		// { Determine height
		$cursorY -=25; // Name
		
		if($location['notes'] != '') {
			
			$lines = explode("\n",$location['notes']); 
			foreach($lines as $line){ 
				
		    	$foo = $line;
		    	$foo = wordwrap($foo,110,"|");
		    	$Arrx = explode("|",$foo);
		    	$i = 0;
		    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
		    		
		       		$i++;
		       		$cursorY -= 10;
		       		
		    	}
		    	
			}
			
		}
		// } Determine height
		
		// Bounds box build
		$boundsBoxHeight = $boundsBoxStart - $cursorY + 5;
		
		$pdf->setColor(0.87,0.87,0.85);
		$pdf->filledRectangle(35, $boundsBoxStart - $boundsBoxHeight, 525, $boundsBoxHeight);
		
		// Return to top of bounds box
		$cursorY = $boundsBoxStart;
		
		// Name
		$location['name'] = strtoupper($location['name']);
		$pdf->setColor(0.23,0.22,0.22);
		$pdf->selectFont('./fonts/HelveticaNeue-CondensedBlack.afm');
		$pdf->addText(45, $cursorY-=20, 15, $location['name']);
		
		// Dimensions
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(440, $boundsBoxStart - ($boundsBoxHeight / 2) - 5, 10, $location['length']." x ".$location['width']." x ".$location['height']."mm");	
		$cursorY+=1;
		
		// Notes
		$pdf->setColor(0.30,0.30,0.30);
		if($location['notes'] != '') {
			foreach($lines as $line){ 
				
		    	$foo = $line;
		    	$foo = wordwrap($foo,110,"|");
		    	$Arrx = explode("|",$foo);
		    	$i = 0;
		    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
		    		
		    		$pdf->addText(45, $cursorY-15, 10, $Arrx[$i]);
		       		$i++;
		       		$cursorY -= 10;
		       		
		    	}
		    	
			}
		}
		
		$cursorY -= 12;
	
		return $cursorY;
		
	} // }
	
	// Add Element (Causation) {
	function addCausationElement($pdf, $cursorY, $element) {
		
		$elHeight = 0;
		
		$pdf->setColor(0.2,0.2,0.2);
		$pdf->setLineStyle(1);
		$pdf->line(35,$cursorY,560,$cursorY);
		
		// Category
		$pdf->setColor(0.3,0.3,0.3);
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(45, $cursorY-=20, 12, $element['category']);
		$right=$pdf->getTextWidth(12, $element['category']) + 50;
		$elHeight +=20;
		
		// { Name
		
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		
		// { If room on this line
		if($right + $pdf->getTextWidth(10, $element['name']) + 5 < 300) {
			
			$pdf->addText($right, $cursorY, 12, $element['name']);
			$right+=$pdf->getTextWidth(12, $element['name']) + 5;
		
		}
		// } If room on this line
		// { Else
		else {
			
			$pdf->addText(45, $cursorY-=18, 12, $element['name']);
			$right=45+$pdf->getTextWidth(12, $element['name']) + 5;
			$elHeight+=18;
			
		}
		// } Else
		
		// } Name
		
		// { Attribue
		
		$pdf->selectFont('./fonts/Helvetica.afm');
		
		// { If room on this line
		if($right + $pdf->getTextWidth(10, $element['attribute']) + 5 < 300) {
			
			$pdf->addText($right, $cursorY, 10, '<i>' . $element['attribute'] . '</i>');
		
		}
		// } If room on this line
		// { Else
		else {
			
			$pdf->addText(45, $cursorY-=15, 10, '<i>' . $element['attribute'] . '</i>');
			$elHeight+=15;
			
		}
		// } Else
		
		// } Attribute
		
		// { Notes
		$pdf->setColor(0.6,0.6,0.6);
		if ($element['notes'] != '') {
			$lines = explode("\n",$element['notes']); 
			foreach($lines as $line){ 
				
		    	$foo = $line;
		    	$foo = wordwrap($foo,110,"|");
		    	$Arrx = explode("|",$foo);
		    	$i = 0;
		    	while (isset($Arrx[$i]) && $Arrx[$i] != "") {
		    		
		    		$pdf->addText(45, $cursorY-15, 10, $Arrx[$i]);
		       		$i++;
		       		$cursorY -= 10;
		       		$elHeight+=10;
		       		
		    	}
		    	
			}
		}
		// } Notes
		
		// Damage
		$pdf->setColor(0.3,0.3,0.3);
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->addText(555 - $pdf->getTextWidth(12, $element['damage']), $cursorY - 12 + ($elHeight / 2), 12, $element['damage']);
		
		// Event Category - Event
		$pdf->selectFont('./fonts/Helvetica.afm');
		$pdf->addText(300, $cursorY - 10 + ($elHeight / 2), 10, $element['eventCategory'] . ' - ' . $element['event']);
		
		$cursorY -= 13;
		
		return $cursorY;
	
	} // }
	
	// Add Building Image {
	function addBuildingImage($pdf, $cursorY, $bdimage, $bdname, $rowassist) {
	
		// Row setup
		if ($rowassist == 1) {
		
			$pdf->selectFont('./fonts/Helvetica-Bold.afm');
			$pdf->addJpegFromFile('/var/www' . $bdimage, 45, $cursorY, '', 190);
			$pdf->addText(45, $cursorY-=14, 12, $bdname);
			$cursorY += 14;
		
			$rowassist = 2;
			
		}
		else if ($rowassist == 2) {
		
			$pdf->selectFont('./fonts/Helvetica-Bold.afm');
			$pdf->addJpegFromFile('/var/www' . $bdimage, 300, $cursorY, '', 190);
			$pdf->addText(300, $cursorY-=14, 12, $bdname);
			$cursorY += 14;
		
			//$cursorY -=30;
			$rowassist = 1;
		}
			
		return array($pdf, $cursorY, $rowassist);
	
	} // }
	
	// Add Element Image {
	function addElementImage($pdf, $cursorY, $eleimage, $elename, $rowassist, $roomname) {
		
		// Row setup
		if ($rowassist == 1) {
		
			$pdf->selectFont('./fonts/Helvetica-Bold.afm');
			$pdf->addJpegFromFile('/var/www' . $eleimage, 45, $cursorY, '', 190);
			$pdf->setColor(0.23,0.22,0.22);
			$pdf->addText(45, $cursorY-=14, 12, $elename);
			$pdf->setColor(0.6,0.6,0.6);
			$pdf->addText(45, $cursorY-=11, 10, $roomname);
			$cursorY += 25;
			$rowassist = 2;
			
		}
		else if ($rowassist == 2) {
		
			$pdf->selectFont('./fonts/Helvetica-Bold.afm');
			$pdf->addJpegFromFile('/var/www' . $eleimage, 300, $cursorY, '', 190);
			$pdf->setColor(0.23,0.22,0.22);
			$pdf->addText(300, $cursorY-=14, 12, $elename);
			$pdf->setColor(0.6,0.6,0.6);
			$pdf->addText(300, $cursorY-=11, 10, $roomname);
			$cursorY += 25;
			$rowassist = 1;
		
		}
			
		return array($pdf, $cursorY, $rowassist);
	
	} // }
	
?>