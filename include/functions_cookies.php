<?php
function ts_get_array_cookie($name, $id)
{
    if (isset($_COOKIE["tsf"][$name]) && ($cookie = unserialize(stripslashes($_COOKIE["tsf"][$name]))) && isset($cookie[$id])) {
        return $cookie[$id];
    }
    return 0;
}
function ts_set_array_cookie($name = "", $id = "", $value = "")
{
    if (isset($_COOKIE["tsf"][$name]) && ($newcookie = unserialize(stripslashes($_COOKIE["tsf"][$name])))) {
        $newcookie[$id] = $value;
    } else {
        $newcookie = [];
        $newcookie[$id] = $value;
    }
    $newcookie = addslashes(serialize($newcookie));
    my_setcookiee("tsf[" . $name . "]", $newcookie);
}
function my_setcookiee($name, $value = "", $expires = "")
{
    if ($expires == -1) {
        $expires = 0;
    } else {
        if (empty($expires)) {
            $expires = TIMENOW + 31536000;
        } else {
            $expires = TIMENOW + intval($expires);
        }
    }
    if (!headers_sent()) {
        setcookie($name, $value, $expires);
    } else {
        write_log("Can't insert the cookie (Headers Already sent): " . $name . " (" . implode(",", unserialize(stripslashes($value))) . ")");
    }
}

?>