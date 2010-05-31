<?
/* ---------------------
* @function  array_flatten
* @param     array
* @since     0.1
* @return    array
* @notes     flatten associative multi dimension array recursive
* @update    22:02 3/7/2009
* @author    Rivanoor Bren <id_ivan(at)yahoo.com>
---------------------- */
function NP_array_flatten($array, $preserve = FALSE, $r = array()){
    foreach($array as $key => $value){
        if (is_array($value)){
            foreach($value as $k => $v){
                if (is_array($v)) { $tmp = $v; unset($value[$k]); }
            }
            if ($preserve) $r[$key] = $value;
            else $r[] = $value;
        }
    }
    $r = isset($tmp) ? array_flatten($tmp, $preserve, $r) : $r;
    return $r;
}
?>
