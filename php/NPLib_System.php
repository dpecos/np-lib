<?
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