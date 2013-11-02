<?php
class mainController extends Controller {
	function indexAction() {
		$this->view('main/index','body',true);
	}

	function loginAction() {
		if (!$_SERVER['IS_AJAX']) die();
		
		require('app/libraries/security.php');
		
		$name = $_POST['name'];
		$password = decode(base64_decode($_POST['epassword']));
		$_SESSION['password'] = $password;
		$timestamp = $_POST['ts'];
		$sent_hmac = $_POST['hmac'];
		$utc_time = date('U');

		$hmac_sha = $_POST['hmac'];
		$hmac = $name.$password.$timestamp;
		$hmac = hash('sha256',$hmac);

		if ($hmac != $sent_hmac) {
			$json['status'] = 404;
			$json['msg'] = 'HMAC Failed';
			$this->data('json',$json)->view('json');
		}

		if (abs($utc_time - (int)$timestamp) > 300) {
		$json['status'] = 404;
		$json['msg'] = 'Timestamp expired. Client and server times may be out of sync.';
		$this->data('json',$json)->view('json');
		}

		session_start();
		$_SESSION['loggedin'] = true;
		$json['status'] = 200;
		$json['msg'] = 'ok';
		$json['session'] = session_id();
		$this->data('json',$json)->view('json');
	}

	function welcomeAction() {
		if ($_SESSION['loggedin'] !== true) general::redirect();
	}

	function logoutAction() {
		$_SESSION['loggedin'] = false;
		session_destroy();
		general::redirect();
	}

	function aesAction() {
		require($_SERVER['APP_PATH'].'/libraries/aes.class.php');

		$password = hash('sha256',$_SESSION['password']);

		$aes = AesCtr::decrypt($_POST['aes'], $password, 128);
		$json['reply'] = '<p>You sent in: '.$aes.'</p>';
		$this->data('json',$json)->view('json');
	}

	function debugAction() {
		echo '<pre>';
		$mvc = &mvc();
		print_r($mvc);
		print_r($this);
		print_r($_SERVER);
	}

}