<?php
   
	//     E: mail@kyeweedon.com
	//    BY: Kye Weedon
	//   FOR: Metric Pty Ltd
	//  DATE: February 2012
	// ABOUT: 
	
	
	// PHP Debugger console
	// Remove for live
	require_once('/var/www/html/mech/php/PhpConsole.php');
	session_start();
	
	// Browser test
	$u_agent = $_SERVER['HTTP_USER_AGENT']; 
	if(preg_match('/Chrome/i', $u_agent) || preg_match('/Safari/i', $u_agent)) {} else {
		
		// Redirect to fail page
		debug('Browser fail.');
		header("Location: /mech/php/outdatedBrowser.php");	
		
	}

	// If session is already authenticated
	if(isset($_SESSION['status']) && $_SESSION['status'] === 'good') {
		// Redirect to management page
		debug('Session active: Routing to home/index.php');
		header("Location: /home/");	
	}
	
	debug('Render index.php');

?>

<!DOCTYPE HTML>
<html>
	<head>
		<!-- Style Sheets -->
		
		<link rel="stylesheet" type="text/css" href="index.css"/>
		
		<!-- Meta Data -->
		<meta charset="UTF-8" />
		<meta name="author" content="Kye Weedon"/>
		<title>Watson Claim Centre</title>
		
	</head>
	
	<body>
		
		<section id="secTopBar">
			
			<div id="tbCentre">
			
				<img id="tbcLogo" src="/src/images/logoHeader.png"/>
				
			</div>
			
		</section>
		
	   	<section id="secLogin">
	   		
	   		<div id="login">
	   		
				<!-- Watson Title -->
				<div id="loginTitle">Sign in with your Watson ID.</div>
				
				<!-- Login Form -->
				<form id="loginForm" action="javascript:doLogin()">
					
					<input id="userName" placeholder="Watson ID" autocomplete=off type="text"    />
					<input id="passWord" placeholder="Password"  autocomplete=off type="password"/>
					<input class="hidden" type="submit"/>
					<button id="go">Sign in</button>
	
				</form>
				
				<a id="loginForgot" href="">Forgot password?</a>
			
			</div>
			
		</section>
		
		<section id="footer">
		
			Copyright 2012 Metric Pty Ltd
			<div id="footerLinks">
				<a href="">Terms of Use</a>&nbsp; | &nbsp;<a href="">Privacy</a>
			</div>
		
		</section>
		
		<!-- Preload box -->
		<aside class="hidden" id="preloader"></aside>
		
		<!-- JavaScript -->
		<script type="text/javascript" src="/mech/js/jq.js"></script>
		<script type="text/javascript" src="/mech/js/jqUI.js"></script>
		<script type="text/javascript" src="index.js"></script>
	
	</body>
</html>