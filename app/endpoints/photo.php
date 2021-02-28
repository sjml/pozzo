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
$router->AddHandler("/copy", ["copyPhotos"], true);
$router->AddHandler("/move", ["movePhotos"], true);
$router->AddHandler("/tagset", ["getPhotosTagged"]);
$router->AddHandler("/tag", ["tagPhotos"], true);
$router->AddHandler("/untag", ["untagPhotos"], true);
$router->AddHandler("/orig", ["downloadOrig"]);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function downloadOrig() {
    $identifier = substr($_REQUEST["POZZO_REQUEST"], 1);
    $photo = DB::GetPhoto($identifier);
    if ($photo == null) {
        output(["message" => "Photo not found"], 404);
        return;
    }

    if ($photo["isVideo"]) {
        $ext = pathinfo($photo["originalFilename"], PATHINFO_EXTENSION);
        $filePath = getImagePath("orig", $photo["hash"], $photo["uniq"], $ext);
    }
    else {
        $filePath = getImagePath("orig", $photo["hash"], $photo["uniq"]);
    }

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
        }
        elseif ($result == -2) {
            output(["message" => "Could not delete photo"], 500);
            return;
        }
    }

    deleteImagesWithHash($result["hash"], $result["uniq"]);

    output(["message" => "Photo deleted", "data" => $result]);
}

function copyPhotos() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["copies"]) || !is_array($input["copies"])) {
        output(["message" => "Invalid or missing parameter 'copies'"], 400);
        return;
    }

    // inefficient given how AddPhotoToGroup works, but this is not the bottleneck
    $statuses = [];
    $numErrors = 0;
    foreach ($input["copies"] as $copyInst) {
        if (!array_key_exists("photoID", $copyInst) || !is_numeric($copyInst["photoID"])) {
            output(["message" => "Invalid or missing parameter 'photoID' in instruction array. Copy may be partially complete."], 400);
            return;
        }
        if (!array_key_exists("groupID", $copyInst) || !is_numeric($copyInst["groupID"])) {
            output(["message" => "Invalid or missing parameter 'groupID' in instruction array. Copy may be partially complete."], 400);
            return;
        }
        $result = DB::AddPhotoToGroup(
            $copyInst["photoID"],
            $copyInst["groupID"],
            null,
        );
        if ($result) {
            array_push($statuses, [
                "photoID" => $copyInst["photoID"],
                "groupID" => $copyInst["groupID"],
                "success" => true,
            ]);
        }
        else {
            array_push($statuses, [
                "photoID" => $copyInst["photoID"],
                "groupID" => $copyInst["groupID"],
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
    if (!isset($input["fromGroupID"]) || !is_numeric($input["fromGroupID"])) {
        output(
            ["message" => "Missing or non-numeric value for 'fromGroupID'"],
            400,
        );
        return;
    }
    if (!isset($input["toGroupID"]) || !is_numeric($input["toGroupID"])) {
        output(
            ["message" => "Missing or non-numeric value for 'toGroupID'"],
            400,
        );
        return;
    }

    $result = DB::MovePhotos(
        $photoIDs,
        $input["fromGroupID"],
        $input["toGroupID"],
    );
    output(["message" => "Photos moved."]);
}

function tagPhotos() {
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
    if (!isset($input["tags"]) || !is_array($input["tags"])) {
        output(["message" => "Invalid or missing parameter 'tags'"], 400);
        return;
    }

    foreach ($input["tags"] as $tag) {
        foreach ($photoIDs as $photoID) {
            DB::TagPhoto($photoID, $tag);
        }
    }

    output(["message" => "Photos tagged"]);
}

function untagPhotos() {
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
    if (!isset($input["tags"]) || !is_array($input["tags"])) {
        output(["message" => "Invalid or missing parameter 'tags'"], 400);
        return;
    }

    foreach ($input["tags"] as $tag) {
        foreach ($photoIDs as $photoID) {
            DB::UntagPhoto($photoID, $tag);
        }
    }

    output(["message" => "Photos untagged"]);
}

function getPhotosTagged() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["tags"]) || !is_array($input["tags"])) {
        output(["message" => "Invalid or missing parameter 'tags'"], 400);
        return;
    }
    $tags = $input["tags"];
    if (count($tags) == 0) {
        output([]);
        return;
    }
    if (count($tags) > 1) {
        output(
            ["message" => "Only one tag at a time supported right now :("],
            400,
        );
        return;
    }

    output(DB::GetPhotosTagged($tags[0]));
}
