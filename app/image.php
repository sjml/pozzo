<?php

require_once __DIR__ . "/db.php";

const sizes = [
    [
        "maxHeight" => 1080,
        "maxWidth" => 1920,
        "label" => "large2x",
    ],
    [
        "maxHeight" => 720,
        "maxWidth" => 1280,
        "label" => "medium2x",
    ],
    [
        "maxHeight" => 540,
        "maxWidth" => 960,
        "label" => "large",
    ],
    [
        "maxHeight" => 360,
        "maxWidth" => 640,
        "label" => "medium",
    ],
];

function getImageDirectory($name) {
    $ret = __DIR__ . "/../public/img/" . $name;
    if (!is_dir($ret)) {
        mkdir($ret, 0755, true);
    }
    return $ret;
}

function deleteImagesWithHash($hash) {
    $delSizes = array_column(sizes, "label");
    array_push($delSizes, "orig");
    foreach ($delSizes as $size) {
        $path = getImageDirectory($size) . "/" . $hash . ".jpg";
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

function importImage($filePath) {
    if (exif_imagetype($filePath) != IMAGETYPE_JPEG) {
        return null;
    }
    $photoData = [
        "title" => basename($filePath),
        "size" => filesize($filePath),
        "hash" => md5_file($filePath),
    ];

    $origPath = getImageDirectory("orig") . "/" . $photoData["hash"] . ".jpg";
    if (is_uploaded_file($filePath)) {
        move_uploaded_file($filePath, $origPath);
    } else {
        copy($filePath, $origPath);
    }

    return $photoData;
}

function processImage(&$photoData) {
    $origPath = getImageDirectory("orig") . "/" . $photoData["hash"] . ".jpg";

    $img = new IMagick();
    foreach (sizes as $size) {
        $img->setOption(
            "jpeg:size",
            $size["maxWidth"] . "x" . $size["maxHeight"],
        );
        $img->readImage($origPath);
        if (!isset($photoData["width"])) {
            $photoData["width"] = $img->getImageWidth();
            $photoData["height"] = $img->getImageHeight();
            $photoData["aspect"] =
                (float) $img->getImageWidth() / (float) $img->getImageHeight();
        }

        // Quality setting is higher than it *needs* to be by any strict definition,
        //   but for gallery purposes I'd rather err on the side of images being
        //   slightly too large.
        // Very hard to pick a single number that's good for all images.
        //   Given how image-heavy this site will be, it'll never really have
        //   perfect lighthouse scores unless we're willing to degrade the visuals,
        //   which... not today.
        $img->setImageCompressionQuality(88);

        $profiles = $img->getImageProfiles("icc", true);

        $img->scaleImage($size["maxWidth"], $size["maxHeight"], true);
        $img->stripImage();

        // all other profiles were removed when the image was stripped;
        //   add back the ICC one if it existed. Leads to some cross-browser
        //   inconsistency, but worth maintaining accuracy if it's possible!
        if (!empty($profiles)) {
            $img->profileImage("icc", $profiles["icc"]);
        }

        $img->writeImage(
            getImageDirectory($size["label"]) .
                "/" .
                $photoData["hash"] .
                ".jpg",
        );
    }

    // generate tiny preview
    // convert -define jpeg:size=32x32 IMG_6738.jpeg -resize 32x32 -auto-orient -strip -quality 40 out.jpg
    $img->setOption("jpeg:size", "32x32");
    $img->readImage($origPath);
    $img->scaleImage(32, 32, true);
    $img->stripImage();
    $img->setCompressionQuality(40);
    $img->setImageFormat("jpeg");

    $photoData["tiny"] = base64_encode($img->getImageBlob());

    // TODO: figure out what EXIF data should be pulled into the database
    // $exif = exif_read_data($origPath);
    // print_r($exif);

    $photoData["id"] = DB::InsertPhoto($photoData);
}
