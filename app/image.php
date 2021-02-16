<?php

use kornrunner\Blurhash\Blurhash;

require_once __DIR__ . "/db.php";

const sizes = [
    [
        "maxHeight" => 2160,
        "maxWidth" => 3840,
        "label" => "large2x",
    ],
    [
        "maxHeight" => 1440,
        "maxWidth" => 2560,
        "label" => "medium2x",
    ],
    [
        "maxHeight" => 1080,
        "maxWidth" => 1920,
        "label" => "large",
    ],
    [
        "maxHeight" => 720,
        "maxWidth" => 1280,
        "label" => "medium",
    ],
    [
        "maxHeight" => 300,
        "maxWidth" => 300,
        "label" => "small2x",
    ],
    [
        "maxHeight" => 150,
        "maxWidth" => 150,
        "label" => "small",
    ],
    [
        "maxHeight" => 64,
        "maxWidth" => 64,
        "label" => "tiny2x",
    ],
    [
        "maxHeight" => 32,
        "maxWidth" => 32,
        "label" => "tiny",
    ]
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
    move_uploaded_file($filePath, $origPath);

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

    // // generate tiny preview
    // // convert -define jpeg:size=32x32 IMG_6738.jpeg -resize 32x32 -auto-orient -strip -quality 40 out.jpg
    // $img->setOption("jpeg:size", "32x32");
    // $img->readImage($origPath);
    // $img->scaleImage(32, 32, true);
    // $orient = $img->getImageOrientation();
    // switch ($orient) {
    //     case imagick::ORIENTATION_RIGHTTOP:
    //         $img->rotateImage("#00000000", 90);
    //         break;
    //     case imagick::ORIENTATION_BOTTOMRIGHT:
    //         $img->rotateImage("#00000000", 180);
    //         break;
    //     case imagick::ORIENTATION_LEFTBOTTOM:
    //         $img->rotateImage("#00000000", 270);
    //         break;
    // }
    // $img->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
    // $img->stripImage();
    // $img->setCompressionQuality(40);
    // $img->setImageFormat("jpeg");

    // $photoData["tinyJPEG"] = base64_encode($img->getImageBlob());

    $img->setOption(
        "jpeg:size",
        sizes[count(sizes) - 1]["maxWidth"] . "x" . sizes[count(sizes) - 1]["maxHeight"],
    );
    $img->readImage($origPath);

    $blur = imagecreatefromstring(file_get_contents(getImagePath("tiny", $photoData["hash"], $photoData["uniq"])));
    $blurW = imagesx($blur);
    $blurH = imagesy($blur);

    $pixels = [];
    for ($y = 0; $y < $blurH; ++$y) {
        $row = [];
        for ($x = 0; $x < $blurW; ++$x) {
            $index = imagecolorat($blur, $x, $y);
            $colors = imagecolorsforindex($blur, $index);
            $row[] = [$colors["red"], $colors["green"], $colors["blue"]];
        }
        $pixels[] = $row;
    }

    $components_x = 4;
    $components_y = 3;
    $photoData["blurHash"] = Blurhash::encode($pixels, $components_x, $components_y);

    processExif($photoData, $origPath);

    $photoData["id"] = DB::InsertPhoto(
        $photoData,
        $photoData["title"],
        $albumID,
        $order,
    );
}

function processExif(&$photoData, $originalFilePath) {
    $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_EXIFTOOL);
    $exif = $reader->read($originalFilePath);

    $rawData = $exif->getRawData();

    $photoData["make"] = $rawData["IFD0:Make"] ?? null;
    $photoData["model"] = $rawData["IFD0:Model"] ?? null;
    $photoData["lens"] = $rawData["ExifIFD:LensModel"] ?? null;
    $photoData["mime"] = $rawData["File:MIMEType"] ?? null;

    $photoData["creationDate"] = $exif->getCreationDate() ?? null;
    if ($photoData["creationDate"] != null) {
        $photoData["creationDate"] = $photoData["creationDate"]->getTimeStamp();
    }

    $photoData["keywords"] = $rawData["IPTC:Keywords"] ?? "";
    if ($photoData["keywords"] != null) {
        $photoData["keywords"] = implode(", ", $photoData["keywords"]);
    }

    $photoData["subjectArea"] = $rawData["ExifIFD:SubjectArea"] ?? null;
    $photoData["aperture"] = $rawData["Composite:Aperture"] ?? null;
    $photoData["iso"] = $rawData["ExifIFD:ISO"] ?? null;
    $photoData["shutterSpeed"] = $rawData["Composite:ShutterSpeed"] ?? null;
    $photoData["gpsLat"] = $rawData["Composite:GPSLatitude"] ?? null;
    $photoData["gpsLon"] = $rawData["Composite:GPSLongitude"] ?? null;
    $photoData["gpsAlt"] = $rawData["Composite:GPSAltitude"] ?? null;
}
