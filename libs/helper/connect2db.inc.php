<?php

	$conn[host]		= "";
	$conn[user]		= "";
	$conn[password		= "";
	$conn[db]		= "";

	$db = new mysqli($conn[host], $conn[user], $conn[password]) or die($db->error);
	$db->select_db($conn[db]) or die($db->error);

	@mysql_connect($conn[host], $conn[user], $conn[password]);
	@mysql_select_db($conn[db]);

?>
