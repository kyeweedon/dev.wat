<?php
	
	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	// ==============
	// { INCLUDES
	
	require_once('/var/www/html/mech/php/PhpConsole.php');
	require_once('/var/www/html/mech/php/functions.php');
	require_once('/var/www/html/mech/php/dbConfig.php');
	require_once('/var/www/html/app/authenticate.php');
	
	// } INCLUDES
	// ==============
	
	// ===============
	// { FUNCTIONS
	
	// { Process incoming records
	
	function processD($table, $records) {
		
		$return = array();
		
		foreach($records as $Dr) {
			
			// If no serverID
			if($Dr['serverID'] == "") {
				
				// Build placeholder Record
				$sql = 'INSERT INTO '         .
						ucfirst($table) . ' ' .
					'SET '                    .
						'isLive = 1'          ;
				
				// Insert placeholder
				if(mysql_query($sql)) {
					
					$newID = mysql_insert_id();
					$Dr['serverID'] = $newID;
					debug('Placeholder ' . ucfirst($table) . ' created: ' . $newID); 
					
				}
				else {
					
					debug('Failed to add placeholder '  . ucfirst($table));
					
				}
				
			}
			
			// Add this Record
			$k = $Dr['serverID'];
			unset($Dr['serverID']);
			$return[$k] = $Dr;
			
		}
		
		return $return;
		
	}
	
	// } Process incoming records
	
	// { Process server records
	
	function processS($table, $IDs) {
		
		$return = array();
		
		if(count($IDs) > 0) {
							
			if($table == 'buildingImage' || $table == 'elementImage') {
				
				$sql = 'SELECT ' .
						'imageID AS serverID, ' .
						'lastChanged ' .
					'FROM ' .
						'Image ' . 
					'WHERE ' .
						'imageID IN(' . implode(', ', $IDs) . ') AND ' .
						'isLive = 1';
				
				if($table == 'buildingImage') {
					
					$sql = $sql . ' AND buildingID > 0';
					
				}
				
				if($table == 'elementImage') {
					
					$sql = $sql . ' AND elementID > 0';
					
				}
			
			}
			
			else {
				
				$sql = 'SELECT ' .
						$table . 'ID AS serverID, ' .
						'lastChanged ' .
					'FROM ' . 
						ucfirst($table) . ' ' . 
					'WHERE ' . 
						$table . 'ID IN(' . implode(', ', $IDs) . ') AND ' .
						'isLive = 1';
						
			}			
			
			// Try query
			if($records = mysql_query($sql)) {
				
				// Per records
				while($record = mysql_fetch_assoc($records)) {
					
					// Add this record
					$k = $record['serverID'];
					unset($record['serverID']);
					$return[$k] = $record;
					
				}
				
			}
			else {
				
				debug('Query faiure (S ' . ucfirst($table) . 's)');
				debug($sql);
				
			}
			
		}
		
		return $return;
		
	}
	// } Process server records
	
	// { Build ID lists
	function makeArrayOfIDs($table, $part) {
		
		$return = array();
		
		// Per record
		foreach($table as $k => $v) {
			
			// Add ID
			if($part == 1) {
				
				array_push($return, $k);
				
			}
			else {
				
				array_push($return, $v['serverID']);
				
			}
		
		}
		
		return $return;
		
	}
	// } Build ID lists
	
	// { Build replies
	function processReply($Drecords, $Srecords) {
		
		$return = array();
		
		// If D has records to process
		if(count($Drecords) > 0) {
			
			// Per D record
			foreach($Drecords as $Dk => $Dv) {
				
				// Find matching S Record
				$Sv = $Srecords[$Dk];
				
				// If D is newer version
				if($Dv['lastUpdate'] > $Sv['lastChanged']) {
					
					// Add to reply
					array_push($return, json_decode('{"serverID":' . $Dk . ', "coreDataID":"' . $Dv['coreDataID'] . '"}', true));
					
				}
				
			}
			
		}
		else {
			
			debug('   No Records to update');
			
		}
		
		return $return;
		
	}
	// } Build replies (P1)
	
	// { Build query section
	function make($records, $Sname, $Dname, $table, $isString) {
			
		$return = $Sname . ' = CASE ' . $table . 'ID ';
		
		// { Per record
		foreach($records as $k => $r) {
			
			// { Undefined
			if(!isset($r[$Dname])) {
				
				//
				
			}
			// } Undefined
			// { Else
			else {
				
				// { Parse isLive
				if($Sname == 'isLive') {
					
					// { Invert isLive
					if($r[$Dname] == 1) {
						
						$r[$Dname] = 0;
						
					}
					else {
						
						$r[$Dname] = 1;
						
					}
					// } Invert isLive
					
				}
				// } Parse isLive
				
				$return = $return . 'WHEN ' . $r['serverID'] . ' THEN ';
				
				if($isString) {
					
					$return = $return . '"' . $r[$Dname] . '" ';
						
				}
				else {
					
					$return = $return       . $r[$Dname] . ' ' ;
					
				}
				
			}
			// } Else
			
		}
		// } Per record
		
		// { Not empty && Not last
		if($return != $Sname . ' = CASE ' . $table . 'ID ') {
			
			$return = $return . 'END, ';
		
		}
		// } Not empty
		// { Else
		else {
			
			$return = '';
			
		}
		// } Else
		
		return $return;
		
	}
	// } Build query section (P2)
	
	// { Update table on Server
	function updateTable($Drecords, $DrecordIDs, $xTable) {
		
		
		// Skip this if there are no records to update
		if(count($Drecords) === 0) {
			
			debug('No ' . $xTable . 's to update');
			$return = 1;
			
		}
		else {
			
			// { Setup
			debug('Updating ' . $xTable . 's');
			$return = 0;
			
			$table = $xTable;
			
			if($xTable == 'buildingImage' || $xTable == 'elementImage') {
				
				$table = 'image';
				
			}
			// } Setup
			
			// { Build query
			$sql = 'UPDATE ' . 
					ucfirst($table) . ' ' .
				'SET ' .
					make($Drecords, 'lastChanged', 'lastUpdate', $table, false) . ' '; // Last Changed
			
			if($xTable == 'elementType') {
				
				$sql = $sql . 'isCustom = 1, ';
				
			}
			
			switch($xTable) {
				
				// { Tasks
				case 'inspection':
					
					$sql = $sql .
						make($Drecords, 'isResubmitted',  'isResubmitted',  $table, false) . // Is Completed
						make($Drecords, 'appointment',    'appointment',    $table, false) . // Appointment Time
						make($Drecords, 'causationNotes', 'causationNotes', $table, true)  . // Causation Notes
					'';	
					
					break;
				
				// } Tasks
				
				// { Buildings
				case 'building':
					
					$sql = $sql .
						make($Drecords, 'inspectionID', 'taskID',              $table, false) . // Inspection ID
						make($Drecords, 'isLive',       'isRemoved',           $table, false) . // Is Removed
						make($Drecords, 'age',          'age',                 $table, false) . // Age
						make($Drecords, 'aspectID',     'aspectID',            $table, false) . // Aspect ID
						make($Drecords, 'nameID',       'buildingNameID',      $table, false) . // Building Name ID
						make($Drecords, 'typeID',       'buildingTypeID',      $table, false) . // Building Type ID
						make($Drecords, 'claddingID',   'claddingID',          $table, false) . // Cladding ID
						make($Drecords, 'conditionID',  'buildingConditionID', $table, false) . // Condition ID
						make($Drecords, 'floorID',      'flooringID',          $table, false) . // Flooring ID
						make($Drecords, 'notes',        'notes',               $table, true)  . // Notes
						make($Drecords, 'roofPitch',    'pitch',               $table, false) . // Pitch
						make($Drecords, 'roofID',       'roofingID',           $table, false) . // Roofing ID
						make($Drecords, 'periodID',     'periodID',            $table, false) . // Roofing ID
						make($Drecords, 'siteSlopeID',  'siteSlopeID',         $table, false) . // Site Slope ID
						make($Drecords, 'stories',      'stories',             $table, false) . // Stories
					'';
					
					break;
				
				// } Buildings
				
				// { Locations
				case 'location':
					
					$sql = $sql .
						make($Drecords, 'buildingID', 'buildingID', $table, false) . // Building ID
						make($Drecords, 'isLive',     'isRemoved',  $table, false) . // Is Removed
						make($Drecords, 'height',     'height',     $table, false) . // Height
						make($Drecords, 'width',      'width',      $table, false) . // Width
						make($Drecords, 'length',     'length',     $table, false) . // Length
						make($Drecords, 'roomID',     'roomNameID', $table, false) . // Room Name ID
						make($Drecords, 'notes',      'notes',      $table, true)  . // Notes
						make($Drecords, 'eventID',    'eventID',    $table, false) . // Event ID
					'';
					
					break;
				
				// } Locations
				
				// { Elements
				case 'element':
					
					// { Parse damageCategoryLinkID -> damageID
					foreach($Drecords as $k => $v) {
						
						$sql2 = 'SELECT damageID FROM DamageCategoryLink WHERE damageCategoryLinkID = ' . $v['damageID'];
						if($result = mysql_query($sql2)) {
							
							$result = mysql_fetch_assoc($result);
							$Drecords[$k]['damageID'] = $result['damageID'];
							
						}
						else {
							
							debug($sql2);
							
						}
						
					}
					// } Parse damageCategoryLinkID -> damageID
					
					$sql = $sql .
						make($Drecords, 'locationID',    'locationID',         $table, false) . // Location ID
						make($Drecords, 'isLive',        'isRemoved',          $table, false) . // Is Removed
						make($Drecords, 'areaAffected',  'areaAffected',       $table, false) . // Area Affected
						make($Drecords, 'damageID',      'damageID',           $table, false) . // Damage ID
						make($Drecords, 'elementTypeID', 'elementDetailID',    $table, false) . // Element Type ID
						make($Drecords, 'eventID',       'eventID',            $table, false) . // Event ID
						make($Drecords, 'isCleanup',     'isClean',            $table, false) . // Is Cleanup
						make($Drecords, 'isPAP',         'isPrepareAndPaint',  $table, false) . // Is Prepare And Paint
						make($Drecords, 'isReinstall',   'isReinstall',        $table, false) . // Is Reinstall
						make($Drecords, 'isRemove',      'isRemove',           $table, false) . // Is Remove
						make($Drecords, 'isRepair',      'isRepair',           $table, false) . // Is Repair
						make($Drecords, 'isSAI',         'isSupplyAndInstall', $table, false) . // Is Supply & Install
						make($Drecords, 'paintQty',      'paintQuantity',      $table, false) . // Paint Quantity
						make($Drecords, 'notes',         'notes',              $table, true)  . // Notes
					'';
					
					break;
				
				// } Elements
				
				// { Element Types
				case 'elementType':
					
					$sql = $sql .
						make($Drecords, 'inspectionID',     'taskID',           $table, false) . // Task ID
						make($Drecords, 'isLive',           'isRemoved',        $table, false) . // Is Removed
						make($Drecords, 'attribute',        'attribute1',       $table, true)  . // Attribute
						make($Drecords, 'category',         'category',         $table, true)  . // Category
						make($Drecords, 'isCustom',         'isCustom',         $table, false) . // Is Custom
						make($Drecords, 'name',             'name',             $table, true)  . // Name
						make($Drecords, 'unitsID',          'unitID',           $table, false) . // Units ID
					'';
					
					break;
				
				// } Element Types
				
				// { Building Images
				case 'buildingImage':
					
					// { Parse Image -> Path
					foreach($Drecords as $k => $v) {
           				
           				// { Decode & upload image Thumb

						$thumbPath = '/var/www/html/src/images/buildings/' . $v['buildingID'] . 'S' . '.jpg';
						$imageThumb = str_replace(' ', '+', $v['thumbnail']);
						$imageThumb = base64_decode($imageThumb);
						$imageThumb = imagecreatefromstring($imageThumb);
						if($imageThumb != false) {
							
							header('Content-Type: image/jpeg');
							imagejpeg($imageThumb, $thumbPath);
							imagedestroy($imageThumb);
							
						}
						// } Decode & upload image Thumb
						
						// { Decode & upload image Full
						$fullPath = '/var/www/html/src/images/buildings/' . $v['buildingID'] . 'L' . '.jpg';
						$imageFull = str_replace(' ', '+', $v['image']);
						$imageFull = base64_decode($imageFull);
						$imageFull = imagecreatefromstring($imageFull);
						
						if($imageFull != false) {
							
							header('Content-Type: image/jpeg');
							imagejpeg($imageFull, $fullPath);
							imagedestroy($imageFull);
							
						}
						// } Decode & upload image Full
						
						// { Insert Path into Record
						$Drecords[$k]['thumbnail'] = str_replace('/var/www', '', $thumbPath);
						$Drecords[$k]['image']     = str_replace('/var/www', '', $fullPath);
						// } Insert Path into Record
						
					}
					// } Parse Image -> Path
					
					$sql = $sql .
						make($Drecords, 'buildingID', 'buildingID', $table, false) . // Building ID
						make($Drecords, 'imageFull',  'image',      $table, true)  . // Image Full
						make($Drecords, 'imageThumb', 'thumbnail',  $table, true)  . // Image Thumbnail
						make($Drecords, 'isLive',     'isRemoved',  $table, false) . // Is Removed
					'';
					
					break;
				
				// } Building Images
				
				// { Element Images
				case 'elementImage':
					
					// { Parse Image -> Path
					foreach($Drecords as $k => $v) {
						
						// { Decode & upload image Thumb
						$thumbPath = '/var/www/html/src/images/elements/' . $v['elementID'] . 'S' . '.jpg';
						$imageThumb = str_replace(' ', '+', $v['thumbnail']);
						$imageThumb = base64_decode($imageThumb);
						$imageThumb = imagecreatefromstring($imageThumb);
						
						if($imageThumb != false) {
							
							header('Content-Type: image/jpeg');
							imagejpeg($imageThumb, $thumbPath);
							imagedestroy($imageThumb);
							
						}
						// } Decode & upload image Thumb
						
						// { Decode & upload image Full
						$fullPath = '/var/www/html/src/images/elements/' . $v['elementID'] . 'L' . '.jpg';
						$imageFull = str_replace(' ', '+', $v['image']);
						$imageFull = base64_decode($imageFull);
						$imageFull = imagecreatefromstring($imageFull);
						
						if($imageFull != false) {
							
							header('Content-Type: image/jpeg');
							imagejpeg($imageFull, $fullPath);
							imagedestroy($imageFull);
							
						}
						// } Decode & upload image Full
						
						// { Insert Path into Record
						$Drecords[$k]['thumbnail'] = str_replace('/var/www', '', $thumbPath);
						$Drecords[$k]['image']     = str_replace('/var/www', '', $fullPath);
						// } Insert Path into Record
						
					}
					// } Parse Image -> Path
					
					$sql = $sql .
						make($Drecords, 'elementID',  'elementID', $table, false) . // Element ID
						make($Drecords, 'imageFull',  'image',     $table, true)  . // Image Full
						make($Drecords, 'imageThumb', 'thumbnail', $table, true)  . // Image Thumbnail
						make($Drecords, 'isLive',     'isRemoved', $table, false) . // Is Removed
					'';
					
					break;
			
				// } Element Images
				
			}
			
			// Finalise
			$sql = substr($sql, 0, -2);
			$sql = $sql . ' WHERE ' . $table . 'ID IN(' . implode(', ', $DrecordIDs) . ')';
			
			// } Build query
			
			// { Try query
			if(mysql_query($sql)) {
				
				debug(ucfirst($xTable) . 's saved');
				$return = 1;
				
			}
			else {
				
				debug('Error saving ' . $xTable . 's');
				debug($sql);
				
			}
			// } Try query
			
		}
		
		return $return;
		
	}
	// } Update table on Server (P2)
	
	// } FUNCTIONS
	// ===============
	
	// =================
	// { DEBUG
	
	if(!isset($_POST['json'])) {
		
		$_POST['json'] = '

		';
			
		//debug('Debug input data: ' . $_POST['json']);
		
	}
	
	// } [RFL] DEBUG
	// =================
	
	// ==========
	// { MAIN
	
	// { Initialise
	$post = json_decode($_POST['json'], true);
	$reply;
	$reply['connectionType'] = 'putData';
	
	// } Initialise
	
	// { Work
	if($user = authenticate($post['userName'], $post['password'])) {
		
		debug('ID ' . $user['userID'] . ' Authenticated'); // [RFL]
		
		// { Initialise reply
		
		$reply['status'] = 'bad';
		$reply['msg']    = 'Your code-fu is weak!';
		
		// } Initialise reply
		
		// { First comm
		if($post['part'] == 1) {
			
			debug('Request part 1'); // [RFL]
			
			// { Build list of D records
			$Dtasks          = processD('inspection' , $post['Task'])         ;
			$Dbuildings      = processD('building'   , $post['Building'])     ;
			$Dlocations      = processD('location'   , $post['Location'])     ;
			$Delements       = processD('element'    , $post['Element'])      ;
			$DelementTypes   = processD('elementType', $post['ElementDetail']);
			$DbuildingImages = processD('image'      , $post['BuildingImage']);
			$DelementImages  = processD('image'      , $post['ElementImage']) ;
			// } Build list of D records
			
			// { Build list of D record ID's
			$DtaskIDs          = makeArrayOfIDs($Dtasks, 1)         ;
			$DbuildingIDs      = makeArrayOfIDs($Dbuildings, 1)     ;
			$DlocationIDs      = makeArrayOfIDs($Dlocations, 1)     ;
			$DelementIDs       = makeArrayOfIDs($Delements, 1)      ;
			$DelementTypeIDs   = makeArrayOfIDs($DelementTypes, 1)  ;
			$DbuildingImageIDs = makeArrayOfIDs($DbuildingImages, 1);
			$DelementImageIDs  = makeArrayOfIDs($DelementImages, 1) ;
			// } Build list of D record ID's
			
			// { Debug received
			debug('Received:');
			debug('   ' . count($Dtasks)          . ' tasks          (' . implode(', ', $DtaskIDs)          . ')');
			debug('   ' . count($Dbuildings)      . ' buildings      (' . implode(', ', $DbuildingIDs)      . ')');
			debug('   ' . count($Dlocations)      . ' locations      (' . implode(', ', $DlocationIDs)      . ')');
			debug('   ' . count($Delements)       . ' elements       (' . implode(', ', $DelementIDs)       . ')');
			debug('   ' . count($DelementTypes)   . ' elementTypes   (' . implode(', ', $DelementTypeIDs)   . ')');
			debug('   ' . count($DbuildingImages) . ' buildingImages (' . implode(', ', $DbuildingImageIDs) . ')');
			debug('   ' . count($DelementImages)  . ' elementImages  (' . implode(', ', $DelementImageIDs)  . ')');
			// } [RFL] Debug received
			
			// { Build list of S records
			$Stasks          = processS('inspection'   , $DtaskIDs)         ;
			$Sbuildings      = processS('building'     , $DbuildingIDs)     ;
			$Slocations      = processS('location'     , $DlocationIDs)     ;
			$Selements       = processS('element'      , $DelementIDs)      ;
			$SelementTypes   = processS('elementType'  , $DelementTypeIDs)  ;
			$SbuildingImages = processS('buildingImage', $DbuildingImageIDs);
			$SelementImages  = processS('elementImage' , $DelementImageIDs) ;
			// } Build list of S records
			
			// { Build list of S record ID's
			$StaskIDs          = makeArrayOfIDs($Stasks, 1)         ;
			$SbuildingIDs      = makeArrayOfIDs($Sbuildings, 1)     ;
			$SlocationIDs      = makeArrayOfIDs($Slocations, 1)     ;
			$SelementIDs       = makeArrayOfIDs($Selements, 1)      ;
			$SelementTypeIDs   = makeArrayOfIDs($SelementTypes, 1)  ;
			$SbuildingImageIDs = makeArrayOfIDs($SbuildingImages, 1);
			$SelementImageIDs  = makeArrayOfIDs($SelementImages, 1) ;
			// } Build list of S record ID's
			
			// { Debug existing
			debug('Existing:');
			debug('   ' . count($Stasks)          . ' tasks          (' . implode(', ', $StaskIDs)          . ')');
			debug('   ' . count($Sbuildings)      . ' buildings      (' . implode(', ', $SbuildingIDs)      . ')');
			debug('   ' . count($Slocations)      . ' locations      (' . implode(', ', $SlocationIDs)      . ')');
			debug('   ' . count($Selements)       . ' elements       (' . implode(', ', $SelementIDs)       . ')');
			debug('   ' . count($SelementTypes)   . ' elementTypes   (' . implode(', ', $SelementTypeIDs)   . ')');
			debug('   ' . count($SbuildingImages) . ' buildingImages (' . implode(', ', $SbuildingImageIDs) . ')');
			debug('   ' . count($SelementImages)  . ' elementImages  (' . implode(', ', $SelementImageIDs)  . ')');
			// } [RFL] Debug existing
			
			// { Build replies
			$reply['Task']          = processReply($Dtasks         , $Stasks)         ;
			$reply['Building']      = processReply($Dbuildings     , $Sbuildings)     ;
			$reply['Location']      = processReply($Dlocations     , $Slocations)     ;
			$reply['Element']       = processReply($Delements      , $Selements)      ;
			$reply['ElementDetail'] = processReply($DelementTypes  , $SelementTypes)  ;
			$reply['BuildingImage'] = processReply($DbuildingImages, $SbuildingImages);
			$reply['ElementImage']  = processReply($DelementImages , $SelementImages) ;
			// } Build replies
			
			// { Debug updating
			debug('Updating:');
			debug('   ' . count($reply['Task'])          . ' tasks          ');
			debug('   ' . count($reply['Building'])      . ' buildings      ');
			debug('   ' . count($reply['Location'])      . ' locations      ');
			debug('   ' . count($reply['Element'])       . ' elements       ');
			debug('   ' . count($reply['ElementDetail']) . ' elementTypes   ');
			debug('   ' . count($reply['BuildingImage']) . ' buildingImages ');
			debug('   ' . count($reply['ElementImage'])  . ' elementImages  ');
			// } [RFL] Debug updating
			
			$reply['status'] = 'good';
			unset($reply['msg']);
		
		}
		// } First comm
		
		// { Second comm
		if($post['part'] == 2) {
			
			debug('Request part 2');
			
			// { Build received
			$Dtasks          = $post['Task']         ;
			$Dbuildings      = $post['Building']     ;
			$Dlocations      = $post['Location']     ;
			$Delements       = $post['Element']      ;
			$DelementTypes   = $post['ElementDetail'];
			$DbuildingImages = $post['BuildingImage'];
			$DelementImages  = $post['ElementImage'] ;
			// } Build received
			
			// { Build list of D record ID's
			$DtaskIDs          = makeArrayOfIDs($Dtasks, 2)         ;
			$DbuildingIDs      = makeArrayOfIDs($Dbuildings, 2)     ;
			$DlocationIDs      = makeArrayOfIDs($Dlocations, 2)     ;
			$DelementIDs       = makeArrayOfIDs($Delements, 2)      ;
			$DelementTypeIDs   = makeArrayOfIDs($DelementTypes, 2)  ;
			$DbuildingImageIDs = makeArrayOfIDs($DbuildingImages, 2);
			$DelementImageIDs  = makeArrayOfIDs($DelementImages, 2) ;
			// } Build list of D record ID's
			
			// { Debug received
			debug('Received:');
			debug('   ' . count($Dtasks)          . ' tasks          (' . implode(', ', $DtaskIDs)          . ')');
			debug('   ' . count($Dbuildings)      . ' buildings      (' . implode(', ', $DbuildingIDs)      . ')');
			debug('   ' . count($Dlocations)      . ' locations      (' . implode(', ', $DlocationIDs)      . ')');
			debug('   ' . count($Delements)       . ' elements       (' . implode(', ', $DelementIDs)       . ')');
			debug('   ' . count($DelementTypes)   . ' elementTypes   (' . implode(', ', $DelementTypeIDs)   . ')');
			debug('   ' . count($DbuildingImages) . ' buildingImages (' . implode(', ', $DbuildingImageIDs) . ')');
			debug('   ' . count($DelementImages)  . ' elementImages  (' . implode(', ', $DelementImageIDs)  . ')');
			// } Debug received
			
			// { Process updates
			$progress = 0;
			$progress = $progress + updateTable($Dtasks,          $DtaskIDs,          'inspection')   ;
			$progress = $progress + updateTable($Dbuildings,      $DbuildingIDs,      'building')     ;
			$progress = $progress + updateTable($Dlocations,      $DlocationIDs,      'location')     ;
			$progress = $progress + updateTable($Delements,       $DelementIDs,       'element')      ;
			$progress = $progress + updateTable($DelementTypes,   $DelementTypeIDs,   'elementType')  ;
			$progress = $progress + updateTable($DbuildingImages, $DbuildingImageIDs, 'buildingImage');
			$progress = $progress + updateTable($DelementImages,  $DelementImageIDs,  'elementImage') ;
			//$progress += 2;
			
			// } Process updates
			
			// { Update Job Statuses
			foreach($Dtasks as $k => $v) {
				
				// { Declined
				if($v['isNew'] == 1 && $v['isDeclined'] == 1) {
					
					//debug('Task: ' . $v['serverID']);
					changeJobStatus($v['serverID'], '0.2');
					
				}
				// } Declined 
				
				// { Accepted
				if($v['isNew'] == 1 && $v['isAccepted'] == 1) {
					
					// { New
					if($v['isResubmitted'] == 0) {
						
						changeJobStatus($v['serverID'], '2.0');
					}
					// } New
					
					// { Resub
					if($v['isResubmitted'] == 1) {
						
						changeJobStatus($v['serverID'], '2.1');
					}
					// } Resub
					
				}
				// } Accepted
				
				// { Completed
				if($v['isCompleted'] == 1) {
					
					// { New
					if($v['isResubmitted'] == 0) {
						
						changeJobStatus($v['serverID'], '4.0');
					}
					// } New
					
					// { Resub
					if($v['isResubmitted'] == 1) {
						
						changeJobStatus($v['serverID'], '4.1');
					}
					// } Resub
					
				}
				// } Completed
				
			}
			// } Update Job Statuses
			
			// { Cascade Removed
			
			// { Building Down
			foreach($Dbuildings as $k => $v) {
				
				if($v['isRemoved'] == 1) {
					
					$k = $v['buildingID'];
					
					$sql = "
						UPDATE
							Location l,
							Element e, 
							ElementType et 
						SET
							l.isLive = 0,
							e.isLive = 0,
							et.isLive = 0
						WHERE
							l.buildingID = $k AND
							e.locationID = l.locationID AND
							et.elementTypeID = e.elementTypeID
					";
					
					if(mysql_query($sql)) {
						
						debug('Cascaded from Building ' . $k);
						
					}
					else {
						
						debug('Failed cascading from Building: ' . $sql);
						
					}
					
				}
				
			}
			// } Building Down
			
			// { Location Down
			foreach($Dlocations as $k => $v) {
				
				if($v['isRemoved'] == 1) {
					
					$k = $v['locationID'];
					
					$sql = "
						UPDATE
							Element e, 
							ElementType et 
						SET
							e.isLive = 0,
							et.isLive = 0
						WHERE
							e.locationID = $k AND
							et.elementTypeID = e.elementTypeID
					";
					
					if(mysql_query($sql)) {
						
						debug('Cascaded from Location ' . $k);
						
					}
					else {
						
						debug('Failed cascading from Location: ' . $sql);
						
					}
					
				}
				
			}
			// } Location Down
			
			// { Element Down
			foreach($Delements as $k => $v) {
				
				if($v['isRemoved'] == 1) {
					
					$k = $v['elementID'];
					
					$sql = "
						UPDATE
							Element e,
							ElementType et 
						SET
							et.isLive = 0
						WHERE
							e.elementID = $k AND
							et.elementTypeID = e.elementTypeID
					";
					
					if(mysql_query($sql)) {
						
						debug('Cascaded from Element ' . $k);
						
					}
					else {
						
						debug('Failed cascading from Element: ' . $sql);
						
					}
					
				}
				
			}
			// } Element Down
			
			// } Cascade Removed
			
			// { Build reply
			if($progress == 7) {
				
				$reply['status'] = 'good';
				unset($reply['msg']);
				
			}
			else {
				
				$reply['status'] = 'bad';
				$reply['msg']    = 'No idea. Debug me fool';
				
			}
			// } Build reply
			
		}
		// } Second comm
		
	}
	else {
		
		$reply['status'] = 'bad';
		$reply['msg']    = 'Invalid authentication';
		
	}
	// } Work
	
	// { Reply
	
	debug('Reply: ' . json_encode($reply));
	
	echo json_encode($reply);
	// } Reply
	
	// } MAIN
	// ==========
	
?>