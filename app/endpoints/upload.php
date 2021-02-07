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
    $orderSlot = null;
    $albumID = null;
    $input = json_decode(file_get_contents("php://input"), true);
    if ($input && array_key_exists("order")) {
        $orderSlot = $input["order"];
    }
    if ($input && array_key_exists("albumID")) {
        $orderSlot = $input["albumID"];
    }

    $photoData = importImage($_FILES["photoUp"]["tmp_name"]);
    if ($photoData == null) {
        http_response_code(415);
        echo '{"error": "415 / Unsupported Media Type"}';
        return;
    }
    $photoData["title"] = $_FILES["photoUp"]["name"];

    try {
        processImage($photoData, $albumID, $orderSlot);
        unset($photoData["tinyJPEG"]);

        http_response_code(200);
        header("Content-Type: application/json");
        echo json_encode($photoData);
    } catch (\Throwable $th) {
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode(["message" => "upload failure", "data" => [$th->getMessage(), $th->getTraceAsString()]]);
    }
}
