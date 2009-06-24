<?
/** 
 * NPLib - PHP
 * 
 * Operating system related functions
 * 
 * @package np-lib
 * @subpackage 
 * @version 20090624
 * 
 * @author Daniel Pecos Martínez
 * @copyright Copyright (c) Daniel Pecos Martínez 
 * @license http://www.gnu.org/licenses/lgpl.html  LGPL License
 */
function NP_directoryList($directory, $pattern = null) {

    // create an array to hold directory list
    $results = array();

    // create a handler for the directory
    $handler = opendir($directory);

    // keep going until all files in directory have been read
    while ($file = readdir($handler)) {
        if ($file != '.' && $file != '..')
            if ($pattern != null && ereg($pattern, $file))
                $results[] = $file;
            else if ($pattern == null)
                $results[] = $file;
    }

    closedir($handler);

    return $results;
}

?>