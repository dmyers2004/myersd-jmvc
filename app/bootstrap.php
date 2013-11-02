<?php
/**
* DMyers Super Simple MVC
*
* @package    Bootstrap File
* @language   PHP
* @author     Don Myers
* @copyright  Copyright (c) 2011
* @license    Released under the MIT License.
*/

/*
SetEnv MODE DEBUG/TEST
Defaults to no errors displayed
*/
ini_set('display_errors','Off');

switch ($_SERVER['MODE']) {
	case 'DEBUG':
		error_reporting(E_ALL);
		ini_set('display_errors','On');
	break;
	case 'TEST':
		error_reporting(E_ALL & ~E_NOTICE);
		ini_set('display_errors','On');
	break;
}

/* Where is this bootstrap file */
$_SERVER['APP_PATH'] = __DIR__;

/* error / 404 handler - you can point this to your own function */
set_exception_handler('error_handler');

/* put the config variables into $_SERVER to make them easier to read globally */
/* with http:// and with trailing slash - auto detect https adjustment may be needed here */
$_SERVER['BASE_URL'] = trim('http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER["SCRIPT_NAME"]),'/');

/* set any other config vars here - quick dirty for this simple framework */
//$_SERVER['CONFIG'] = 'something';

/* do all your auto includes for the ENTIRE application here */
//require('libraries/database.php');
require('libraries/general.php');

/* Anything you want included as Default view variable */
$data['sitename'] = 'Web Site Template';

/* Session */
session_start();

/* only "reserved" view variable */
$data['BASE_URL'] = $_SERVER['BASE_URL'];

$_SERVER['IS_AJAX'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

/* get the url pieces */
$_SERVER['SEGS'] = explode('/',trim(urldecode(substr(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),strlen(dirname($_SERVER["SCRIPT_NAME"])))),'/'));

/* If they didn't include a controller and method use the default */
$_SERVER['CONTROLLER'] = (!empty($_SERVER['SEGS'][0])) ? strtolower(array_shift($_SERVER['SEGS'])) : 'main';
$_SERVER['FUNCTION'] = (!empty($_SERVER['SEGS'][0])) ? strtolower(array_shift($_SERVER['SEGS'])) : 'index';
$_SERVER['METHOD'] = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));

if ($_SERVER['FUNCTION']{0} == '_')
	throw new Exception('Illegal Method '.$_SERVER['FUNCTION'],4001);

/* does the controller file exist? */
if (!file_exists($_SERVER['APP_PATH'].'/controllers/'.$_SERVER['CONTROLLER'].'.php'))
	throw new Exception('Controller /controllers/'.$_SERVER['CONTROLLER'].'.php Not Found',4002);

/* yes load it! */
require($_SERVER['APP_PATH'].'/controllers/'.$_SERVER['CONTROLLER'].'.php');

/* is the class named properly? */
if (!class_exists($_SERVER['CONTROLLER'].'Controller',FALSE))
	throw new Exception('Controller Class '.$_SERVER['CONTROLLER'].'Controller Not found in /controllers/'.$_SERVER['CONTROLLER'].'.php',4003);

/* yes build the mvc class */
$classname = $_SERVER['CONTROLLER'].'Controller';
$mvc = new $classname($data);

/* you can attach stuff now */
/* $mvc->attach('Thislib');

/* database connection - if needed */
//database::connect('localhost','root','root','bolt');

/* does the class method exist? */
if (method_exists($mvc,$_SERVER['FUNCTION'].'Action')) {
	call_user_func_array(array($mvc,$_SERVER['FUNCTION'].'Action'),$_SERVER['SEGS']);
	$mvc->view(); /* try to display the view - if it's there */
}

/* does the a REST looking method exist? */
if (method_exists($mvc,$_SERVER['FUNCTION'].$_SERVER['METHOD'].'Action')) {
	/* if this is a PUT - jam the PUT into Global POST to make it easier to read */
	if ($_SERVER['METHOD'] == 'Put')
		parse_str(file_get_contents('php://input'), $_POST);
	call_user_func_array(array($mvc,$_SERVER['FUNCTION'].$_SERVER['METHOD'].'Action'), $_SERVER['SEGS']);
	$mvc->view(); /* try to display the view - if it's there */
}

