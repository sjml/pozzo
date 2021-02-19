<?php

require_once __DIR__ . "/../db.php";
require_once __DIR__ . "/../image.php";

$_REQUEST["POZZO_REQUEST"] = preg_replace(
    "/^\/photo/",
    "",
    $_REQUEST["POZZO_REQUEST"],
);

require_once __DIR__ . "/../../app/router.php";
$router = new Router();

$router->AddHandler("/delete", ["deletePhoto"], true);
$router->AddHandler("/copy", ["copyPhoto"], true);
$router->AddHandler("/move", ["movePhotos"], true);
$router->AddHandler("/view", ["viewPhoto"]);
$router->AddHandler("/set", ["viewPhotoSet"]);
$router->AddHandler("/orig", ["downloadOrig"]);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function viewPhoto() {
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $photo = DB::GetPhoto($identifier);
    if ($photo == null) {
        output(["message" => "Photo not found"], 404);
        return;
    }

    output($photo);
}

function viewPhotoSet() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoIDs"]) || !is_array($input["photoIDs"])) {
        output(["message" => "Invalid or missing parameter 'photoIDs'"], 400);
        return;
    }
    $photoIDs = array_filter($input["photoIDs"], "is_numeric");
    if (count($photoIDs) != count($input["photoIDs"])) {
        output(["message" => "Non-numeric values in photoIDs list"], 400);
        return;
    }

    $photos = DB::GetPhotoSet($input["photoIDs"]);
    output($photos);
}

function downloadOrig() {
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $photo = DB::GetPhoto($identifier);
    if ($photo == null) {
        output(["message" => "Photo not found"], 404);
        return;
    }

    $filePath = getImagePath("orig", $photo["hash"], $photo["uniq"]);

    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header(
        "Content-Disposition: attachment; filename=\"" .
            $photo["originalFilename"] .
            "\"",
    );
    header("Expires: 0");
    header("Cache-Control: must-revalidate");
    header("Content-Length: " . filesize($filePath));
    readfile($filePath);
}

function deletePhoto() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoID"]) || !is_numeric($input["photoID"])) {
        output(["message" => "Invalid or missing parameter 'photoID'"], 400);
        return;
    }

    $result = DB::DeletePhoto($input["photoID"]);

    if (!is_array($result)) {
        if ($result == -1) {
            output(["message" => "Photo not found"], 404);
            return;
        } elseif ($result == -2) {
            output(["message" => "Could not delete photo"], 500);
            return;
        }
    }

    deleteImagesWithHash($result["hash"], $result["uniq"]);

    output(["message" => "Photo deleted", "data" => $result]);
}

function copyPhoto() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["copies"]) || !is_array($input["copies"])) {
        output(["message" => "Invalid or missing parameter 'copies'"], 400);
        return;
    }

    // inefficient given how AddPhotoToAlbum works, but this is not the bottleneck
    $statuses = [];
    $numErrors = 0;
    foreach ($input["copies"] as $copyInst) {
        $result = DB::AddPhotoToAlbum(
            $copyInst["photoID"],
            $copyInst["albumID"],
            null,
        );
        if ($result) {
            array_push($statuses, [
                "photoID" => $copyInst["photoID"],
                "albumID" => $copyInst["albumID"],
                "success" => true,
            ]);
        } else {
            array_push($statuses, [
                "photoID" => $copyInst["photoID"],
                "albumID" => $copyInst["albumID"],
                "success" => false,
            ]);
        }
    }
    output([
        "message" => "Operation complete.",
        "numErrors" => $numErrors,
        "statuses" => $statuses,
    ]);
}

function movePhotos() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoIDs"]) || !is_array($input["photoIDs"])) {
        output(["message" => "Invalid or missing parameter 'photoIDs'"], 400);
        return;
    }
    $photoIDs = array_filter($input["photoIDs"], "is_numeric");
    if (count($photoIDs) != count($input["photoIDs"])) {
        output(["message" => "Non-numeric values in photoIDs list"], 400);
        return;
    }
    if (!isset($input["fromAlbumID"]) || !is_numeric($input["fromAlbumID"])) {
        output(
            ["message" => "Missing or non-numeric value for 'fromAlbumID'"],
            400,
        );
        return;
    }
    if (!isset($input["toAlbumID"]) || !is_numeric($input["toAlbumID"])) {
        output(
            ["message" => "Missing or non-numeric value for 'toAlbumID'"],
            400,
        );
        return;
    }

    $result = DB::MovePhotos(
        $photoIDs,
        $input["fromAlbumID"],
        $input["toAlbumID"],
    );
    output(["message" => "Photos moved."]);
}
