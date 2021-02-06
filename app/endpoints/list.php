<?php

require_once __DIR__ . "/../db.php";

$photos = DB::GetAllPhotos();
header("Content-Type: application/json");
http_response_code(200);
echo json_encode($photos);
