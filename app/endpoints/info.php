<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_once __DIR__ . "/../image.php";

// @codeCoverageIgnoreStart
// checks for extremely large file sizes for the sake of
//    completeness, but not worth testing all those branches
function phpNotationToBytes($fsize) {
    $unit = strtolower(substr($fsize, -1));

    // who is attempting to upload petabytes through PHP?!
    if (!in_array($unit, ["k", "m", "g", "t", "p"])) {
        return (int) $fsize;
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
    return (int) $num;
}
// @codeCoverageIgnoreEnd

$message = [
    "formats" => ["image/jpeg", "video/mp4", "video/quicktime"],
    "sizes" => sizes,
    "siteTitle" => DB::GetConfig("site_title"),
    "promo" => DB::GetConfig("promo"),
    "dynamicPublic" => DB::GetConfig("dynamic_public"),
    "maxUploadBytes" => phpNotationToBytes(ini_get("upload_max_filesize")),
    "simultaneousUploads" => 4,
];

header("Content-Type: application/json");
http_response_code(200);
echo json_encode($message);
