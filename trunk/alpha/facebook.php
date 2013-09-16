<?php
	include_once 'backend/user.php';
	//echo FacebookUrl($_GET['q']);
	header( 'Location: ' . FacebookUrl($_GET['q'])) ;
?>