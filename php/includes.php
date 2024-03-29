<?
/**
 * NPLib - PHP
 * @package np-lib
 * @subpackage
 * @version 20090624
 *
 * @author Daniel Pecos Mart�nez
 * @copyright Copyright (c) Daniel Pecos Mart�nez
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */

if (!defined("NPLIB_PATH")) {
	die("You must define NPLIB_PATH before using np-lib");
} else {
	require_once(NPLIB_PATH."tools/NPLib_Common.php");

	require_once(NPLIB_PATH."tools/NPLib_Array.php");
	require_once(NPLIB_PATH."tools/NPLib_Image.php");
	require_once(NPLIB_PATH."tools/NPLib_Net.php");
	require_once(NPLIB_PATH."tools/NPLib_Object.php");
	require_once(NPLIB_PATH."tools/NPLib_PHP4_Compatible.php");
	require_once(NPLIB_PATH."tools/NPLib_Security.php");
	require_once(NPLIB_PATH."tools/NPLib_Sql_2.php");
	require_once(NPLIB_PATH."tools/NPLib_String.php");
	require_once(NPLIB_PATH."tools/NPLib_System.php");


	require_once(NPLIB_PATH."classes/NPLogger.class.php");
	require_once(NPLIB_PATH."classes/NPLib_YUI.php");

	require_once(NPLIB_PATH."classes/NPLib_Exception.php");
	require_once(NPLIB_PATH."classes/NPLib_SQL_Object.php");
	require_once(NPLIB_PATH."classes/NPLib_MySQL_Connection.php");
	require_once(NPLIB_PATH."classes/NPLib_MySQL_Toolkit.php");
}
?>
