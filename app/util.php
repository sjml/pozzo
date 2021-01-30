<?php

function stringInArray($needle, $haystack) {
    foreach ($haystack as $val) {
        if ($val == $needle) {
            return true;
        }
    }
    return false;
}

function removeDir($targetDir) {
    if (is_dir($targetDir)) {
        $contents = array_diff(scandir($targetDir), [".", ".."]);
        foreach ($contents as $entry) {
            $entry = $targetDir . "/" . $entry;
            if (is_dir($entry)) {
                removeDir($entry);
            } else {
                unlink($entry);
            }
        }
        rmdir($targetDir);
    }
}
