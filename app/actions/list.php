<?php

require_once __DIR__ . "/../db.php";

DB::Init();

$photos = DB::GetAllPhotos();
header("Content-Type: application/json");
echo json_encode($photos);
