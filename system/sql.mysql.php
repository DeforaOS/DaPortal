<?php //system/sql.mysql.php



//check url
if(!ereg('/index.php$', $_SERVER['PHP_SELF']))
	exit(header('Location: ../index.php'));

define('SQL_TRUE', '1');
define('SQL_FALSE', '0');


function _query($query)
{
	global $debug, $connection;

	_info($query);
	if(!$debug)
		return mysql_query($query);
	$ret = mysql_query($query);
	if(($info = mysql_info($connection)) != NULL)
		_info($info);
	return $ret;
}


function _sql_array($query)
{
	if(($res = _query($query)) == FALSE)
		return FALSE;
	for($array = array(); ($a = mysql_fetch_array($res)) != FALSE;
			$array[] = $a);
	return $array;
}


function _sql_enum($table, $field)
{
	$str = _sql_array('SHOW COLUMNS FROM '.$table." LIKE '$field';");
	$str = ereg_replace("^enum\('(.*)'\)$", '\1', $str[0]['Type']);
	return split("[']?,[']?", $str);
}


function _sql_id($table, $field)
{
	return mysql_insert_id();
}


function _sql_offset($offset, $limit)
{
	return 'LIMIT '.$offset.', '.$limit;
}


function _sql_single($query)
{
	if(($res = _sql_query($query)) == FALSE)
		return FALSE;
	if(mysql_num_rows($res) != 1 || mysql_num_fields($res) != 1)
		return FALSE;
	return mysql_result($res, 0);
}


//main
global $dbtype, $dbhost, $dbuser, $dbpassword, $dbname;
if(($connection = mysql_connect($dbhost, $dbuser, $dbpassword)) != FALSE)
	if(mysql_select_db($dbname, $connection) != TRUE)
	{
		mysql_close($connection);
		$connection = FALSE;
	}

?>
