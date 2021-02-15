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

const photoExifFields = [
    "IFD0" => ["Make", "Model", "DateTime"],
    "EXIF" => [
        "ExposureTime",
        "FNumber",
        "ISOSpeedRatings",
        "DateTimeOriginal",
        "ShutterSpeedValue",
        "ApertureValue",
        "BrightnessValue",
        "MeteringMode",
        "Flash",
        "FocalLength",
        "SubjectLocation",
        "MakerNote",
    ],
    "GPS" => [
        "GPSLatitudeRef",
        "GPSLatitude",
        "GPSLongitudeRef",
        "GPSLongitude",
        "GPSAltitudeRef",
        "GPSAltitude",
        "GPSSpeedRef",
        "GPSSpeed",
        "GPSImgDirectionRef",
        "GPSImgDirection",
    ],
];

function getImagePath($sizeLabel, $hash, $uniq) {
    $ret = __DIR__ . "/../public/photos/";
    $rawDirs = str_split($hash, 2);
    $rawDirs = array_slice($rawDirs, 0, 3);
    $dirs = [];
    foreach ($rawDirs as $d) {
        // having directories called "ad" in an image path
        //   can trigger some adblockers
        if ($d == "ad") {
            array_push($dirs, "a_");
        } else {
            array_push($dirs, $d);
        }
    }

    $ret .= implode("/", $dirs);
    if (!is_dir($ret)) {
        mkdir($ret, 0755, true);
    }
    return $ret . "/" . $hash . "_" . $uniq . "_" . $sizeLabel . ".jpg";
}

function deleteImagesWithHash($hash, $uniq) {
    $delSizes = array_column(sizes, "label");
    array_push($delSizes, "orig");
    foreach ($delSizes as $size) {
        $path = getImagePath($size, $hash, $uniq);
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
        "uniq" => uniqid(),
    ];

    $origPath = getImagePath("orig", $photoData["hash"], $photoData["uniq"]);
    if (is_uploaded_file($filePath)) {
        move_uploaded_file($filePath, $origPath);
    } else {
        copy($filePath, $origPath);
    }

    return $photoData;
}

function processImage(&$photoData, $albumID, $order) {
    $origPath = getImagePath("orig", $photoData["hash"], $photoData["uniq"]);

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

        // correct orientation directly
        $orient = $img->getImageOrientation();
        switch ($orient) {
            case imagick::ORIENTATION_RIGHTTOP:
                $img->rotateImage("#00000000", 90);
                break;
            case imagick::ORIENTATION_BOTTOMRIGHT:
                $img->rotateImage("#00000000", 180);
                break;
            case imagick::ORIENTATION_LEFTBOTTOM:
                $img->rotateImage("#00000000", 270);
                break;
        }
        $img->setImageOrientation(imagick::ORIENTATION_TOPLEFT);

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
            getImagePath(
                $size["label"],
                $photoData["hash"],
                $photoData["uniq"],
            ),
        );
    }

    // generate tiny preview
    // convert -define jpeg:size=32x32 IMG_6738.jpeg -resize 32x32 -auto-orient -strip -quality 40 out.jpg
    $img->setOption("jpeg:size", "32x32");
    $img->readImage($origPath);
    $img->scaleImage(32, 32, true);
    $orient = $img->getImageOrientation();
    switch ($orient) {
        case imagick::ORIENTATION_RIGHTTOP:
            $img->rotateImage("#00000000", 90);
            break;
        case imagick::ORIENTATION_BOTTOMRIGHT:
            $img->rotateImage("#00000000", 180);
            break;
        case imagick::ORIENTATION_LEFTBOTTOM:
            $img->rotateImage("#00000000", 270);
            break;
    }
    $img->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
    $img->stripImage();
    $img->setCompressionQuality(40);
    $img->setImageFormat("jpeg");

    $photoData["tinyJPEG"] = base64_encode($img->getImageBlob());

    //// <sigh>
    // $img->setImageFormat("webp");
    // $photoData["tinyWebP"] = base64_encode($img->getImageBlob());

    processExif($photoData, $origPath);

    $photoData["id"] = DB::InsertPhoto(
        $photoData,
        $photoData["title"],
        $albumID,
        $order,
    );
}

function _gpsToDegrees($val) {
    $ds = explode("/", $val[0]);
    $ms = explode("/", $val[1]);
    $ss = explode("/", $val[2]);
    $d = floatval($ds[0]) / floatval($ds[1]);
    $m = floatval($ms[0]) / floatval($ms[1]);
    $s = floatval($ss[0]) / floatval($ss[1]);

    return $d + $m / 60.0 + $s / 3600.0;
}

function processExif(&$photoData, $originalFilePath) {
    // suppressing notices from exif_read_data
    $exif = @exif_read_data($originalFilePath, 0, true);

    foreach (photoExifFields as $meta => $datums) {
        // just fill them with nulls and let the
        //   database worry about it
        if (!array_key_exists($meta, $exif)) {
            $exif[$meta] = [];
        }
        foreach ($datums as $field) {
            if (!array_key_exists($field, $exif[$meta])) {
                $photoData[$meta . "_" . $field] = null;
                continue;
            }
            $val = $exif[$meta][$field];
            if (is_array($val)) {
                $val = "[" . implode(", ", $val) . "]";
            }
            $photoData[$meta . "_" . $field] = strval($val);
        }
    }

    if (array_key_exists("GPS", $exif)) {
        $gpsLat = array_key_exists("GPSLatitude", $exif["GPS"])
            ? $exif["GPS"]["GPSLatitude"]
            : null;
        $gpsLatRef = array_key_exists("GPSLatitudeRef", $exif["GPS"])
            ? $exif["GPS"]["GPSLatitudeRef"]
            : null;
        $gpsLon = array_key_exists("GPSLongitude", $exif["GPS"])
            ? $exif["GPS"]["GPSLongitude"]
            : null;
        $gpsLonRef = array_key_exists("GPSLongitudeRef", $exif["GPS"])
            ? $exif["GPS"]["GPSLongitudeRef"]
            : null;

        if (
            $gpsLat != null &&
            $gpsLatRef != null &&
            $gpsLon != null &&
            $gpsLonRef != null
        ) {
            $lat = _gpsToDegrees($gpsLat);
            $lon = _gpsToDegrees($gpsLon);

            if ($gpsLatRef != "N") {
                $lat = -$lat;
            }
            if ($gpsLonRef != "E") {
                $lon = -$lon;
            }

            $photoData["latitude"] = $lat;
            $photoData["longitude"] = $lon;
        } else {
            $photoData["latitude"] = null;
            $photoData["longitude"] = null;
        }
    }
}
