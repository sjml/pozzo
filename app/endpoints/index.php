<?php

$message = [
    "message" => "Hello from Pozzo.",
    "endpoints" => [
        "/index" => [],
        "/info" => [],
        "/setup" => [],
        "/config" => [
            "/get",
            "/set",
        ],
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
            "/delete",
        ],
        "/photo" => [
            "/delete",
            "/copy",
            "/move",
            "/orig",
            // "/allTags",
            "/tagSet",
            "/tag",
            "/untag",
        ],
        "/dynamic" => [
            "/all",
            "/unsorted",
        ],
    ],
];

header("Content-Type: application/json");
http_response_code(200);
echo json_encode($message);
