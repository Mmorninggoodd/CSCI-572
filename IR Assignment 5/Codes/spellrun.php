<?php
	// This file generates serialized dictionary separately helping to avoid memory errors. 
	include 'SpellCorrector.php'; 
	$check = SpellCorrector::correct("octabr");
	echo $check;  

?>
