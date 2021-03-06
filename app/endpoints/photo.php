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

$router->AddHandler("/delete", ["deletePhotos"], true);
$router->AddHandler("/copy", ["copyPhotos"], true);
$router->AddHandler("/move", ["movePhotos"], true);
// $router->AddHandler("/allTags", ["getAllTags"]);
$router->AddHandler("/tagSet", ["getPhotosTagged"]);
$router->AddHandler("/tag", ["tagPhotos"], true);
$router->AddHandler("/untag", ["untagPhotos"], true);

$router->Route();

function output($obj, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($obj);
}

function deletePhotos() {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!isset($input["photoIDs"]) || !is_array($input["photoIDs"])) {
        output(["message" => "Invalid or missing parameter 'photoIDs'"], 400);
        return;
    }
    $deletionList = $input["photoIDs"];

    foreach ($deletionList as $del) {
        if (!is_numeric($del)) {
            output(["message" => "Non-numeric value in 'photoIDs'"], 400);
            return;
        }
    }

    $results = DB::DeletePhotos($deletionList);

    foreach ($results as $deadPD) {
        deleteImagesWithHash($deadPD["hash"], $deadPD["uniq"]);
    }

    output(["message" => "Photos deleted", "data" => $results]);
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
            if (!isset($copyInst["albumID"]) || !is_numeric($copyInst["albumID"])) {
                output(
                    ["message" => "Missing or non-numeric value for either 'groupID' or 'albumID'"],
                    400,
                );
                return;
            }
            $album = DB::FindAlbum($copyInst["albumID"], true);
            if ($album == false) {
                output(["message" => "Target album not found"], 404);
                return;
            }
            $lastGroup = end($album["photoGroups"]);
            $copyInst["groupID"] = $lastGroup["id"];
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
        if (!isset($input["toAlbumID"]) || !is_numeric($input["toAlbumID"])) {
            output(
                ["message" => "Missing or non-numeric value for either 'toGroupID' or 'toAlbumID'"],
                400,
            );
            return;
        }
        $album = DB::FindAlbum($input["toAlbumID"], true);
        if ($album == false) {
            output(["message" => "Target album not found"], 404);
            return;
        }
        $lastGroup = end($album["photoGroups"]);
        $input["toGroupID"] = $lastGroup["id"];
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
        output(["message" => "Empty tag array"], 400);
        return;
    }
    if (count($tags) > 1) {
        output(["message" => "Only one tag at a time supported right now :("], 400);
        return;
    }

    $taggedPhotos = DB::GetPhotosTagged($tags[0]);
    if (count($taggedPhotos) == 0) {
        output(["message" => "No photos with that tag"], 404);
        return;
    }

    output($taggedPhotos);
}

// function getAllTags() {
//     output(DB::GetTagList());
// }
