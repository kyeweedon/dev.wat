<?php

	require_once('/var/www/html/mech/php/all.php');
	
?>

<!DOCTYPE HTML>
<html>
	<head>
		
		<!-- Style Sheets -->
		<link rel="stylesheet" type="text/css" href="/mech/css/all.css"/>
		<link rel="stylesheet" type="text/css" href="/mech/css/ttip.css"/>
		<link rel="stylesheet" type="text/css" href="/mech/css/smoothness/smoothness.css"/>
		<link rel="stylesheet" type="text/css" href="index.css"/>
		
		<!-- Meta Data -->
		<meta charset="UTF-8" />
		<meta name="author" content="Kye Weedon"/>
		<title>Watson Claim Centre</title>
		
	</head>
	
	<body>
	
		<!-- Top Bar -->
		<section id="secTopBar">
			
			<div id="tbCentre">
				
				<a href="" style="text-decoration:none">
				
					<img id="tbcLogo" src="/src/images/logoHeader.png"/>
				
				</a>
				<div id="tbcLinks">
					
					<a id="tbclLogout" href="../slatfatf.php">Logout</a>
					
				</div>
				
			</div>
			
		</section>
		
		<!-- Top Breadcrumb Bar -->
		<nav id="mainNav">
		
			<a id="navJobs">Claims</a>/
			<a id="newJob">+</a>
			
		</nav>
		
		<!-- Main Content -->
		<section class="mainBody">
		
			<!-- Unassigned (0.0 - 0.1 - 0.2) -->
			<div class="jobsBox" id="boxUnassigned">
				
				<a class="statusBar" id="barUnassigned">New</a>
				<div></div>
				
			</div>
			
			<!-- Assigned (1.0) -->
			<div class="jobsBox" id="boxAssigned">
				
				<a class="statusBar" id="barAssigned"  >Awaiting Confirmation</a>
				<div></div>
				
			</div>
			
			<!-- Accepted (2.0 - 2.1 - 3.0 - 3.1) -->
			<div class="jobsBox" id="boxAccepted">
			
				<a class="statusBar" id="barAccepted"  >With Inspector</a>
				<div></div>
				
			</div>
			
			<!-- Returned (4.0 - 4.1 - 4.2) -->
			<div class="jobsBox" id="boxReturned">
				
				<a class="statusBar" id="barReturned"  >For Approval</a>
				<div></div>
				
			</div>
			
			<!-- Completed (9.9) -->
			<div class="jobsBox" id="boxCompleted">
				
				<a class="statusBar" id="barCompleted" >Recently Completed</a>
				<div></div>
				
			</div>
			
		</section>
		
		<!-- New Job Modal -->
		<?php require('/var/www/html/home/newJob.php'); ?>
		
		<!-- Preload -->
		<aside class="hidden" id="preload"></aside>
		
		<!-- JavaScript -->
		<script type="text/javascript" src="/mech/js/jq.js"></script>
		<script type="text/javascript" src="/mech/js/jqUI.js"></script>
		<script type="text/javascript" src="/mech/js/jqTimePicker.js"></script>
		<script type="text/javascript" src="/mech/js/jqFlip.js"></script>
		<script type="text/javascript" src="/mech/js/ttip.js"></script>
		<script type="text/javascript" src="/mech/js/mask.js"></script>
		<script type="text/javascript" src="/mech/js/all.js"></script>
		<script type="text/javascript" src="index.js"></script>
	</body>
</html>