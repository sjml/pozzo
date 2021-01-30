<?php

header("Content-Type: application/json");

$message = [
    "message" => "Hello from Pozzo.",
    "actions" => ["list", "upload"],
];

echo json_encode($message);
