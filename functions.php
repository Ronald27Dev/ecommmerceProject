<?php

	function formatPrice(float $vlprice){

		return number_format($vlprice, 2, ",", ".");
	}

	function pr($value='',$die = 1){

		echo "========================================================================<pre>";
		print_r($value);
		echo "</pre>========================================================================";
	
		if($die != null){
			die;
		}
	}

	function vr($value='',$die = 1){

		echo "========================================================================<pre>";
		var_dump($value);
		echo "</pre>========================================================================";
	
		if($die != null){
			die;
		}
	}
?>