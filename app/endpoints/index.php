<?php

header("Content-Type: application/json");

$message = [
    "message" => "Hello from Pozzo.",
    "actions" => ["list", "upload", "album"],
];

echo json_encode($message);
