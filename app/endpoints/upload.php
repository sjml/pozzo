<?php

require_once __DIR__ . "/../../app/router.php";
require_once __DIR__ . "/../image.php";

const imgDir = __DIR__ . "/../../public/img";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/upload/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);
$router = new Router();

$router->AddHandler("/$", ["upload"], true);

$router->Route();

function upload() {
    $photoData = importImage($_FILES["photoUp"]["tmp_name"]);
    if ($photoData == null) {
        http_response_code(415);
        echo '{"error": "415 / Unsupported Media Type"}';
        return;
    }
    $photoData["title"] = $_FILES["photoUp"]["name"];

    processImage($photoData);
    unset($photoData["tinyJPEG"]);

    header("Content-Type: application/json");
    echo json_encode($photoData);
}
