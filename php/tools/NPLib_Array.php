<?
function NP_asorti(array &$array)
{
    $copy = $array;

    $array = array_map('strtolower', $array);
    asort($array);

    foreach ($array as $index => $value) {
        $array[$index] = $copy[$index];
    }

    return $array;
}
?>