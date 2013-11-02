<?php
/**
 * DMyers Super Simple MVC
 *
 * @package    Bootstrap File
 * @language   PHP
 * @author     Don Myers
 * @copyright  Copyright (c) 2011
 * @license    Released under the MIT License.
 *
 */

class general {

	/* basic create url */
	static public function createurl($url='') {
		return $_SERVER['BASE_URL'].trim($url,'/');
	}

	/* basic redirect */
	static public function redirect($url='') {
		header('Location: '.$_SERVER['BASE_URL'].trim($url,'/'));
		header('Connection: close');
		exit;
	}

	/* basic clean */
	static public function clean($dirtyinput) {
		// tab, linefeed, return, ascii space (32) - ascii ~ (126) allowed only!
		return preg_replace("![^\t|\n|\r|\x20-\x7E]!", '', $dirtyinput);
	}

	static public function securitycheck() {
		/* do something */
		return TRUE;
	}
	
	static public function json($data) {
		header('Content-type: text/json');
		header('Content-type: application/json');
		
		die(json_encode((array)$data));
	}

} /* end general */