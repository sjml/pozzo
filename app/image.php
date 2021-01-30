<?php

require_once(__DIR__ . "/db.php");
DB::Init();

const sizes = Array(
    Array(
        "maxHeight" => 1080,
        "maxWidth" =>  1920,
        "label" => "large2x",
    ),
    Array(
        "maxHeight" => 720,
        "maxWidth" =>  1280,
        "label" => "medium2x",
    ),
    Array(
        "maxHeight" => 540,
        "maxWidth" =>  960,
        "label" => "large",
    ),
    Array(
        "maxHeight" => 360,
        "maxWidth" =>  640,
        "label" => "medium",
    ),
);

function getImageDirectory($name) {
    $ret = __DIR__ . "/../public/img/" . $name;
    if (!is_dir($ret)) {
        mkdir($ret, 0755, true);
    }
    return $ret;
}

function importImage($filePath) {
    if (exif_imagetype($filePath) != IMAGETYPE_JPEG) {
        return null;
    }
    $photoData = Array(
        "title" => basename($filePath),
        "size" => filesize($filePath),
        "hash" => md5_file($filePath)
    );

    $origPath = getImageDirectory("orig") . '/' . $photoData["hash"] . '.jpg';
    if (is_uploaded_file($filePath)) {
        move_uploaded_file($filePath, $origPath);
    }
    else {
        copy($filePath, $origPath);
    }

    return $photoData;
}

function processImage($photoData) {
    $origPath = getImageDirectory("orig") . '/' . $photoData["hash"] . '.jpg';

    $img = new IMagick();
    foreach (sizes as $size) {
        $img->readImage($origPath);
        if (!array_key_exists("width", $photoData)) {
            $photoData['width'] = $img->getImageWidth();
            $photoData['height'] = $img->getImageHeight();
        }
        $img->setImageCompressionQuality(90);

        $profiles = $img->getImageProfiles('icc', true);

        $img->scaleImage($size["maxWidth"], $size["maxHeight"], true);
        $img->stripImage();
        if (!empty($profiles)) {
            $img->profileImage('icc', $profiles['icc']);
        }

        $img->writeImage(
            getImageDirectory($size['label'])
            . '/' . $photoData['hash'] . ".jpg"
        );
    }

    // $exif = exif_read_data($origPath);
    // print_r($exif);

    $photoData["id"] = DB::InsertPhoto($photoData);
}
