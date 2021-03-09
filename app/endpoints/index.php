<?php

$message = [
    "message" => "Hello from Pozzo.",
    "endpoints" => [
        "/index" => [],
        "/info" => [],
        "/setup" => [],
        "/login" => [
            "/",
            "check",
        ],
        "/upload" => [],
        "/album" => [
            "/list",
            "/new",
            "/view",
            "/remove",
            "/delete",
            "/edit",
            "/reorderList",
            "/reorderGroups",
        ],
        "/group" => [
            "/new",
            "/edit",
            "/move",
            "/reorder",
            "/merge",
        ],
        "/photo" => [
            "/delete",
            "/copy",
            "/move",
            "/orig",
            "/tagset",
            "/tag",
            "/untag",
        ],
    ],
];

header("Content-Type: application/json");
http_response_code(200);
echo json_encode($message);
