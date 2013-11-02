<?php

function isloggedin() {
	if ($_SESSION['loggedin']) return true;
	redirect('/');
}

function decode($input) {
	$private = file_get_contents($_SERVER['APP_PATH'].'/libraries/private.key');
	// decrypt argument
	if (!openssl_private_decrypt($input, $output, openssl_pkey_get_private($private))) output(404,'Private Key Error');
	return $output;
}

function output($status,$msg,$session='') {
	die(json_encode(array('status'=>$status,'msg'=>$msg,'session'=>$session)));
}