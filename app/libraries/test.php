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

class test {
	public $vara;
	public $varb;

	public function __construct($a='',$b='') {
		$this->vara = $a;
		$this->varb = $b;
	}

	public function output() {
		$html = 'var a = '.$this->vara.' var b = '.$this->varb.'<br/>';
		return 'test output '.$html;
	}

	public function debug() {
		return print_r($this,true);
	}
}