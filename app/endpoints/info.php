<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_once __DIR__ . "/../image.php";

function phpNotationToBytes($fsize) {
    $unit = strtolower(substr($fsize, -1));

    // who is attempting to upload petabytes through PHP?!
    if (!in_array($unit, ["k", "m", "g", "t", "p"])) {
        return (int)$fsize;
    }

    $num = substr($fsize, 0, -1);
    // all fallthroughs intentional
    switch ($unit) {
        case "p":
            $num *= 1024;
        case "t":
            $num *= 1024;
        case "g":
            $num *= 1024;
        case "m":
            $num *= 1024;
        case "k":
            $num *= 1024;
            break;
    }
    return (int)$num;
}

$message = [
    "formats" => ["image/jpeg"],
    "sizes" => sizes,
    "siteTitle" => DB::GetConfig("site_title"),
    "promo" => DB::GetConfig("promo"),
    "maxUploadBytes" => phpNotationToBytes(ini_get("upload_max_filesize")),
];

header("Content-Type: application/json");
http_response_code(200);
echo json_encode($message);
