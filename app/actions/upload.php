<?php

require_once(__DIR__ . "/../image.php");


const imgDir = __DIR__ . "/../../public/img";


$photoData = importImage($_FILES['photoUp']['tmp_name']);
if ($photoData == null) {
    http_response_code(415);
    echo('{"error": "415 / Unsupported Media Type"}');
    die();
}
$photoData["title"] = $_FILES['photoUp']['name'];

processImage($photoData);

header('Content-Type: application/json');
echo(json_encode($photoData));
