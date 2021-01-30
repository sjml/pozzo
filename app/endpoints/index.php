<?php

header("Content-Type: application/json");

$message = [
    "message" => "Hello from Pozzo.",
    "endpoints" => ["index", "login", "list", "upload", "album", "photo"],
];

echo json_encode($message);
