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
    try {
        $orderSlot = null;
        $albumID = null;
        if (array_key_exists("data", $_POST)) {
            $input = json_decode($_POST["data"], true);
            if ($input && array_key_exists("order", $input)) {
                $orderSlot = $input["order"];
            }
            if ($input && array_key_exists("albumID", $input)) {
                $albumID = $input["albumID"];
            }
        }

        if (substr($_FILES["mediaUp"]["type"], 0, 6) === "image/") {
            $photoData = importImage($_FILES["mediaUp"]["tmp_name"]);
            if ($photoData == null) {
                http_response_code(415);
                echo '{"error": "415 / Unsupported Media Type"}';
                return;
            }
            $photoData["isVideo"] = false;
            $photoData["title"] = $_FILES["mediaUp"]["name"];
            $photoData["uploadedBy"] = $_REQUEST["POZZO_AUTH"];

            processImage($photoData);

            processPhotoMeta($photoData);

            $photoData["id"] = DB::InsertPhoto(
                $photoData,
                $photoData["title"],
                $albumID,
                $orderSlot,
            );

            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($photoData);
        }
        elseif (substr($_FILES["mediaUp"]["type"], 0, 6) === "video/") {
            $vidData = importVideo($_FILES["mediaUp"]["tmp_name"], $_FILES["mediaUp"]["name"]);
            $vidData["isVideo"] = true;
            $vidData["title"] = $_FILES["mediaUp"]["name"];
            $vidData["uploadedBy"] = $_REQUEST["POZZO_AUTH"];

            processImage($vidData);
            processVideoMeta($vidData);

            $vidData["id"] = DB::InsertPhoto(
                $vidData,
                $vidData["title"],
                $albumID,
                $orderSlot,
            );

            http_response_code(200);
            header("Content-Type: application/json");
            echo json_encode($vidData);
        }
        else {
            http_response_code(415);
            echo '{"error": "415 / Unsupported Media Type"}';
            return;
        }

        // @codeCoverageIgnoreStart
        // As with setup's catch, this is handling stuff that is unanticipated
        //    (hitting disk quota limits, timeouts, etc.)
    } catch (\Throwable $th) {
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode([
            "message" => "upload failure",
            "data" => [$th->getMessage(), $th->getTraceAsString()],
        ]);
    }
    // @codeCoverageIgnoreEnd
}
