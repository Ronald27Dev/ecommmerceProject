<?php

	use Ronald\Model\User;

	function formatPrice($vlprice){

		return number_format($vlprice, 2, ",", ".");
	}


	function checkUserName(){

		$user = User::getFromSession();
		
		return $user->getdesperson();
	}

	function checkLogin($inadmin = false) {
		
		return User::checkLogin($inadmin);
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