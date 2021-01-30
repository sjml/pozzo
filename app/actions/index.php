<?php

header('Content-Type: application/json');

$message = Array(
    "message" => "Hello from Pozzo.",
    "actions" => Array("list", "upload"),
);

echo(json_encode($message));
