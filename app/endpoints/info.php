<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../auth.php";
require_once __DIR__ . "/../image.php";

$message = [
    "formats" => ["image/jpeg"],
    "sizes" => sizes,
    "siteTitle" => DB::GetConfig("site_title"),
    "promo" => DB::GetConfig("promo"),
    "maxUpload" => ini_get('upload_max_filesize'),
];

header("Content-Type: application/json");
http_response_code(200);
echo json_encode($message);
