<?php

function removeDir($targetDir) {
    if(is_dir($targetDir)){
        $contents = array_diff(scandir($targetDir), array('.', '..'));
        foreach($contents as $entry) {
            $entry = $targetDir . '/' . $entry;
            if (is_dir($entry)) {
                removeDir($entry);
            }
            else {
                unlink($entry);
            }
        }
        rmdir($targetDir);
    }
}