/* Not sure what else to - do throw an error */
throw new Exception('Methods '.$_SERVER['FUNCTION'].'Action or '.$_SERVER['FUNCTION'].$_SERVER['METHOD'].'Action Not Found in '.$_SERVER['CONTROLLER'].'Controller Class',4004);

/* rewrite this function for your own custom error handler */
function error_handler($exception) {
	header('HTTP/1.0 404 Not Found');
	extract(array('error'=>$exception->getMessage(),'errorno'=>$exception->getCode(),'BASE_URL'=>$_SERVER['BASE_URL']));
	require($_SERVER['APP_PATH'].'/views/404.php');
	exit;
}

/* if something needs a reference to the controller */
function &mvc() {
	global $mvc;
	return $mvc;
}

function __autoload($name) {
	$path = (substr($name,0,1) >= 'A' && substr($name,0,1) <='Z') ? 'libraries' : 'models';

	if (!file_exists($_SERVER['APP_PATH'].'/'.$path.'/'.$name.'.php'))
		throw new Exception('File: '.$_SERVER['APP_PATH'].'/'.$path.'/'.$name.'.php Not Found',4005);

	require_once($_SERVER['APP_PATH'].'/'.$path.'/'.$name.'.php');
}

/* Base Class & SuperObject */
class Controller {
	public $data = array();

	public function __construct($data) {
		$this->data = $data;
	}

	public function __set($name,$value) {
		$this->data[$name] = $value;
		return $this;
	}

	public function __get($name) {
		return $this->data[$name];
	}

	/* set & get view data */
	public function data($name='',$val='#yohoho#') {
		if ($val == '#yohoho#') {
			return $this->data[$name];
		} else {
			$this->data[$name] = $val;
			return $this;
		}
	}

	/*
	Attach
	library Starts with Captial letter
	model Starts with lowercase letter
	*/
	public function attach($name,$diffname=NULL,$params=array()) {
		$params = (is_array($diffname)) ? $diffname : $params;
		$classname = ($diffname == NULL || is_array($diffname)) ? strtolower($name) : $diffname;

		$class = new ReflectionClass($name);
		$this->data[$classname] = $class->newInstanceArgs($params);

		return $this;
	}

	/* load view into variable or output */
	/* the parameters need to be unique to not run into the extracted variables */
	public function view($mvc_viewfile=NULL,$mvc_viewvariable=NULL,$mvc_direct_output=FALSE) {
		/* if view filename is null then setup defaults */
		if ($mvc_viewfile === NULL) {
			$mvc_viewvariable = 'body';
			$mvc_viewfile = $_SERVER['CONTROLLER'].'/'.$_SERVER['FUNCTION'];
			$mvc_direct_output = TRUE;
		}

		/* if sent in a view variable put the view file into a page variable */
		if ($mvc_viewvariable !== NULL) {
			if (file_exists($_SERVER['APP_PATH'].'/views/'.$mvc_viewfile.'.php')) {
				extract($this->data);
				ob_start();
				require($_SERVER['APP_PATH'].'/views/'.$mvc_viewfile.'.php');
				$this->data[$mvc_viewvariable] = ob_get_clean();
				if ($mvc_direct_output === FALSE) {
					return $this;
				} else {
					$mvc_viewfile = ($mvc_direct_output === TRUE) ? 'layout' : $mvc_direct_output ;
				}
			}
		}

		/* output the template */
		/* is it there? */
		if (file_exists($_SERVER['APP_PATH'].'/views/'.$mvc_viewfile.'.php')) {
			extract($this->data);
			require($_SERVER['APP_PATH'].'/views/'.$mvc_viewfile.'.php');
		}
		exit;
	}

} /* end mvc controller class */