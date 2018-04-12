<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $page_title; ?> | Julien Miclo</title>

		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Material+Icons|Source+Sans+Pro:400,700|Josefin+Sans:300|Montserrat:400,600">
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/stylesheets/normalize.css">
		<link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/stylesheets/master.css">
	</head>
	<body>
		<div class="modal-container"></div>
		<div class="layout-container">
			<div class="navigation-mobile">
				<button id="buttonMenu" class="button-menu"><i class="material-icons">menu</i></button>
			</div>
			<div class="navigation-container">
				<?php include LAYOUTS_PATH.'/partials/navigation.php'; ?>
			</div>
			<div class="main-container">