<?php

header("Content-Type: application/json");

$message = [
    "message" => "Hello from Pozzo.",
    "endpoints" => ["index", "login", "list", "upload", "album", "photo"],
];

http_response_code(200);
echo json_encode($message);
