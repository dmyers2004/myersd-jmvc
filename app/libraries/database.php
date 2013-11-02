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

class database {
	static public function connect($host,$user,$password,$database) {
		$bol = @mysql_connect($host,$user,$password);
		if (!$bol) throw new Exception('Cannot Connect to Database Server');

		$bol = mysql_select_db($database);
		if (!$bol) throw new Exception('Cannot Connect to Database Server');
	}

	static public function dbc2data($dbc,$name) {
		while ($dbr = mysql_fetch_assoc($dbc))
			$ary[] = $dbr;
		mvc()->data($name,(array)$ary);
	}

	static public function dbr2data($dbr) {
		foreach ($dbr as $n=>$v)
			mvc()->data[$n] = $v;
	}

	static public function query($query= NULL) {
		if (!query) return FALSE;

		$result = @mysql_query($query);

		if (mysql_errno() > 0)
			throw new Exception('Query Failed <li>errorno='.mysql_errno().'<li>error='.mysql_error().'<li>query='.$query);

		return $result;
	}

	static public function insertupdate($t,$f,$where=FALSE) {
		if (!$where) { // insert
			foreach ($f as $key => $value) $fields .= '`'.$key.'`, ';
			foreach ($f as $key => $value) $values .= "'".mysql_real_escape_string($value)."', ";
			$rtn_sql = 'insert into '.$t.' ('.rtrim($fields,', ').') values ('.rtrim($values,', ').')';
		} else { // update
			foreach ($f as $key => $value) $sql .= "`".$key."`='".mysql_real_escape_string($value)."', "; // quote it all can't hurt
			$rtn_sql = 'update '.$t.' set '.rtrim($sql,', ').' where '.$where;
		}
		database::query($rtn_sql);
	}

}