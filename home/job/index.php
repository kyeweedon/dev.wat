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
				
				<a href="../" style="text-decoration:none">
				
					<img id="tbcLogo" src="/src/images/logoHeader.png"/>
				
				</a>
				<div id="tbcLinks">
					
					<a id="tbclLogout" href="../../slatfatf.php">Logout</a>
					
				</div>
				
			</div>
			
		</section>
		
		<!-- Top Breadcrumb Bar -->
		<nav id="mainNav">
		
			<a id="navJobs">Claims</a>
			<a id="navJob"></a>
			
		</nav>
	
		<!-- Modal -->
		<aside id="modalBack">
			
			<div id="modal">
			
				<div id="modalText1"></div>
				<div id="modalText2"></div>
				<div id="modalBox"></div>
				
				<a class="modalButton" id="modalButtonNo"></a>
				<a class="modalButton" id="modalButtonYes"></a>
				
			</div>
			
		</aside>
		
		<!-- Main Section -->
		<section class="mainBody">
		
			<!-- Popup -->
			<aside id="popup">
				<div id="popupHead"></div>
				<div id="popupBody"></div>
			</aside>
			
			<!-- Job Info -->
			<div class="jobInfo">
				
				<div id="jiTop">
					<div id="jitAddress"></div>
					<div id="jitPDF">Export as PDF</div>
				</div>
				<div id="jiClaimant"     ></div>
				<div id="jiNumbers">
					<div id="jinPhoneMobile"  ></div>
					<div id="jinPhoneLandline"></div>
				</div>
				<div id="jiButtonsLeft">
					<a id="jiblClaimBrief"    >Job Brief</a>
					<a id="jiblCausationNotes">Causation Notes</a>
				</div>
				<div id="jiButtonsRight">
					<a class="jibr" id="jibrAssign"></a>
					<a class="jibr" id="jibrResub"></a>
					<a class="jibr" id="jibrApprove"></a>
				</div>
			</div>
			
			<!-- Current Task -->
			
		</section>
		
		<!-- Preload -->
		<aside class="hidden" id="preload"></aside>
		
		<!-- JavaScript -->
		<script type="text/javascript" src="/mech/js/jq.js"></script>
		<script type="text/javascript" src="/mech/js/jqUI.js"></script>
		<script type="text/javascript" src="/mech/js/ttip.js"></script>
		<script type="text/javascript" src="/mech/js/jqSelectBox.js"></script>
		<script type="text/javascript" src="/mech/js/all.js"></script>
		<script type="text/javascript" src="index.js"></script>
	</body>
</html>