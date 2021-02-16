<?php

function stringInArray($needle, $haystack) {
    foreach ($haystack as $val) {
        if ($val == $needle) {
            return true;
        }
    }
    return false;
}
