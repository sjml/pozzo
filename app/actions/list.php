<?php

require_once __DIR__ . "/../db.php";

$photos = DB::GetAllPhotos();
header("Content-Type: application/json");
echo json_encode($photos);
